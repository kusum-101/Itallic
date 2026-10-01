<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $guarded = [];

    // The company this ledger belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The group this ledger sits under
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // The billing and shipping addresses of this ledger
    public function addresses()
    {
        return $this->hasMany(LedgerAddress::class);
    }
}
