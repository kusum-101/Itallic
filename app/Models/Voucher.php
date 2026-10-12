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

    public function isVerified()
    {
        return $this->status == 'Verified';
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
