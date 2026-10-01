<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerAddress extends Model
{
    protected $guarded = [];

    // The client (ledger) this address belongs to
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
