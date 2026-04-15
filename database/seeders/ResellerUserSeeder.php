<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ResellerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'reseller@brewhub.com',
        ], [
            'name' => 'Reseller Test',
            'password' => bcrypt('password'),
            'role' => 'reseller',
            'email_verified_at' => now(),
        ]);
    }
}
