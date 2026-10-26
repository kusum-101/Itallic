<?php

namespace App\Http\Controllers\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\InterCompanyEntry;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Incoming Purchases: sales that other companies on this software made to the current company.
 * OK books the sale as a Purchase voucher here (no need to type it again), Reject turns it down.
 */
class IncomingPurchaseController extends Controller
{
    // Recently accepted / rejected entries shown below the pending ones
    private const HISTORY = 20;

    // The list: pending sales (each with its OK / Reject buttons) and the recently handled ones
    public function index()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $pending = InterCompanyEntry::where('to_company_id', $companyId)
            ->where('status', 'Pending')
            ->with('sale.documents', 'fromCompany', 'purchase')
            ->latest('updated_at')
            ->get();

        $handled = InterCompanyEntry::where('to_company_id', $companyId)
            ->where('status', '!=', 'Pending')
            ->with('sale', 'fromCompany', 'purchase', 'handler')
            ->latest('handled_at')
            ->limit(self::HISTORY)
            ->get();

        // Dropdowns of the Purchase voucher: Purchase Account (Dr) and Supplier (Cr)
        $purchaseLedgers = $this->ledgersUnder($companyId, ['Purchase Accounts']);
        $supplierLedgers = $this->ledgersUnder($companyId, ['Sundry Creditors']);

        // What each pending sale's dropdowns start on, so usually OK is the only click
        foreach ($pending as $entry) {
            $entry->default_purchase = $this->defaultPurchaseLedger($entry, $purchaseLedgers);
            $entry->default_supplier = $this->defaultSupplier($entry, $supplierLedgers);
        }

        return view('voucher.incoming-purchases', compact('user', 'pending', 'handled', 'purchaseLedgers', 'supplierLedgers'));
    }

    // View page: every detail of one incoming sale and its documents, with OK / Reject while it is pending
    public function show($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $entry = InterCompanyEntry::where('to_company_id', $companyId)
            ->with('sale.documents', 'fromCompany', 'purchase', 'handler')
            ->findOrFail($id);

        $purchaseLedgers = $this->ledgersUnder($companyId, ['Purchase Accounts']);
        $supplierLedgers = $this->ledgersUnder($companyId, ['Sundry Creditors']);

        if ($entry->status == 'Pending') {
            $entry->default_purchase = $this->defaultPurchaseLedger($entry, $purchaseLedgers);
            $entry->default_supplier = $this->defaultSupplier($entry, $supplierLedgers);
        }

        return view('voucher.show-incoming-purchase', compact('user', 'entry', 'purchaseLedgers', 'supplierLedgers'));
    }

    // Open one document of the seller's sale. Only the company the sale was sent to can open it.
    public function document($id, $documentId)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $entry = InterCompanyEntry::where('to_company_id', session('company_id'))->findOrFail($id);
        $document = $entry->sale->documents()->findOrFail($documentId);

        return Storage::disk('local')->response($document->path, $document->original_name);
    }

    // OK: book the sale as a Purchase voucher (or update the Purchase booked earlier, when the sale was edited)
    public function accept(Request $request, $id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');
        $entry = $this->findPending($id)->load('sale.documents', 'fromCompany', 'purchase');
        $sale = $entry->sale;
        $seller = $entry->fromCompany;

        // 'new' = make the ledger now (seller's name under Sundry Creditors / "Purchase" under Purchase Accounts)
        $request->validate([
            'purchase_ledger_id' => ['required', Rule::in(array_merge($this->ledgersUnder($companyId, ['Purchase Accounts'])->pluck('id')->all(), ['new']))],
            'supplier_ledger_id' => ['required', Rule::in(array_merge($this->ledgersUnder($companyId, ['Sundry Creditors'])->pluck('id')->all(), ['new']))],
        ], [
            'purchase_ledger_id.*' => 'Please select the Purchase Account.',
            'supplier_ledger_id.*' => 'Please select the Supplier.',
        ]);

        $purchase = $entry->purchase;

        if ($purchase && $purchase->isVerified()) {
            return back()->with('error', $purchase->voucher_no . ' is verified. Unverify it first, then OK the changed sale again.');
        }

        $purchase = DB::transaction(function () use ($request, $companyId, $entry, $sale, $seller, $purchase) {
            $supplierId = $request->supplier_ledger_id == 'new'
                ? $this->newLedger($companyId, 'Sundry Creditors', $seller->company_name, [
                    'contact'      => $seller->company_phone_no,
                    'gst_no'       => substr(InterCompanyEntry::clean($seller->gst), 0, 15) ?: null,
                    'pan_no'       => substr(InterCompanyEntry::clean($seller->pan), 0, 10) ?: null,
                    'opening_type' => 'Cr',
                ])
                : $request->supplier_ledger_id;

            $purchaseLedgerId = $request->purchase_ledger_id == 'new'
                ? $this->newLedger($companyId, 'Purchase Accounts', 'Purchase')
                : $request->purchase_ledger_id;

            // Same bill as the sale. The seller's voucher number is the supplier bill number here.
            $data = [
                'date'         => $sale->date,
                'dr_ledger_id' => $purchaseLedgerId,
                'cr_ledger_id' => $supplierId,
                'reference'    => $sale->reference,
                'bill_no'      => $sale->voucher_no,
                'quote_amount' => $sale->quote_amount,
                'amount'       => $sale->amount,
                'due_amount'   => $sale->due_amount,
                'narration'    => $sale->narration,
            ];

            if ($purchase) {
                $purchase->update(array_merge($data, ['updated_by' => session('user_id')]));
            } else {
                // Goes into the sale's financial year, whatever year is picked in the header
                $financialYear = Voucher::financialYear($sale->date);
                $serial = Voucher::nextSerial($companyId, 'purchase', $financialYear);

                $purchase = Voucher::create(array_merge($data, [
                    'company_id'     => $companyId,
                    'type'           => 'purchase',
                    'financial_year' => $financialYear,
                    'serial'         => $serial,
                    'voucher_no'     => VoucherController::voucherNumber('purchase', $financialYear, $serial),
                    'created_by'     => session('user_id'),
                ]));

                $this->copyDocuments($sale, $purchase);
            }

            $entry->update([
                'status'              => 'Accepted',
                'purchase_voucher_id' => $purchase->id,
                'handled_by'          => session('user_id'),
                'handled_at'          => now(),
            ]);

            return $purchase;
        });

        return redirect()->route('incoming-purchases')
            ->with('success', 'Purchase ' . $purchase->voucher_no . ' saved from ' . $seller->company_name . '\'s sale ' . $sale->voucher_no . '.');
    }

    // Reject: the sale is not booked here. The seller can see it was rejected; editing the sale sends it again.
    public function reject($id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $entry = $this->findPending($id)->load('sale');

        $entry->update([
            'status'     => 'Rejected',
            'handled_by' => session('user_id'),
            'handled_at' => now(),
        ]);

        return redirect()->route('incoming-purchases')
            ->with('success', 'Sale ' . $entry->sale->voucher_no . ' rejected.');
    }

    // A pending entry sent to the current company (404 for anything else)
    private function findPending($id)
    {
        return InterCompanyEntry::where('to_company_id', session('company_id'))
            ->where('status', 'Pending')
            ->findOrFail($id);
    }

    // Ledgers of the company under the given default groups (and their sub-groups)
    private function ledgersUnder($companyId, $groupNames)
    {
        return Ledger::where('company_id', $companyId)
            ->whereIn('group_id', Group::idsUnder($companyId, $groupNames))
            ->orderBy('name')
            ->get();
    }

    // The Purchase Account used the last time this seller's sale was booked, else the first one, else 'new'
    private function defaultPurchaseLedger(InterCompanyEntry $entry, $purchaseLedgers)
    {
        $lastUsed = InterCompanyEntry::where('to_company_id', $entry->to_company_id)
            ->where('from_company_id', $entry->from_company_id)
            ->whereNotNull('purchase_voucher_id')
            ->latest('handled_at')
            ->first()?->purchase?->dr_ledger_id;

        if ($lastUsed && $purchaseLedgers->contains('id', $lastUsed)) {
            return $lastUsed;
        }

        return $purchaseLedgers->first()->id ?? 'new';
    }

    // The supplier ledger with the seller's GST No. (or PAN No., or name), else 'new'
    private function defaultSupplier(InterCompanyEntry $entry, $supplierLedgers)
    {
        if ($entry->purchase) {
            return $entry->purchase->cr_ledger_id;
        }

        $seller = $entry->fromCompany;
        $gst = InterCompanyEntry::clean($seller->gst);
        $pan = InterCompanyEntry::clean($seller->pan);

        $supplier = $supplierLedgers->first(function ($ledger) use ($gst, $pan, $seller) {
            return ($gst && InterCompanyEntry::clean($ledger->gst_no) == $gst)
                || ($pan && InterCompanyEntry::clean($ledger->pan_no) == $pan)
                || strcasecmp(trim($ledger->name), trim($seller->company_name)) == 0;
        });

        return $supplier->id ?? 'new';
    }

    // Make a ledger under a default group and return its id
    private function newLedger($companyId, $groupName, $name, $details = [])
    {
        // Ledger names are unique in a company
        if (Ledger::where('company_id', $companyId)->where('name', $name)->exists()) {
            throw ValidationException::withMessages([
                'ledger' => 'A ledger named "' . $name . '" already exists under another group. Pick it in the dropdown, or move it under ' . $groupName . '.',
            ]);
        }

        return Ledger::create(array_merge([
            'company_id' => $companyId,
            'group_id'   => Group::where('company_id', $companyId)->where('is_primary', true)->where('name', $groupName)->value('id'),
            'name'       => $name,
        ], $details))->id;
    }

    // The sale's document proofs go with the Purchase too (copied, so each company keeps its own files)
    private function copyDocuments(Voucher $sale, Voucher $purchase)
    {
        foreach ($sale->documents as $document) {
            $path = "vouchers/{$purchase->company_id}/{$purchase->id}/" . basename($document->path);

            if (Storage::disk('local')->copy($document->path, $path)) {
                $purchase->documents()->create([
                    'path'          => $path,
                    'original_name' => $document->original_name,
                    'size'          => $document->size,
                ]);
            }
        }
    }
}
