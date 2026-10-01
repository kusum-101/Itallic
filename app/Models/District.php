<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    public $timestamps = false;

    // The districts of a state for a dropdown (cached for good, they never change)
    public static function listOf($stateId)
    {
        return State::cached("districts.$stateId", function () use ($stateId) {
            return self::where('state_id', $stateId)->orderBy('name')->get(['id', 'name']);
        });
    }

    // The state this district is in
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // The cities (tehsils) in this district
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
