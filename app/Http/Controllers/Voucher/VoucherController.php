<?php

namespace App\Http\Controllers\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    /**
     * Every account voucher uses the same pages (Add / Edit form, Register, View).
     * Only these settings differ:
     *   label  -> shown in headings and field labels
     *   prefix -> start of the voucher number, e.g. Jv/26-27/00001
     *   dr, cr -> default groups whose ledgers can be picked on the Debit / Credit side
     *             (their sub-groups count too). Empty = all ledgers.
     *
     * To add a new voucher type, add one line here: its routes (/type,
     * /type-register, ...) are made from this list in routes/web.php.
     */
    public const TYPES = [
        'journal'  => ['label' => 'Journal',  'prefix' => 'Jv',  'dr' => [], 'cr' => []],
        'sales'    => ['label' => 'Sales',    'prefix' => 'Sl',  'dr' => ['Sundry Debtors'], 'cr' => ['Sales Accounts']],
        'purchase' => ['label' => 'Purchase', 'prefix' => 'Pur', 'dr' => ['Purchase Accounts'], 'cr' => ['Sundry Creditors']],
        'receipt'  => ['label' => 'Receipt',  'prefix' => 'Rct', 'dr' => ['Bank Accounts', 'Cash-in-Hand'], 'cr' => ['Sundry Debtors', 'Loans (Liability)']],
        'payment'  => ['label' => 'Payment',  'prefix' => 'Pmt', 'dr' => ['Sundry Creditors'], 'cr' => ['Bank Accounts', 'Cash-in-Hand']],
        'contra'   => ['label' => 'Contra',   'prefix' => 'Ctr', 'dr' => ['Bank Accounts', 'Cash-in-Hand'], 'cr' => ['Bank Accounts', 'Cash-in-Hand']],
    ];

    public const REFERENCES = ['New Ref', 'Against Ref', 'Advance', 'On Account'];

    // Rows shown on one page of the register
    private const PER_PAGE = 50;

    // Register page: all vouchers of this type for the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');
        $type = $this->type();
        $voucher = self::TYPES[$type];

        $query = $this->filteredVouchers($request, $type, $companyId);

        // Total amount of every voucher that matches the filters (not just this page)
        $totalAmount = (clone $query)->sum('amount');

        $vouchers = $query->paginate(self::PER_PAGE)->withQueryString();

        // "Ledger" filter dropdown: every ledger this voucher type can use on either side
        $ledgerOptions = $this->ledgersUnder($companyId, $voucher['dr'])
            ->merge($this->ledgersUnder($companyId, $voucher['cr']))
            ->unique('id')
            ->sortBy('name');

        return view('voucher.list-voucher', compact('user', 'type', 'voucher', 'vouchers', 'totalAmount', 'ledgerOptions'));
    }

    // Export to Excel: every voucher that matches the register's filters, as a .csv file
    public function export(Request $request)
    {
        $type = $this->type();
        $voucher = self::TYPES[$type];

        $vouchers = $this->filteredVouchers($request, $type, session('company_id'))->get();

        return response()->streamDownload(function () use ($vouchers, $voucher) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'S.No.', $voucher['label'] . ' No', 'Date', 'Debit Account (Dr)', 'Credit Account (Cr)', 'Narration',
                'Reference', 'Bill No', 'Quote Amount', 'Amount', 'Due Amount', 'Status', 'Created Date', 'Created By',
            ]);

            foreach ($vouchers as $index => $entry) {
                fputcsv($handle, [
                    $index + 1,
                    $entry->voucher_no,
                    $entry->date->format('d-m-Y'),
                    $entry->drLedger->name,
                    $entry->crLedger->name,
                    $entry->narration,
                    $entry->reference,
                    $entry->bill_no,
                    $entry->quote_amount,
                    $entry->amount,
                    $entry->due_amount,
                    $entry->status,
                    $entry->created_at->format('d-m-Y h:i A'),
                    $entry->creator->name ?? '-',
                ]);
            }

            fclose($handle);
        }, $type . '-register.csv', ['Content-Type' => 'text/csv']);
    }

    // Add page: the form to create a new voucher of this type
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');
        $type = $this->type();
        $voucher = self::TYPES[$type];

        // Ledgers for the Debit / Credit Account dropdowns
        $drLedgers = $this->ledgersUnder($companyId, $voucher['dr']);
        $crLedgers = $this->ledgersUnder($companyId, $voucher['cr']);

        // No saved voucher yet: the form (shared with the Edit page) starts empty
        $entry = null;

        return view('voucher.voucher', compact('user', 'type', 'voucher', 'entry', 'drLedgers', 'crLedgers'));
    }

    // Save the new voucher
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $type = $this->type();

        $data = $this->voucherData($request, $type, $companyId);
        $financialYear = Voucher::financialYear(Carbon::parse($request->date));

        // Voucher, its number and its documents are saved together: if one fails, nothing is saved
        $entry = DB::transaction(function () use ($request, $data, $type, $companyId, $financialYear) {
            // Next serial of this type in this financial year. lockForUpdate makes a second
            // user saving at the same moment wait, so both can't get the same number.
            $serial = Voucher::where('company_id', $companyId)
                ->where('type', $type)
                ->where('financial_year', $financialYear)
                ->lockForUpdate()
                ->max('serial') + 1;

            $entry = Voucher::create(array_merge($data, [
                'company_id'     => $companyId,
                'type'           => $type,
                'financial_year' => $financialYear,
                'serial'         => $serial,
                // e.g. Jv/26-27/00001
                'voucher_no'     => self::TYPES[$type]['prefix'] . '/' . $financialYear . '/' . str_pad($serial, 5, '0', STR_PAD_LEFT),
                'created_by'     => session('user_id'),
            ]));

            $this->saveDocuments($request, $entry);

            return $entry;
        });

        return redirect()->route($type . '-register')
            ->with('success', self::TYPES[$type]['label'] . ' ' . $entry->voucher_no . ' saved successfully.');
    }

    // View page: every detail of one voucher
    public function show(Request $request, $id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $type = $this->type();
        $voucher = self::TYPES[$type];

        $entry = $this->findVoucher($id)->load('drLedger', 'crLedger', 'documents', 'creator', 'verifier');

        return view('voucher.show-voucher', compact('user', 'type', 'voucher', 'entry'));
    }

    // Edit page: the form to edit an existing voucher
    public function edit(Request $request, $id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');
        $type = $this->type();
        $voucher = self::TYPES[$type];

        $entry = $this->findVoucher($id)->load('documents');

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        $drLedgers = $this->ledgersUnder($companyId, $voucher['dr']);
        $crLedgers = $this->ledgersUnder($companyId, $voucher['cr']);

        return view('voucher.voucher', compact('user', 'type', 'voucher', 'entry', 'drLedgers', 'crLedgers'));
    }

    // Save changes to an existing voucher
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');
        $type = $this->type();

        $entry = $this->findVoucher($id);

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        $data = $this->voucherData($request, $type, $companyId);

        // The voucher number has the financial year in it, so the date can't move to another year
        if (Voucher::financialYear(Carbon::parse($request->date)) != $entry->financial_year) {
            throw ValidationException::withMessages([
                'date' => 'The date must stay in financial year ' . $entry->financial_year . ' (' . $entry->voucher_no . ').',
            ]);
        }

        // Documents ticked "Remove" on the form
        $removed = $entry->documents()->whereIn('id', (array) $request->input('remove_documents', []))->get();

        DB::transaction(function () use ($request, $entry, $data, $removed) {
            $entry->update(array_merge($data, ['updated_by' => session('user_id')]));

            $entry->documents()->whereIn('id', $removed->pluck('id'))->delete();

            $this->saveDocuments($request, $entry);
        });

        // Files go only after the rows are really gone
        Storage::disk('local')->delete($removed->pluck('path')->all());

        return redirect()->route($type . '-register')
            ->with('success', self::TYPES[$type]['label'] . ' ' . $entry->voucher_no . ' updated successfully.');
    }

    // Delete a voucher (and its document files)
    public function destroy(Request $request, $id)
    {
        $type = $this->type();

        $entry = $this->findVoucher($id);

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        $paths = $entry->documents()->pluck('path')->all();

        // voucher_documents rows go with the voucher (onDelete cascade)
        $entry->delete();

        Storage::disk('local')->delete($paths);

        return redirect()->route($type . '-register')
            ->with('success', self::TYPES[$type]['label'] . ' ' . $entry->voucher_no . ' deleted successfully.');
    }

    // Verify a voucher, or unverify it again. A verified voucher can't be edited or deleted.
    public function verify(Request $request, $id)
    {
        $entry = $this->findVoucher($id);

        if ($entry->isVerified()) {
            $entry->update(['status' => 'Not Verified', 'verified_by' => null, 'verified_at' => null]);
            $message = $entry->voucher_no . ' is not verified any more.';
        } else {
            $entry->update(['status' => 'Verified', 'verified_by' => session('user_id'), 'verified_at' => now()]);
            $message = $entry->voucher_no . ' verified.';
        }

        return back()->with('success', $message);
    }

    // Open one document proof. Files are on the private disk, so they can only be
    // reached through here, and only by a user of the company the voucher belongs to.
    public function document($id)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $document = VoucherDocument::whereHas('voucher', function ($query) {
            $query->where('company_id', session('company_id'));
        })->findOrFail($id);

        return Storage::disk('local')->response($document->path, $document->original_name);
    }

    // The voucher type of the current page (journal, sales, ...): set on each route in routes/web.php
    private function type()
    {
        return request()->route('type');
    }

    // One voucher of the current company and the current type (404 for anything else)
    private function findVoucher($id)
    {
        return Voucher::where('company_id', session('company_id'))
            ->where('type', $this->type())
            ->findOrFail($id);
    }

    private function lockedMessage(Voucher $entry)
    {
        return $entry->voucher_no . ' is verified. Unverify it first to edit or delete it.';
    }

    /**
     * The register's list with its filters applied, newest first.
     * Used by both the register page and Export to Excel, so they always match.
     */
    private function filteredVouchers(Request $request, $type, $companyId)
    {
        return Voucher::where('company_id', $companyId)
            ->where('type', $type)
            ->with('drLedger', 'crLedger', 'documents', 'creator')
            ->when($request->voucher_no, function ($query, $voucherNo) {
                $query->where('voucher_no', 'like', "%{$voucherNo}%");
            })
            // A voucher shows up when this ledger is on its Debit or Credit side
            ->when($request->ledger, function ($query, $ledgerId) {
                $query->where(function ($query) use ($ledgerId) {
                    $query->where('dr_ledger_id', $ledgerId)->orWhere('cr_ledger_id', $ledgerId);
                });
            })
            ->when($request->bill_no, function ($query, $billNo) {
                $query->where('bill_no', 'like', "%{$billNo}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->from, function ($query, $from) {
                $query->whereDate('date', '>=', $from);
            })
            ->when($request->to, function ($query, $to) {
                $query->whereDate('date', '<=', $to);
            })
            ->orderByDesc('date')
            ->orderByDesc('id');
    }

    /**
     * Check the form and return the values to save (used by both store and update).
     * The Debit / Credit ledgers must be ones this voucher type allows.
     */
    private function voucherData(Request $request, $type, $companyId)
    {
        $voucher = self::TYPES[$type];

        $drIds = $this->ledgersUnder($companyId, $voucher['dr'])->pluck('id')->all();
        $crIds = $this->ledgersUnder($companyId, $voucher['cr'])->pluck('id')->all();

        $request->validate([
            'date'         => 'required|date',
            'dr_ledger_id' => ['required', Rule::in($drIds)],
            'cr_ledger_id' => ['required', Rule::in($crIds), 'different:dr_ledger_id'],
            'reference'    => ['required', Rule::in(self::REFERENCES)],
            'bill_no'      => 'nullable|string|max:100',
            // 9999999999999.99 is the biggest amount the columns can hold
            'quote_amount' => 'nullable|numeric|min:0|max:9999999999999.99',
            'amount'       => 'required|numeric|min:0.01|max:9999999999999.99',
            'narration'    => 'nullable|string|max:1000',
            'documents'    => 'nullable|array|max:10',
            // 2 MB: the server (php.ini upload_max_filesize) doesn't take bigger files
            'documents.*'  => 'file|mimes:jpg,jpeg,png,webp,pdf|max:2048',
        ], [
            'dr_ledger_id.required'  => 'Please select the Debit Account.',
            'dr_ledger_id.in'        => 'This ledger can\'t be used as the Debit Account here.',
            'cr_ledger_id.required'  => 'Please select the Credit Account.',
            'cr_ledger_id.in'        => 'This ledger can\'t be used as the Credit Account here.',
            'cr_ledger_id.different' => 'Debit and Credit Account can\'t be the same ledger.',
            'documents.max'          => 'You can attach up to 10 documents at a time.',
            'documents.*.mimes'      => 'Documents must be images (jpg, png, webp) or PDF.',
            'documents.*.max'        => 'Each document must be 2 MB or smaller.',
            'documents.*.file'       => 'A document could not be uploaded. Each one must be 2 MB or smaller.',
        ]);

        $quoteAmount = $request->filled('quote_amount') ? round($request->quote_amount, 2) : null;
        $amount = round($request->amount, 2);

        return [
            'date'         => $request->date,
            'dr_ledger_id' => $request->dr_ledger_id,
            'cr_ledger_id' => $request->cr_ledger_id,
            'reference'    => $request->reference,
            'bill_no'      => $request->bill_no,
            'quote_amount' => $quoteAmount,
            'amount'       => $amount,
            // Worked out here again, not taken from the form
            'due_amount'   => max(($quoteAmount ?? 0) - $amount, 0),
            'narration'    => $request->narration,
        ];
    }

    // Store the uploaded document proofs of a voucher: storage/app/private/vouchers/{company}/{voucher}/
    private function saveDocuments(Request $request, Voucher $entry)
    {
        foreach ($request->file('documents', []) as $file) {
            $entry->documents()->create([
                'path'          => $file->store("vouchers/{$entry->company_id}/{$entry->id}", 'local'),
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
            ]);
        }
    }

    /**
     * Ledgers of the company that sit under the given default groups
     * (e.g. ['Bank Accounts', 'Cash-in-Hand']) or under any sub-group below them.
     * An empty list means all ledgers. Each ledger also gets ->balance, e.g. "1,500.00 Dr".
     */
    private function ledgersUnder($companyId, $groupNames)
    {
        $query = Ledger::where('company_id', $companyId)->orderBy('name');

        if ($groupNames) {
            $groups = Group::where('company_id', $companyId)->get();

            // Start with the default groups themselves...
            $groupIds = $groups->where('is_primary', true)->whereIn('name', $groupNames)->pluck('id')->all();

            // ...then keep adding the groups that sit under them, one level at a time
            $found = $groupIds;

            while ($found) {
                $found = $groups->whereIn('parent_id', $found)->whereNotIn('id', $groupIds)->pluck('id')->all();
                $groupIds = array_merge($groupIds, $found);
            }

            $query->whereIn('group_id', $groupIds);
        }

        $ledgers = $query->get();

        // Current balance = opening balance + everything debited - everything credited (all voucher types)
        $debited = Voucher::where('company_id', $companyId)->groupBy('dr_ledger_id')
            ->selectRaw('dr_ledger_id, SUM(amount) as total')->pluck('total', 'dr_ledger_id');
        $credited = Voucher::where('company_id', $companyId)->groupBy('cr_ledger_id')
            ->selectRaw('cr_ledger_id, SUM(amount) as total')->pluck('total', 'cr_ledger_id');

        foreach ($ledgers as $ledger) {
            // Dr balance is counted as plus, Cr balance as minus
            $balance = ($ledger->opening_type == 'Dr' ? 1 : -1) * $ledger->opening_balance
                + ($debited[$ledger->id] ?? 0)
                - ($credited[$ledger->id] ?? 0);

            $ledger->balance = number_format(abs($balance), 2) . ' ' . ($balance >= 0 ? 'Dr' : 'Cr');
        }

        return $ledgers;
    }
}
