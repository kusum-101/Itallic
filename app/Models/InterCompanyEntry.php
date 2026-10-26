<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Sales voucher sent to another company on this software.
 * The buyer company sees it under Incoming Vouchers and saves it as a voucher (Purchase by default) with one OK,
 * so nobody has to type the same bill twice.
 */
class InterCompanyEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    // Sale fields that, when edited, send the sale to the buyer again
    private const SENT_FIELDS = ['date', 'dr_ledger_id', 'reference', 'bill_no', 'quote_amount', 'amount', 'narration'];

    // The seller's Sales voucher
    public function sale()
    {
        return $this->belongsTo(Voucher::class, 'sale_voucher_id');
    }

    // The buyer's voucher made from the sale: a Purchase, or the type the buyer picked (IncomingPurchaseController::SAVE_AS)
    public function purchase()
    {
        return $this->belongsTo(Voucher::class, 'purchase_voucher_id');
    }

    // The seller
    public function fromCompany()
    {
        return $this->belongsTo(Company::class, 'from_company_id');
    }

    // The buyer
    public function toCompany()
    {
        return $this->belongsTo(Company::class, 'to_company_id');
    }

    // The user who accepted / rejected it
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Called after a Sales voucher is saved. When its customer ledger (Debit side) has the GST No.
     * (or, without GST, the PAN No.) of another company on this software, that company gets
     * the sale in its Incoming Vouchers.
     */
    public static function syncFromSale(Voucher $sale)
    {
        // The sale's current entry (a Withdrawn one is history: that buyer no longer gets this sale)
        $entry = self::where('sale_voucher_id', $sale->id)->where('status', '!=', 'Withdrawn')->latest('id')->first();

        // Saved without changing anything the buyer gets: nothing to send again
        if ($entry && !$sale->wasChanged(self::SENT_FIELDS)) {
            return;
        }

        $buyer = self::buyerOf($sale);

        if ($entry && $entry->purchase_voucher_id) {
            // Already saved by the same buyer: send them the edit, their OK updates their voucher
            if ($buyer && $buyer->id == $entry->to_company_id) {
                $entry->update(['status' => 'Pending', 'handled_by' => null, 'handled_at' => null]);

                return;
            }

            // The sale now goes to someone else: the first buyer keeps their voucher (it is in their books,
            // only they can delete it) and sees the sale under "Withdrawn by seller" until they dismiss it
            $entry->update(['status' => 'Withdrawn', 'handled_by' => null, 'handled_at' => null]);
            $entry = null;
        }

        // Not saved by the buyer yet: (re)send it to whoever the customer is now
        $entry?->delete();

        if (!$buyer) {
            return;
        }

        // Moved back to a buyer it was withdrawn from, who still has their voucher: send it to them again
        // on that entry, so their OK updates that voucher instead of making a second one
        $earlier = self::where('sale_voucher_id', $sale->id)
            ->where('to_company_id', $buyer->id)
            ->where('status', 'Withdrawn')
            ->whereNotNull('purchase_voucher_id')
            ->latest('id')
            ->first();

        if ($earlier) {
            $earlier->update(['status' => 'Pending', 'handled_by' => null, 'handled_at' => null]);

            return;
        }

        self::create([
            'sale_voucher_id' => $sale->id,
            // Kept on the entry too, for when the sale is deleted (saleDeleted)
            'sale_no'         => $sale->voucher_no,
            'from_company_id' => $sale->company_id,
            'to_company_id'   => $buyer->id,
        ]);
    }

    /**
     * Called just before a Sales voucher is deleted. A buyer who already saved a voucher from it
     * keeps that voucher and sees the sale under "Withdrawn by seller" (the entry stays, without its sale).
     * Entries nobody saved a voucher for (pending, rejected) just go.
     */
    public static function saleDeleted(Voucher $sale)
    {
        $entries = self::where('sale_voucher_id', $sale->id)->get();

        foreach ($entries as $entry) {
            if (!$entry->purchase_voucher_id) {
                $entry->delete();
            } elseif ($entry->status != 'Withdrawn') {
                // (One already Withdrawn, from an earlier change of customer, stays as it is, dismissed or not)
                $entry->update(['status' => 'Withdrawn', 'handled_by' => null, 'handled_at' => null]);
            }
        }
    }

    // Withdrawn because the seller deleted the sale (rather than changed its customer)
    public function saleWasDeleted()
    {
        return $this->sale_voucher_id === null;
    }

    // The company on this software that the sale's customer ledger is (null for an outside party)
    private static function buyerOf(Voucher $sale)
    {
        $customer = Ledger::find($sale->dr_ledger_id);

        foreach (['gst' => $customer->gst_no, 'pan' => $customer->pan_no] as $column => $number) {
            $number = self::clean($number);

            if ($number) {
                $companies = Company::where('id', '!=', $sale->company_id)
                    ->whereRaw("UPPER(TRIM($column)) = ?", [$number])
                    ->limit(2)
                    ->get();

                // Two companies with this number (e.g. one PAN, two GST registrations): the buyer
                // isn't certain, so the sale goes nowhere rather than to the wrong company
                return $companies->count() == 1 ? $companies->first() : null;
            }
        }

        return null;
    }

    // GST / PAN No. compared without spaces and case: " 09abc " -> "09ABC"
    public static function clean($number)
    {
        return strtoupper(trim((string) $number));
    }
}
