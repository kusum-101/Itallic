<?php

namespace App\Support;

use App\Models\Company;
use App\Models\StockVoucher;
use App\Models\Voucher;
use Carbon\Carbon;

/**
 * The financial year the user is working in (April to March), like Tally's "period".
 * It is picked from the header and kept in the session; until then it is the running year.
 * A year is known by the year it starts in: 2026 = 1 Apr 2026 to 31 Mar 2027.
 */
class FinancialYear
{
    // The running year: 24-Oct-2026 -> 2026, 15-Feb-2027 -> 2026
    public static function current()
    {
        $today = Carbon::today();

        return $today->month >= 4 ? $today->year : $today->year - 1;
    }

    // The year picked in the header (the running year if none was picked)
    public static function selected()
    {
        return (int) session('financial_year', self::current());
    }

    // 1 April of the year
    public static function start($year = null)
    {
        return Carbon::create($year ?? self::selected(), 4, 1);
    }

    // 31 March of the next year
    public static function end($year = null)
    {
        return Carbon::create(($year ?? self::selected()) + 1, 3, 31);
    }

    // "2026-27", for showing
    public static function label($year = null)
    {
        $year = $year ?? self::selected();

        return $year . '-' . substr($year + 1, -2);
    }

    // "26-27", the way vouchers store it (Voucher::financialYear)
    public static function code($year = null)
    {
        $year = $year ?? self::selected();

        return substr($year, -2) . '-' . substr($year + 1, -2);
    }

    // The date a new voucher starts with: today, or the year's last day when another year is picked
    public static function defaultDate()
    {
        $today = Carbon::today();

        if ($today->lt(self::start())) {
            return self::start();
        }

        return $today->gt(self::end()) ? self::end() : $today;
    }

    // Newest first: next year, the running year, and back to the oldest year that has vouchers
    // (at least one year before the company was made, for entering old data)
    public static function options($companyId)
    {
        $oldest = min(
            self::current() - 1,
            self::of(Voucher::where('company_id', $companyId)->min('date')),
            self::of(StockVoucher::where('company_id', $companyId)->min('date')),
            self::of(Company::where('id', $companyId)->value('created_at')) - 1,
        );

        return range(self::current() + 1, $oldest);
    }

    // The year a date falls in (the running year when there is no date)
    public static function of($date)
    {
        if (!$date) {
            return self::current();
        }

        $date = Carbon::parse($date);

        return $date->month >= 4 ? $date->year : $date->year - 1;
    }
}
