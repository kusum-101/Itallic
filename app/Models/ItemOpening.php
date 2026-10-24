<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemOpening extends Model
{
    protected $guarded = [];

    // The item this opening stock belongs to
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // The godown the stock is kept in
    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }
}
