<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockVoucher extends Model
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

    // The supplier / customer the material came from or went to (can be null)
    public function party()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    // One row per item: item, godown, quantity, rate, value
    public function lines()
    {
        return $this->hasMany(StockVoucherLine::class);
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
}
