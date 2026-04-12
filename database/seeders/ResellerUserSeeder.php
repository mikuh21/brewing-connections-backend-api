<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ResellerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Reseller Test',
            'email' => 'reseller@brewhub.com',
            'password' => bcrypt('password'),
            'role' => 'reseller',
            'email_verified_at' => now(),
        ]);
    }
}
