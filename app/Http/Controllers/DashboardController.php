<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Group;
use App\Models\Item;
use App\Models\ItemOpening;
use App\Models\Ledger;
use App\Models\StockVoucher;
use App\Models\User;
use App\Models\Voucher;
use App\Support\FinancialYear;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // Dashboard page: this year's totals, money to receive / pay, quick links and the setup guide
    public function index()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        // If no company is selected yet, send the user to pick one
        if (!session('company_id')) {
            return redirect()->route('select-company');
        }

        $companyId = session('company_id');
        $company = Company::find($companyId);

        // The financial year picked in the header (1 April to 31 March)
        $financialYear = FinancialYear::code();
        $yearStart = FinancialYear::start();
        $yearEnd = FinancialYear::end();

        // Balances are worked out on this day: today, or the year's last day for a past year
        $today = Carbon::today()->min($yearEnd);

        // Business operations: totals of this financial year
        $totalSale = $this->typeTotal($companyId, 'sales', $financialYear);
        $totalPurchase = $this->typeTotal($companyId, 'purchase', $financialYear);
        $totalExpenses = $this->expenseTotal($companyId, $financialYear);

        // Money to receive (Sundry Debtors) and to pay (Sundry Creditors) on that day, and at the end of the month before
        $lastMonthEnd = $today->copy()->startOfMonth()->subDay();

        $receivable = $this->balance($companyId, ['Sundry Debtors'], $today);
        $receivableBefore = $this->balance($companyId, ['Sundry Debtors'], $lastMonthEnd);

        // A creditor's balance is on the Credit side, so it is turned round to show what we owe
        $payable = -$this->balance($companyId, ['Sundry Creditors'], $today);
        $payableBefore = -$this->balance($companyId, ['Sundry Creditors'], $lastMonthEnd);

        $receivableChange = $this->change($receivable, $receivableBefore);
        $payableChange = $this->change($payable, $payableBefore);

        // The 5 newest account vouchers of the year
        $recentVouchers = Voucher::where('company_id', $companyId)
            ->where('financial_year', $financialYear)
            ->with('drLedger', 'crLedger')
            ->latest('date')->latest('id')
            ->take(5)
            ->get();

        $setupSteps = $this->setupSteps($company, $companyId);

        return view('user-management.dashboard', compact(
            'user', 'company', 'financialYear', 'yearStart', 'yearEnd', 'today',
            'totalSale', 'totalPurchase', 'totalExpenses',
            'receivable', 'payable', 'receivableChange', 'payableChange',
            'recentVouchers', 'setupSteps'
        ));
    }

    // Total amount of one voucher type (sales, purchase, ...) in a financial year
    private function typeTotal($companyId, $type, $financialYear)
    {
        return (float) Voucher::where('company_id', $companyId)
            ->where('type', $type)
            ->where('financial_year', $financialYear)
            ->sum('amount');
    }

    // Total of every voucher that debits an expense ledger (Direct / Indirect Expenses) in a financial year
    private function expenseTotal($companyId, $financialYear)
    {
        $ledgerIds = $this->ledgerIds($companyId, ['Direct Expenses', 'Indirect Expenses']);

        return (float) Voucher::where('company_id', $companyId)
            ->where('financial_year', $financialYear)
            ->whereIn('dr_ledger_id', $ledgerIds)
            ->sum('amount');
    }

    /**
     * Balance of all ledgers under some groups, as Debit minus Credit:
     * opening balance + amounts debited - amounts credited.
     * With $upTo, only vouchers up to that date are counted.
     */
    private function balance($companyId, $groupNames, $upTo = null)
    {
        $ledgerIds = $this->ledgerIds($companyId, $groupNames);

        $opening = Ledger::whereIn('id', $ledgerIds)->where('opening_type', 'Dr')->sum('opening_balance')
            - Ledger::whereIn('id', $ledgerIds)->where('opening_type', 'Cr')->sum('opening_balance');

        $vouchers = Voucher::where('company_id', $companyId)
            ->when($upTo, function ($query, $upTo) {
                $query->where('date', '<=', $upTo);
            });

        $debited = (clone $vouchers)->whereIn('dr_ledger_id', $ledgerIds)->sum('amount');
        $credited = (clone $vouchers)->whereIn('cr_ledger_id', $ledgerIds)->sum('amount');

        return round($opening + $debited - $credited, 2);
    }

    // Ids of the ledgers under some default groups (and their sub-groups)
    private function ledgerIds($companyId, $groupNames)
    {
        return Ledger::where('company_id', $companyId)
            ->whereIn('group_id', Group::idsUnder($companyId, $groupNames))
            ->pluck('id')
            ->all();
    }

    // Change from the old amount to the new one, in percent (null when there is nothing to compare with)
    private function change($now, $before)
    {
        if ($before == 0) {
            return $now == 0 ? 0 : null;
        }

        return round(($now - $before) / abs($before) * 100, 1);
    }

    // The Setup Guide: each step is done once the company has that data
    private function setupSteps($company, $companyId)
    {
        return [
            [
                'label' => 'Fill company details (GST, PAN, phone).',
                'done'  => $company && $company->gst && $company->pan && $company->company_phone_no,
                'url'   => route('company-setting'),
            ],
            [
                'label' => 'Create your ledgers (parties, bank, expenses).',
                'done'  => Ledger::where('company_id', $companyId)->exists(),
                'url'   => route('ledgers.create'),
            ],
            [
                'label' => 'Create your items.',
                'done'  => Item::where('company_id', $companyId)->exists(),
                'url'   => route('items.create'),
            ],
            [
                'label' => 'Enter opening stock of items.',
                'done'  => ItemOpening::whereIn('item_id', Item::where('company_id', $companyId)->select('id'))->exists(),
                'url'   => route('items.index'),
            ],
            [
                'label' => 'Enter your first sale.',
                'done'  => Voucher::where('company_id', $companyId)->where('type', 'sales')->exists(),
                'url'   => route('sales'),
            ],
            [
                'label' => 'Record your first material inward.',
                'done'  => StockVoucher::where('company_id', $companyId)->where('type', 'material-inward')->exists(),
                'url'   => route('material-inward'),
            ],
        ];
    }
}
