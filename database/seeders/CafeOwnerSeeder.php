<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CafeOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $userData = [
            'name' => 'Cafe Owner Test',
            'email' => 'cafeowner@brewhub.com',
            'password' => Hash::make('password'),
            'role' => 'cafe_owner',
            'email_verified_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('users', 'status')) {
            $userData['status'] = 'active';
        }

        if (Schema::hasColumn('users', 'deactivated_at')) {
            $userData['deactivated_at'] = null;
        }

        if (Schema::hasColumn('users', 'is_verified_reseller')) {
            $userData['is_verified_reseller'] = false;
        }

        if (Schema::hasColumn('users', 'created_at')) {
            $userData['created_at'] = $now;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'cafeowner@brewhub.com'],
            $userData
        );

        $cafeOwnerId = (int) DB::table('users')
            ->where('email', 'cafeowner@brewhub.com')
            ->value('id');

        $establishmentData = [
            'name' => 'BrewHub Cafe Test',
            'description' => 'A cozy specialty coffee cafe serving single-origin brews and artisanal blends.',
            'address' => '123 Coffee Street',
            'city' => 'Lipa',
            'province' => 'Batangas',
            'type' => 'cafe',
            'contact_number' => '09171234567',
            'operating_hours' => 'Mon-Sun 7:00 AM - 9:00 PM',
            'latitude' => 13.9411,
            'longitude' => 121.1631,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('establishments', 'user_id')) {
            $establishmentData['user_id'] = $cafeOwnerId;
        }

        if (Schema::hasColumn('establishments', 'owner_id')) {
            $establishmentData['owner_id'] = $cafeOwnerId;
        }

        if (!Schema::hasColumn('establishments', 'operating_hours') && Schema::hasColumn('establishments', 'visit_hours')) {
            $establishmentData['visit_hours'] = 'Mon-Sun 7:00 AM - 9:00 PM';
            unset($establishmentData['operating_hours']);
        }

        // Remove keys that do not exist in the current schema.
        foreach (array_keys($establishmentData) as $column) {
            if (!Schema::hasColumn('establishments', $column)) {
                unset($establishmentData[$column]);
            }
        }

        DB::table('establishments')->updateOrInsert(
            ['name' => 'BrewHub Cafe Test'],
            $establishmentData
        );

        $establishmentId = (int) DB::table('establishments')
            ->where('name', 'BrewHub Cafe Test')
            ->value('id');
    }
}
