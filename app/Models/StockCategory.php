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

    // The stock category this one sits under (null for a top-level/Primary category)
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
