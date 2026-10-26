<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date'        => 'date',
        'verified_at' => 'datetime',
    ];

    // The company this voucher belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The ledger on the Debit side
    public function drLedger()
    {
        return $this->belongsTo(Ledger::class, 'dr_ledger_id');
    }

    // The ledger on the Credit side
    public function crLedger()
    {
        return $this->belongsTo(Ledger::class, 'cr_ledger_id');
    }

    // The document proofs uploaded with this voucher
    public function documents()
    {
        return $this->hasMany(VoucherDocument::class);
    }

    // The user who created this voucher
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // The user who verified this voucher
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // A Sales voucher sent to another company on this software
    public function sentTo()
    {
        return $this->hasOne(InterCompanyEntry::class, 'sale_voucher_id');
    }

    // A Purchase voucher booked from another company's sale
    public function receivedFrom()
    {
        return $this->hasOne(InterCompanyEntry::class, 'purchase_voucher_id');
    }

    public function isVerified()
    {
        return $this->status == 'Verified';
    }

    /**
     * Next serial of a voucher type in a financial year. Call inside a DB transaction:
     * lockForUpdate makes a second user saving at the same moment wait, so both can't get the same number.
     */
    public static function nextSerial($companyId, $type, $financialYear)
    {
        return self::where('company_id', $companyId)
            ->where('type', $type)
            ->where('financial_year', $financialYear)
            ->lockForUpdate()
            ->max('serial') + 1;
    }

    /**
     * Financial year of a date. It runs April to March:
     * 09-Oct-2026 -> "26-27", 15-Feb-2027 -> "26-27"
     */
    public static function financialYear($date)
    {
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;

        return substr($startYear, -2) . '-' . substr($startYear + 1, -2);
    }
}
