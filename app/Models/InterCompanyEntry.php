<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Sales voucher sent to another company on this software.
 * The buyer company sees it under Incoming Purchases and books it as a Purchase with one OK,
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

    // The buyer's Purchase voucher made from the sale
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
     * the sale in its Incoming Purchases.
     */
    public static function syncFromSale(Voucher $sale)
    {
        $entry = self::where('sale_voucher_id', $sale->id)->first();

        // Saved without changing anything the buyer gets: nothing to send again
        if ($entry && !$sale->wasChanged(self::SENT_FIELDS)) {
            return;
        }

        $buyer = self::buyerOf($sale);

        // Already booked by the buyer: send the edit to the same buyer, their OK updates their Purchase.
        // If the sale now goes to someone else, the buyer's Purchase is theirs to keep or delete.
        if ($entry && $entry->purchase_voucher_id) {
            if ($buyer && $buyer->id == $entry->to_company_id) {
                $entry->update(['status' => 'Pending', 'handled_by' => null, 'handled_at' => null]);
            }

            return;
        }

        // Not booked yet: (re)send it to whoever the customer is now
        $entry?->delete();

        if ($buyer) {
            self::create([
                'sale_voucher_id' => $sale->id,
                'from_company_id' => $sale->company_id,
                'to_company_id'   => $buyer->id,
            ]);
        }
    }

    // The company on this software that the sale's customer ledger is (null for an outside party)
    private static function buyerOf(Voucher $sale)
    {
        $customer = Ledger::find($sale->dr_ledger_id);

        foreach (['gst' => $customer->gst_no, 'pan' => $customer->pan_no] as $column => $number) {
            $number = self::clean($number);

            if ($number) {
                return Company::where('id', '!=', $sale->company_id)
                    ->whereRaw("UPPER(TRIM($column)) = ?", [$number])
                    ->first();
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
