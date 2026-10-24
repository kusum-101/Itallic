<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockVoucherLine extends Model
{
    protected $guarded = [];

    // The voucher this row belongs to
    public function stockVoucher()
    {
        return $this->belongsTo(StockVoucher::class);
    }

    // The item that came in / went out
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // The godown it came into / went out of
    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }

    // How much moved: quantity_in for an inward row, quantity_out for an outward row
    public function quantity()
    {
        return $this->quantity_in > 0 ? $this->quantity_in : $this->quantity_out;
    }
}
