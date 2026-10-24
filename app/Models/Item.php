<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $guarded = [];

    // The company this item belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The unit the item is counted in (Nos, Kg, ...)
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // The stock group this item sits under (null = Primary)
    public function stockGroup()
    {
        return $this->belongsTo(StockGroup::class);
    }

    // The stock category of this item (null = none)
    public function stockCategory()
    {
        return $this->belongsTo(StockCategory::class);
    }

    // The opening stock of this item, one row per godown
    public function openings()
    {
        return $this->hasMany(ItemOpening::class);
    }

    // Every inventory voucher row with this item (Material Inward, ...)
    public function stockLines()
    {
        return $this->hasMany(StockVoucherLine::class);
    }
}
