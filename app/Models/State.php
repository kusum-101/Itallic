<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class State extends Model
{
    public $timestamps = false;

    // All states for a dropdown. States never change, so the list is cached for good
    public static function list()
    {
        return self::cached('states', function () {
            return self::orderBy('name')->get(['id', 'name']);
        });
    }

    /**
     * Cache a dropdown list for good, but never an empty one: an empty list
     * means the data isn't seeded yet, so it's read again next time.
     * Used by District and City too.
     */
    public static function cached($key, $query)
    {
        $list = Cache::get($key);

        if ($list === null || $list->isEmpty()) {
            $list = $query();

            if ($list->isNotEmpty()) {
                Cache::forever($key, $list);
            }
        }

        return $list;
    }

    // The districts in this state
    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
