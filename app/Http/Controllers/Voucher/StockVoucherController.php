<?php

namespace App\Http\Controllers\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\Group;
use App\Models\Item;
use App\Models\Ledger;
use App\Models\StockVoucher;
use App\Models\User;
use App\Models\Voucher;
use App\Support\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockVoucherController extends Controller
{
    /**
     * Every inventory voucher uses the same pages (Add / Edit form, Register, View).
     * Only these settings differ:
     *   label     -> shown in headings and field labels
     *   prefix    -> start of the voucher number, e.g. MIN/26-27/00001
     *   party     -> default groups whose ledgers can be picked as the Party (their sub-groups count too)
     *   direction -> in: the rows add stock to their godown; out: they take it away
     *
     * To add a new inventory voucher, add one line here: its routes (/type,
     * /type-register, ...) are made from this list in routes/web.php.
     */
    public const TYPES = [
        'material-inward' => ['label' => 'Material Inward', 'prefix' => 'MIN', 'party' => ['Sundry Creditors', 'Sundry Debtors'], 'direction' => 'in'],
    ];

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

        // Total value of every voucher that matches the filters (not just this page)
        $totalValue = DB::table('stock_voucher_lines')
            ->whereIn('stock_voucher_id', (clone $query)->select('stock_vouchers.id'))
            ->sum('value');

        $vouchers = $query->paginate(self::PER_PAGE)->withQueryString();

        // Filter dropdowns
        $partyOptions = $this->parties($companyId, $voucher);
        $itemOptions = Item::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('stock-voucher.list-stock-voucher', compact('user', 'type', 'voucher', 'vouchers', 'totalValue', 'partyOptions', 'itemOptions'));
    }

    // Export to Excel: every row of every voucher that matches the register's filters, as a .csv file
    public function export(Request $request)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $type = $this->type();
        $voucher = self::TYPES[$type];

        $vouchers = $this->filteredVouchers($request, $type, session('company_id'))->get();

        return response()->streamDownload(function () use ($vouchers, $voucher) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                $voucher['label'] . ' No', 'Date', 'Party', 'Challan / Bill No', 'Item', 'Godown',
                'Quantity', 'Unit', 'Rate', 'Value', 'Narration', 'Status', 'Created By',
            ]);

            // One line in the file for each item row of each voucher
            foreach ($vouchers as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($handle, [
                        $entry->voucher_no,
                        $entry->date->format('d-m-Y'),
                        $entry->party->name ?? '-',
                        $entry->reference_no,
                        $line->item->name,
                        $line->godown->name,
                        (float) $line->quantity(),
                        $line->item->unit->quantityUnit()->symbol,
                        $line->rate,
                        $line->value,
                        $entry->narration,
                        $entry->status,
                        $entry->creator->name ?? '-',
                    ]);
                }
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

        $type = $this->type();
        $voucher = self::TYPES[$type];

        // No saved voucher yet: the form (shared with the Edit page) starts empty, with one empty row
        $entry = null;
        $lines = [];

        return view('stock-voucher.stock-voucher', array_merge(
            compact('user', 'type', 'voucher', 'entry', 'lines'),
            $this->formOptions(session('company_id'), $voucher)
        ));
    }

    // Save the new voucher
    public function store(Request $request)
    {
        $companyId = session('company_id');
        $type = $this->type();
        $voucher = self::TYPES[$type];

        $data = $this->voucherData($request, $companyId, $voucher);
        $lines = $this->lineRows($request, $companyId, $voucher);
        $financialYear = Voucher::financialYear(Carbon::parse($request->date));

        // A new voucher goes into the financial year picked in the header
        if ($financialYear != FinancialYear::code()) {
            throw ValidationException::withMessages([
                'date' => 'The date must be in financial year ' . FinancialYear::label()
                    . ' (' . FinancialYear::start()->format('d-m-Y') . ' to ' . FinancialYear::end()->format('d-m-Y') . ').'
                    . ' To enter another year, change the year in the header.',
            ]);
        }

        // Voucher, its number and its rows are saved together: if one fails, nothing is saved
        $entry = DB::transaction(function () use ($data, $lines, $type, $voucher, $companyId, $financialYear) {
            // Next serial of this type in this financial year. lockForUpdate makes a second
            // user saving at the same moment wait, so both can't get the same number.
            $serial = StockVoucher::where('company_id', $companyId)
                ->where('type', $type)
                ->where('financial_year', $financialYear)
                ->lockForUpdate()
                ->max('serial') + 1;

            $entry = StockVoucher::create(array_merge($data, [
                'company_id'     => $companyId,
                'type'           => $type,
                'financial_year' => $financialYear,
                'serial'         => $serial,
                // e.g. MIN/26-27/00001
                'voucher_no'     => $voucher['prefix'] . '/' . $financialYear . '/' . str_pad($serial, 5, '0', STR_PAD_LEFT),
                'created_by'     => session('user_id'),
            ]));

            $entry->lines()->createMany($lines);

            return $entry;
        });

        return redirect()->route($type . '-register')
            ->with('success', $voucher['label'] . ' ' . $entry->voucher_no . ' saved successfully.');
    }

    // View page: every detail of one voucher, with its rows
    public function show($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $type = $this->type();
        $voucher = self::TYPES[$type];

        $entry = $this->findVoucher($id)->load('party', 'lines.item.unit.firstUnit', 'lines.item.unit.secondUnit', 'lines.godown', 'creator', 'verifier');

        return view('stock-voucher.show-stock-voucher', compact('user', 'type', 'voucher', 'entry'));
    }

    // Edit page: the form to edit an existing voucher
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $type = $this->type();
        $voucher = self::TYPES[$type];

        $entry = $this->findVoucher($id)->load('lines');

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        // Saved rows for the form. (float) drops the extra zeros: 10.0000 -> 10
        $lines = $entry->lines->map(function ($line) {
            return [
                'item_id'   => $line->item_id,
                'godown_id' => $line->godown_id,
                'quantity'  => (float) $line->quantity(),
                'rate'      => (float) $line->rate,
            ];
        })->all();

        return view('stock-voucher.stock-voucher', array_merge(
            compact('user', 'type', 'voucher', 'entry', 'lines'),
            $this->formOptions(session('company_id'), $voucher)
        ));
    }

    // Save changes to an existing voucher
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');
        $type = $this->type();
        $voucher = self::TYPES[$type];

        $entry = $this->findVoucher($id);

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        $data = $this->voucherData($request, $companyId, $voucher);
        $lines = $this->lineRows($request, $companyId, $voucher);

        // The voucher number has the financial year in it, so the date can't move to another year
        if (Voucher::financialYear(Carbon::parse($request->date)) != $entry->financial_year) {
            throw ValidationException::withMessages([
                'date' => 'The date must stay in financial year ' . $entry->financial_year . ' (' . $entry->voucher_no . ').',
            ]);
        }

        // Old rows are replaced by the ones in the form, together with the voucher
        DB::transaction(function () use ($entry, $data, $lines) {
            $entry->update(array_merge($data, ['updated_by' => session('user_id')]));

            $entry->lines()->delete();
            $entry->lines()->createMany($lines);
        });

        return redirect()->route($type . '-register')
            ->with('success', $voucher['label'] . ' ' . $entry->voucher_no . ' updated successfully.');
    }

    // Delete a voucher (its rows go with it: onDelete cascade)
    public function destroy($id)
    {
        $type = $this->type();

        $entry = $this->findVoucher($id);

        if ($entry->isVerified()) {
            return redirect()->route($type . '-register')->with('error', $this->lockedMessage($entry));
        }

        $entry->delete();

        return redirect()->route($type . '-register')
            ->with('success', self::TYPES[$type]['label'] . ' ' . $entry->voucher_no . ' deleted successfully.');
    }

    // Verify a voucher, or unverify it again. A verified voucher can't be edited or deleted.
    public function verify($id)
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

    // The voucher type of the current page (material-inward, ...): set on each route in routes/web.php
    private function type()
    {
        return request()->route('type');
    }

    // One voucher of the current company and the current type (404 for anything else)
    private function findVoucher($id)
    {
        return StockVoucher::where('company_id', session('company_id'))
            ->where('type', $this->type())
            ->findOrFail($id);
    }

    private function lockedMessage(StockVoucher $entry)
    {
        return $entry->voucher_no . ' is verified. Unverify it first to edit or delete it.';
    }

    // Ledgers that can be picked as this voucher type's Party
    private function parties($companyId, $voucher)
    {
        return Ledger::where('company_id', $companyId)
            ->whereIn('group_id', Group::idsUnder($companyId, $voucher['party']))
            ->orderBy('name')
            ->get();
    }

    // The dropdown lists of the Add / Edit form
    private function formOptions($companyId, $voucher)
    {
        return [
            'parties' => $this->parties($companyId, $voucher),
            // With their unit, for the "Quantity (Kg)" label and decimal check of each row
            'items'   => Item::where('company_id', $companyId)->with('unit.firstUnit', 'unit.secondUnit')->orderBy('name')->get(),
            // Main Location first: a new row starts with it
            'godowns' => Godown::where('company_id', $companyId)->orderByDesc('is_primary')->orderBy('name')->get(),
        ];
    }

    /**
     * The register's list with its filters applied, newest first.
     * Used by both the register page and Export to Excel, so they always match.
     */
    private function filteredVouchers(Request $request, $type, $companyId)
    {
        return StockVoucher::where('company_id', $companyId)
            ->where('type', $type)
            // Only the financial year picked in the header
            ->where('financial_year', FinancialYear::code())
            ->with('party', 'creator', 'lines.item.unit.firstUnit', 'lines.item.unit.secondUnit', 'lines.godown')
            ->when($request->voucher_no, function ($query, $voucherNo) {
                $query->where('voucher_no', 'like', "%{$voucherNo}%");
            })
            ->when($request->party, function ($query, $ledgerId) {
                $query->where('ledger_id', $ledgerId);
            })
            // A voucher shows up when any of its rows has this item
            ->when($request->item, function ($query, $itemId) {
                $query->whereHas('lines', function ($query) use ($itemId) {
                    $query->where('item_id', $itemId);
                });
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

    // Check the voucher's own fields and return the values to save (used by both store and update)
    private function voucherData(Request $request, $companyId, $voucher)
    {
        $partyIds = $this->parties($companyId, $voucher)->pluck('id')->all();

        $request->validate([
            'date'         => 'required|date',
            // Optional, but if picked it must be one this voucher type allows
            'ledger_id'    => ['nullable', Rule::in($partyIds)],
            'reference_no' => 'nullable|string|max:100',
            'narration'    => 'nullable|string|max:1000',
        ], [
            'ledger_id.in' => 'This ledger can\'t be used as the party here.',
        ]);

        return [
            'date'         => $request->date,
            'ledger_id'    => $request->ledger_id,
            'reference_no' => $request->reference_no,
            'narration'    => $request->narration,
        ];
    }

    /**
     * Check the item rows of the form and return the rows to save.
     * A row left fully empty is skipped; at least one row is needed. In a filled row the
     * Item, Godown and Quantity are needed; Rate can be left empty (0).
     * The quantity goes into quantity_in or quantity_out, by the voucher type's direction.
     * Value = Quantity x Rate, worked out here, not taken from the form.
     */
    private function lineRows(Request $request, $companyId, $voucher)
    {
        $items = Item::where('company_id', $companyId)->with('unit.secondUnit')->get()->keyBy('id');
        $godownIds = Godown::where('company_id', $companyId)->pluck('id')->all();

        $errors = [];
        $rows = [];

        foreach ((array) $request->input('lines', []) as $i => $row) {
            $itemId = trim($row['item_id'] ?? '');
            $godownId = trim($row['godown_id'] ?? '');
            $quantity = trim($row['quantity'] ?? '');
            $rate = trim($row['rate'] ?? '');

            // Fully empty row (the godown alone starts filled with Main Location): skip it
            if ($itemId === '' && $quantity === '' && $rate === '') {
                continue;
            }

            $item = $items->get($itemId);

            if (!$item) {
                $errors["lines.$i.item_id"] = 'Please select the item.';
            }

            if (!in_array($godownId, $godownIds)) {
                $errors["lines.$i.godown_id"] = 'Please select the godown.';
            }

            if (!is_numeric($quantity) || $quantity <= 0) {
                $errors["lines.$i.quantity"] = 'Quantity must be more than 0.';
            } elseif ($quantity > 99999999999) {
                // The biggest quantity the column can hold
                $errors["lines.$i.quantity"] = 'Quantity is too big.';
            } elseif ($item) {
                // The unit the quantity is typed in: Kg for Kg, Pcs for "Box of 12 Pcs"
                $quantityUnit = $item->unit->quantityUnit();

                if (round($quantity, $quantityUnit->decimals) != $quantity) {
                    $errors["lines.$i.quantity"] = $quantityUnit->symbol . ' allows only ' . $quantityUnit->decimals . ' digit(s) after the point.';
                }
            }

            if ($rate !== '' && (!is_numeric($rate) || $rate < 0)) {
                $errors["lines.$i.rate"] = 'Rate must be 0 or more.';
            }

            // Stop here for this row if anything is wrong with it
            if (array_intersect_key($errors, array_flip(["lines.$i.item_id", "lines.$i.godown_id", "lines.$i.quantity", "lines.$i.rate"]))) {
                continue;
            }

            $rate = $rate === '' ? 0 : round($rate, 2);
            $value = round($quantity * $rate, 2);

            // 9999999999999.99 is the biggest amount the columns can hold
            if ($value > 9999999999999.99) {
                $errors["lines.$i.rate"] = 'Quantity x Rate is too big.';
                continue;
            }

            $rows[] = [
                'item_id'      => $itemId,
                'godown_id'    => $godownId,
                'quantity_in'  => $voucher['direction'] == 'in' ? $quantity : 0,
                'quantity_out' => $voucher['direction'] == 'out' ? $quantity : 0,
                'rate'         => $rate,
                'value'        => $value,
            ];
        }

        if (!$errors && !$rows) {
            $errors['lines'] = 'Add at least one item.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $rows;
    }
}
