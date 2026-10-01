<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;

    // The cities of a district for a dropdown (cached for good, they never change)
    public static function listOf($districtId)
    {
        return State::cached("cities.$districtId", function () use ($districtId) {
            return self::where('district_id', $districtId)->orderBy('name')->get(['id', 'name']);
        });
    }

    // The district this city is in
    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
