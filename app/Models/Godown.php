<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Godown extends Model
{
    protected $guarded = [];

    // The company this godown belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The opening stock rows kept in this godown
    public function openings()
    {
        return $this->hasMany(ItemOpening::class);
    }

    /**
     * Create the default "Main Location" godown (like Tally) for a newly
     * created company. Does nothing if the company already has godowns.
     */
    public static function seedDefaults($companyId)
    {
        if (self::where('company_id', $companyId)->exists()) {
            return;
        }

        self::create([
            'company_id' => $companyId,
            'name'       => 'Main Location',
            'is_primary' => true,
        ]);
    }
}
