<?php

namespace App\Http\Controllers\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\InterCompanyEntry;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Voucher;
use App\Support\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Incoming Vouchers: sales that other companies on this software made to the current company.
 * OK saves the sale here as a voucher (Purchase by default, or Journal / Payment when the buyer
 * picks that on the View page), so nobody types it twice. Reject turns it down.
 */
class IncomingPurchaseController extends Controller
{
    // Voucher types a sale can be saved as here (keys of VoucherController::TYPES)
    public const SAVE_AS = ['purchase', 'journal', 'payment'];

    // Debit / Credit dropdown options that make the ledger on OK:
    // the seller under Sundry Creditors, or "Purchase" under Purchase Accounts
    public const NEW_SUPPLIER = 'new-supplier';
    public const NEW_PURCHASE = 'new-purchase';

    // Recently rejected entries shown below the pending ones
    private const HISTORY = 20;

    // The list: pending sales (each with View / OK / Reject) and the recently rejected ones.
    // An accepted sale leaves this page: it is in the register of the voucher it was saved as.
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

        $withdrawn = self::withdrawnQuery($companyId)
            ->with('sale', 'fromCompany', 'purchase')
            ->latest('updated_at')
            ->get();

        $rejected = InterCompanyEntry::where('to_company_id', $companyId)
            ->where('status', 'Rejected')
            ->with('sale', 'fromCompany', 'handler')
            ->latest('handled_at')
            ->limit(self::HISTORY)
            ->get();

        // How each pending sale is saved on OK (voucher type and Debit / Credit), so OK is the only click.
        // To save it differently, the View page has the dropdowns.
        $choices = $this->choices($companyId);

        foreach ($pending as $entry) {
            $entry->defaults = $this->defaults($entry, $choices);
            $entry->save_as = $this->saveAs($entry, $choices);
        }

        return view('voucher.incoming-purchases', compact('user', 'pending', 'withdrawn', 'rejected'));
    }

    // View page: every detail of one incoming sale and its documents, and how to save it while it is pending
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

        $choices = null;

        if ($entry->status == 'Pending') {
            $choices = $this->choices($companyId);
            $entry->defaults = $this->defaults($entry, $choices);
        }

        return view('voucher.show-incoming-purchase', compact('user', 'entry', 'choices'));
    }

    // Open one document of the seller's sale. Only the company the sale was sent to can open it.
    public function document($id, $documentId)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $entry = InterCompanyEntry::where('to_company_id', session('company_id'))->findOrFail($id);

        // A deleted sale's documents went with it
        abort_unless($entry->sale, 404);

        $document = $entry->sale->documents()->findOrFail($documentId);

        return Storage::disk('local')->response($document->path, $document->original_name);
    }

    // OK: save the sale as a voucher here (or update the one saved earlier, when the sale was edited)
    public function accept(Request $request, $id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');
        $entry = $this->findEntry($id)->load('sale.documents', 'fromCompany', 'purchase');

        if ($entry->status != 'Pending') {
            return $this->alreadyHandled($entry);
        }

        $sale = $entry->sale;
        $seller = $entry->fromCompany;
        $booked = $entry->purchase;

        // A voucher saved earlier keeps its type (its number has the type in it)
        $type = $booked ? $booked->type : $request->voucher_type;

        if (!$booked) {
            $request->validate(['voucher_type' => ['required', Rule::in(self::SAVE_AS)]], [
                'voucher_type.*' => 'Please select the Voucher Type.',
            ]);
        }

        $choice = $this->choices($companyId)[$type];

        $request->validate([
            'dr_ledger_id' => ['required', Rule::in($this->allowed($choice['dr']))],
            'cr_ledger_id' => ['required', Rule::in($this->allowed($choice['cr'])), 'different:dr_ledger_id'],
        ], [
            'dr_ledger_id.required'  => 'Please select the Debit Account.',
            'dr_ledger_id.in'        => 'This ledger can\'t be used as the Debit Account of a ' . $choice['label'] . '.',
            'cr_ledger_id.required'  => 'Please select the Credit Account.',
            'cr_ledger_id.in'        => 'This ledger can\'t be used as the Credit Account of a ' . $choice['label'] . '.',
            'cr_ledger_id.different' => 'Debit and Credit Account can\'t be the same ledger.',
        ]);

        if ($booked && $booked->isVerified()) {
            return back()->with('error', $booked->voucher_no . ' is verified. Unverify it first, then OK the changed sale again.');
        }

        $voucher = DB::transaction(function () use ($request, $companyId, $entry, $sale, $seller, $booked, $type) {
            // A second OK of the same sale (double click, or another user at the same moment) waits
            // here until the first one is saved, then finds it already handled and saves nothing
            if (!$this->stillPending($entry)) {
                return null;
            }

            // The sale as it is now (the seller may have edited it since the page was opened)
            $sale->refresh();

            // Same bill as the sale. The seller's voucher number is the bill number here.
            $data = [
                'date'         => $sale->date,
                'dr_ledger_id' => $this->ledgerId($request->dr_ledger_id, $companyId, $seller),
                'cr_ledger_id' => $this->ledgerId($request->cr_ledger_id, $companyId, $seller),
                'reference'    => $sale->reference,
                'bill_no'      => $sale->voucher_no,
                'quote_amount' => $sale->quote_amount,
                'amount'       => $sale->amount,
                'due_amount'   => $sale->due_amount,
                'narration'    => $sale->narration,
            ];

            if ($booked) {
                $booked->update(array_merge($data, ['updated_by' => session('user_id')]));
                $voucher = $booked;
            } else {
                // Goes into the sale's financial year, whatever year is picked in the header
                $financialYear = Voucher::financialYear($sale->date);
                $serial = Voucher::nextSerial($companyId, $type, $financialYear);

                $voucher = Voucher::create(array_merge($data, [
                    'company_id'     => $companyId,
                    'type'           => $type,
                    'financial_year' => $financialYear,
                    'serial'         => $serial,
                    'voucher_no'     => VoucherController::voucherNumber($type, $financialYear, $serial),
                    'created_by'     => session('user_id'),
                ]));

                $this->copyDocuments($sale, $voucher);
            }

            $entry->update([
                'status'              => 'Accepted',
                'purchase_voucher_id' => $voucher->id,
                'handled_by'          => session('user_id'),
                'handled_at'          => now(),
            ]);

            return $voucher;
        });

        if (!$voucher) {
            return $this->alreadyHandled($entry);
        }

        $message = VoucherController::TYPES[$type]['label'] . ' ' . $voucher->voucher_no . ' saved from ' . $seller->company_name . '\'s sale ' . $sale->voucher_no . '.';

        // The voucher is in the sale's financial year. The registers only list the year picked in the
        // header, so when that is another year, say where it went and open the voucher itself.
        $otherYear = $voucher->financial_year != FinancialYear::code();

        if ($otherYear) {
            $message .= ' It is in financial year ' . $voucher->financial_year . ': to see it in the '
                . VoucherController::TYPES[$type]['label'] . ' Register, change the year in the header.';
        }

        // Back to the list while more sales are waiting, else to the voucher's register (or the voucher, for another year)
        if (InterCompanyEntry::where('to_company_id', $companyId)->where('status', 'Pending')->exists()) {
            return redirect()->route('incoming-purchases')->with('success', $message);
        }

        if ($otherYear) {
            return redirect()->route($type . '.show', $voucher->id)->with('success', $message);
        }

        return redirect()->route($type . '-register')->with('success', $message);
    }

    // Reject: the sale is not saved here. The seller can see it was rejected; editing the sale sends it again.
    public function reject($id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $entry = $this->findEntry($id)->load('sale');

        if ($entry->status != 'Pending') {
            return $this->alreadyHandled($entry);
        }

        // Same lock as OK: an OK and a Reject at the same moment can't both happen
        $rejected = DB::transaction(function () use ($entry) {
            if (!$this->stillPending($entry)) {
                return false;
            }

            $entry->update([
                'status'     => 'Rejected',
                'handled_by' => session('user_id'),
                'handled_at' => now(),
            ]);

            return true;
        });

        if (!$rejected) {
            return $this->alreadyHandled($entry);
        }

        return redirect()->route('incoming-purchases')
            ->with('success', 'Sale ' . $entry->sale_no . ' rejected.');
    }

    // Dismiss a sale the seller moved to another customer: this company has seen it and
    // decided about its voucher (kept or deleted), so it leaves the list
    public function dismiss($id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $entry = self::withdrawnQuery(session('company_id'))->with('sale')->findOrFail($id);

        $entry->update(['handled_by' => session('user_id'), 'handled_at' => now()]);

        return redirect()->route('incoming-purchases')
            ->with('success', 'Withdrawn sale ' . $entry->sale_no . ' dismissed.');
    }

    /**
     * Sales the seller moved to another customer after this company had saved them, not dismissed yet,
     * and whose voucher is still here (once the voucher is deleted there is nothing left to decide).
     * Also counted in the sidebar.
     */
    public static function withdrawnQuery($companyId)
    {
        return InterCompanyEntry::where('to_company_id', $companyId)
            ->where('status', 'Withdrawn')
            ->whereNull('handled_by')
            ->whereNotNull('purchase_voucher_id');
    }

    // An entry sent to the current company (404 for anything else)
    private function findEntry($id)
    {
        return InterCompanyEntry::where('to_company_id', session('company_id'))->findOrFail($id);
    }

    /**
     * Inside a DB transaction: lock the entry's row and check nobody handled it since this page
     * was opened (still Pending, still pointing at the same voucher). Anyone else handling it waits for the lock.
     */
    private function stillPending(InterCompanyEntry $entry)
    {
        $now = InterCompanyEntry::lockForUpdate()->find($entry->id);

        return $now && $now->status == 'Pending' && $now->purchase_voucher_id == $entry->purchase_voucher_id;
    }

    // OK / Reject on a sale someone already handled (e.g. a double click): say what happened to it, change nothing
    private function alreadyHandled(InterCompanyEntry $entry)
    {
        $now = InterCompanyEntry::with('purchase')->find($entry->id);

        // The seller deleted the sale meanwhile (an entry nobody had saved a voucher for goes with it)
        if (!$now) {
            return redirect()->route('incoming-purchases')
                ->with('error', 'Sale ' . $entry->sale_no . ' was deleted by the seller. Nothing was saved.');
        }

        $entry = $now;

        $message = 'Sale ' . $entry->sale_no . ' was already '
            . ($entry->status == 'Accepted' && $entry->purchase ? 'saved as ' . $entry->purchase->voucher_no : strtolower($entry->status))
            . '. Nothing was changed.';

        return redirect()->route('incoming-purchases')->with('success', $message);
    }

    /**
     * For each voucher type in SAVE_AS: its label and, for the Debit and the Credit side,
     * the ledgers it allows (same groups as VoucherController::TYPES) and whether
     * a new supplier / purchase ledger can go on that side.
     */
    private function choices($companyId)
    {
        $groups = Group::where('company_id', $companyId)->get(['id', 'parent_id', 'name', 'is_primary']);
        $ledgers = Ledger::where('company_id', $companyId)->orderBy('name')->get();
        $supplierGroups = Group::idsUnder($companyId, ['Sundry Creditors']);
        $purchaseGroups = Group::idsUnder($companyId, ['Purchase Accounts']);

        $choices = [];

        foreach (self::SAVE_AS as $type) {
            $voucher = VoucherController::TYPES[$type];
            $choices[$type] = ['label' => $voucher['label']];

            foreach (['dr', 'cr'] as $side) {
                // No groups = every ledger
                $groupIds = $voucher[$side] ? Group::idsUnder($companyId, $voucher[$side]) : $groups->pluck('id')->all();

                $choices[$type][$side] = [
                    'ledgers'  => $ledgers->whereIn('group_id', $groupIds)->values(),
                    'supplier' => (bool) array_intersect($supplierGroups, $groupIds),
                    'purchase' => (bool) array_intersect($purchaseGroups, $groupIds),
                    'purchase_ids' => $ledgers->whereIn('group_id', array_intersect($purchaseGroups, $groupIds))->pluck('id')->all(),
                ];
            }
        }

        return $choices;
    }

    // Values a Debit / Credit dropdown may send: its ledgers, and the "new ledger" options it offers
    private function allowed($side)
    {
        $values = $side['ledgers']->pluck('id')->all();

        if ($side['supplier']) {
            $values[] = self::NEW_SUPPLIER;
        }

        if ($side['purchase']) {
            $values[] = self::NEW_PURCHASE;
        }

        return $values;
    }

    /**
     * What the dropdowns start on: ['type' => ..., 'by_type' => [type => ['dr' => ..., 'cr' => ...]]].
     * The type is the one the last sale from this seller was saved as (Purchase the first time).
     * The seller goes on the side that takes Sundry Creditors (Credit for Purchase / Journal,
     * Debit for Payment), and the other side gets a Purchase ledger where it can.
     */
    private function defaults(InterCompanyEntry $entry, $choices)
    {
        // Saved earlier and edited by the seller: same type and accounts as then
        $last = $entry->purchase ?? InterCompanyEntry::where('to_company_id', $entry->to_company_id)
            ->where('from_company_id', $entry->from_company_id)
            ->whereNotNull('purchase_voucher_id')
            ->latest('handled_at')
            ->first()?->purchase;

        $byType = [];

        foreach ($choices as $type => $choice) {
            if ($last && $last->type == $type
                && $choice['dr']['ledgers']->contains('id', $last->dr_ledger_id)
                && $choice['cr']['ledgers']->contains('id', $last->cr_ledger_id)) {
                $byType[$type] = ['dr' => $last->dr_ledger_id, 'cr' => $last->cr_ledger_id];
                continue;
            }

            $supplierSide = $choice['cr']['supplier'] ? 'cr' : ($choice['dr']['supplier'] ? 'dr' : null);
            $otherSide = $supplierSide == 'dr' ? 'cr' : 'dr';
            $pick = ['dr' => '', 'cr' => ''];

            if ($supplierSide) {
                $pick[$supplierSide] = $this->supplierIn($entry, $choice[$supplierSide]['ledgers']) ?? self::NEW_SUPPLIER;
            }

            $other = $choice[$otherSide];

            if ($other['purchase']) {
                $pick[$otherSide] = $other['purchase_ids'][0] ?? self::NEW_PURCHASE;
            } else {
                // e.g. Payment: the first Bank / Cash ledger
                $pick[$otherSide] = $other['ledgers']->first()->id ?? '';
            }

            $byType[$type] = $pick;
        }

        return [
            'type'    => $last && isset($choices[$last->type]) ? $last->type : 'purchase',
            'by_type' => $byType,
        ];
    }

    // "Purchase: Dr Purchase / Cr We4U" for the list page, from the defaults
    private function saveAs(InterCompanyEntry $entry, $choices)
    {
        $type = $entry->defaults['type'];
        $pick = $entry->defaults['by_type'][$type];

        $name = function ($side) use ($choices, $type, $pick, $entry) {
            return match ($pick[$side]) {
                self::NEW_SUPPLIER => $entry->fromCompany->company_name . ' (new)',
                self::NEW_PURCHASE => 'Purchase (new)',
                ''                 => '-',
                default            => $choices[$type][$side]['ledgers']->firstWhere('id', $pick[$side])->name,
            };
        };

        return $choices[$type]['label'] . ': Dr ' . $name('dr') . ' / Cr ' . $name('cr');
    }

    // The ledger among these with the seller's GST No. (or PAN No., or name), null if none
    private function supplierIn(InterCompanyEntry $entry, $ledgers)
    {
        $seller = $entry->fromCompany;
        $gst = InterCompanyEntry::clean($seller->gst);
        $pan = InterCompanyEntry::clean($seller->pan);

        return $ledgers->first(function ($ledger) use ($gst, $pan, $seller) {
            return ($gst && InterCompanyEntry::clean($ledger->gst_no) == $gst)
                || ($pan && InterCompanyEntry::clean($ledger->pan_no) == $pan)
                || strcasecmp(trim($ledger->name), trim($seller->company_name)) == 0;
        })?->id;
    }

    // The ledger id a dropdown value stands for, making the ledger for a "new" option
    private function ledgerId($value, $companyId, $seller)
    {
        if ($value == self::NEW_SUPPLIER) {
            return $this->newLedger($companyId, 'Sundry Creditors', $seller->company_name, [
                'contact'      => $seller->company_phone_no,
                'gst_no'       => substr(InterCompanyEntry::clean($seller->gst), 0, 15) ?: null,
                'pan_no'       => substr(InterCompanyEntry::clean($seller->pan), 0, 10) ?: null,
                'opening_type' => 'Cr',
            ]);
        }

        if ($value == self::NEW_PURCHASE) {
            return $this->newLedger($companyId, 'Purchase Accounts', 'Purchase');
        }

        return $value;
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

    // The sale's document proofs go with the voucher too (copied, so each company keeps its own files)
    private function copyDocuments(Voucher $sale, Voucher $voucher)
    {
        foreach ($sale->documents as $document) {
            $path = "vouchers/{$voucher->company_id}/{$voucher->id}/" . basename($document->path);

            if (Storage::disk('local')->copy($document->path, $path)) {
                $voucher->documents()->create([
                    'path'          => $path,
                    'original_name' => $document->original_name,
                    'size'          => $document->size,
                ]);
            }
        }
    }
}
