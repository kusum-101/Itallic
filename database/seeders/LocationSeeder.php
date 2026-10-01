<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the states, districts and cities tables from database/data/*.json.
 * Data: India's Local Government Directory (LGD), Sep 2026. Cities are the
 * sub-districts (tehsils); Mumbai and Kolkata have none, so the district
 * itself is their one city.
 *
 * Run: php artisan db:seed --class=LocationSeeder
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Already filled: nothing to do
        if (DB::table('states')->exists()) {
            return;
        }

        // Each file is a list of rows: states [id, name], districts/cities [id, parent_id, name]
        $load = function ($file) {
            return json_decode(file_get_contents(database_path("data/$file.json")), true);
        };

        DB::transaction(function () use ($load) {
            $states = array_map(fn ($row) => ['id' => $row[0], 'name' => $row[1]], $load('states'));
            DB::table('states')->insert($states);

            $districts = array_map(fn ($row) => ['id' => $row[0], 'state_id' => $row[1], 'name' => $row[2]], $load('districts'));
            DB::table('districts')->insert($districts);

            $cities = array_map(fn ($row) => ['id' => $row[0], 'district_id' => $row[1], 'name' => $row[2]], $load('cities'));

            // Insert in chunks so the query doesn't get too big
            foreach (array_chunk($cities, 1000) as $chunk) {
                DB::table('cities')->insert($chunk);
            }
        });
    }
}
