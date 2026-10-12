<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherDocument extends Model
{
    protected $guarded = [];

    // The voucher this document proof belongs to
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
