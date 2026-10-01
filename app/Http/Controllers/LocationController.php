<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;

// District / City dropdowns: filled after picking a State / District
class LocationController extends Controller
{
    // The districts of a state, as JSON [{id, name}]
    public function districts($stateId)
    {
        return response()->json(District::listOf($stateId));
    }

    // The cities of a district, as JSON [{id, name}]
    public function cities($districtId)
    {
        return response()->json(City::listOf($districtId));
    }
}
