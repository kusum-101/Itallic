<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockGroup extends Model
{
    protected $guarded = [];

    // The company this stock group belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The stock group this one sits under (null for a top-level/Primary group)
    public function parent()
    {
        return $this->belongsTo(StockGroup::class, 'parent_id');
    }

    // The stock groups that sit under this one
    public function children()
    {
        return $this->hasMany(StockGroup::class, 'parent_id');
    }

    // The items that sit under this stock group
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
