<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    protected $guarded = [];

    // The company this stock category belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The stock group this category belongs to
    public function stockGroup()
    {
        return $this->belongsTo(StockGroup::class);
    }

    // The stock category this one sat under before categories went under stock groups (old data only)
    public function parent()
    {
        return $this->belongsTo(StockCategory::class, 'parent_id');
    }

    // The stock categories that sit under this one
    public function children()
    {
        return $this->hasMany(StockCategory::class, 'parent_id');
    }

    // The items in this stock category
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
