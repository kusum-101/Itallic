<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterAdmin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        MasterAdmin::create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'), // Aapka login password
        ]);
    }
}
