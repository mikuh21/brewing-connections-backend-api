<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. ADMIN USER
        // ─────────────────────────────────────────
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        // Ensure only the three required user accounts are kept.
        $this->call([
            CafeOwnerSeeder::class,
            ResellerUserSeeder::class,
        ]);

        $this->cleanupSeededDummyData([
            'admin@example.com',
            'cafeowner@brewhub.com',
            'reseller@brewhub.com',
        ]);

        // ─────────────────────────────────────────
        // 2. COFFEE VARIETIES
        // ─────────────────────────────────────────
        $this->call([
            CoffeeVarietySeeder::class,
        ]);

        // ─────────────────────────────────────────
        // 3. ESTABLISHMENTS
        // ─────────────────────────────────────────
        $admin = \App\Models\User::where('email', 'admin@example.com')->first();

        $establishments = [
            [
                'name' => 'Barako Brew Cafe',
                'type' => 'cafe',
                'description' => 'A cozy cafe in the heart of Lipa serving freshly brewed Kapeng Barako.',
                'address' => 'Lipa City, Batangas',
                'barangay' => 'Poblacion',
                'latitude' => 13.9411,
                'longitude' => 121.1631,
                'owner_id' => $admin->id,
            ],
            [
                'name' => 'Freya Studio Café & Bakery',
                'type' => 'cafe',
                'description' => 'A creative cafe and bakery offering artisan pastries and specialty coffee.',
                'address' => 'JP Laurel Highway, Marawoy, Lipa City',
                'barangay' => 'Marawoy',
                'latitude' => 13.9666,
                'longitude' => 121.1659,
                'owner_id' => $admin->id,
            ],
            [
                'name' => 'Grātia by: Abbey\'s Kitchenette',
                'type' => 'cafe',
                'description' => 'A pop up shop offering mouth watering and jaw dropping food selections.',
                'address' => 'Lipa City, Batangas',
                'barangay' => 'Poblacion',
                'latitude' => 13.9450,
                'longitude' => 121.1620,
                'owner_id' => $admin->id,
            ],
            [
                'name' => 'Katy\'s Farm',
                'type' => 'farm',
                'description' => 'A local Lipa farm producing quality Kapeng Barako with traditional farming methods.',
                'address' => 'Lipa City, Batangas',
                'barangay' => 'Mataas na Lupa',
                'latitude' => 13.9380,
                'longitude' => 121.1590,
                'owner_id' => $admin->id,
            ],
        ];

        foreach ($establishments as $data) {
            \App\Models\Establishment::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // ─────────────────────────────────────────
        // ─────────────────────────────────────────
        // 4. COUPON PROMOS
        // ─────────────────────────────────────────
        $freya = \App\Models\Establishment::where('name', 'Freya Studio Café & Bakery')->first();
        $gratia = \App\Models\Establishment::where('name', 'Grātia by: Abbey\'s Kitchenette')->first();

        \App\Models\CouponPromo::firstOrCreate(
            ['title' => '20% Off Premium Barako Beans'],
            [
                'establishment_id' => \App\Models\Establishment::where('name', 'Barako Brew Cafe')->first()->id,
                'description' => 'Get 20% discount on all premium Kapeng Barako coffee beans.',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'qr_code_token' => 'BARAKO20OFF',
                'valid_from' => '2026-03-15',
                'valid_until' => '2026-04-15',
                'max_usage' => 100,
                'used_count' => 45,
                'status' => 'active',
            ]
        );

        \App\Models\CouponPromo::firstOrCreate(
            ['title' => '15% Off All Pastries'],
            [
                'establishment_id' => $freya->id,
                'description' => 'Get 15% discount on all freshly baked pastries and breads at Freya Studio Café & Bakery.',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'qr_code_token' => 'FREYA15PASTRY',
                'valid_from' => '2026-03-15',
                'valid_until' => '2026-04-15',
                'max_usage' => 50,
                'used_count' => 12,
                'status' => 'active',
            ]
        );

        \App\Models\CouponPromo::firstOrCreate(
            ['title' => '10% Off Your First Visit'],
            [
                'establishment_id' => $gratia->id,
                'description' => 'Enjoy 10% off your total bill on your first visit to Grātia by Abbey\'s Kitchenette.',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'qr_code_token' => 'GRATIA10FIRST',
                'valid_from' => '2026-03-20',
                'valid_until' => '2026-04-20',
                'max_usage' => 30,
                'used_count' => 5,
                'status' => 'active',
            ]
        );

        $this->call([
            MarketplaceSeeder::class,
        ]);
    }

    private function cleanupSeededDummyData(array $allowedEmails): void
    {
        $allowedUserIds = DB::table('users')
            ->whereIn('email', $allowedEmails)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($allowedUserIds)) {
            return;
        }

        $allowedEstablishmentIds = [];
        if (Schema::hasTable('establishments')) {
            $establishmentQuery = DB::table('establishments');

            if (Schema::hasColumn('establishments', 'owner_id')) {
                $establishmentQuery->whereIn('owner_id', $allowedUserIds);
            } elseif (Schema::hasColumn('establishments', 'user_id')) {
                $establishmentQuery->whereIn('user_id', $allowedUserIds);
            } else {
                $establishmentQuery->whereRaw('1 = 0');
            }

            $allowedEstablishmentIds = $establishmentQuery
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if (Schema::hasTable('orders')) {
            DB::table('orders')->delete();
        }

        if (Schema::hasTable('bulk_orders')) {
            DB::table('bulk_orders')->delete();
        }

        if (Schema::hasTable('reseller_products')) {
            DB::table('reseller_products')->delete();
        }

        if (Schema::hasTable('products')) {
            DB::table('products')->delete();
        }

        if (Schema::hasTable('recommendations')) {
            if (Schema::hasColumn('recommendations', 'establishment_id') && !empty($allowedEstablishmentIds)) {
                DB::table('recommendations')->whereNotIn('establishment_id', $allowedEstablishmentIds)->delete();
            } else {
                DB::table('recommendations')->delete();
            }
        }

        if (Schema::hasTable('rating')) {
            if (Schema::hasColumn('rating', 'user_id')) {
                DB::table('rating')->whereNotIn('user_id', $allowedUserIds)->delete();
            }
        }

        if (Schema::hasTable('coffee_trails')) {
            if (Schema::hasColumn('coffee_trails', 'user_id')) {
                DB::table('coffee_trails')->whereNotIn('user_id', $allowedUserIds)->delete();
            } else {
                DB::table('coffee_trails')->delete();
            }
        }

        if (Schema::hasTable('coupon_promos')) {
            if (Schema::hasColumn('coupon_promos', 'establishment_id') && !empty($allowedEstablishmentIds)) {
                DB::table('coupon_promos')->whereNotIn('establishment_id', $allowedEstablishmentIds)->delete();
            } else {
                DB::table('coupon_promos')->delete();
            }
        }

        if (Schema::hasTable('messages')) {
            DB::table('messages')->delete();
        }

        if (Schema::hasTable('conversation_participants')) {
            DB::table('conversation_participants')->whereNotIn('user_id', $allowedUserIds)->delete();
        }

        if (Schema::hasTable('conversations')) {
            if (Schema::hasTable('conversation_participants') && Schema::hasColumn('conversation_participants', 'conversation_id')) {
                $conversationIds = DB::table('conversation_participants')
                    ->distinct()
                    ->pluck('conversation_id')
                    ->all();

                if (empty($conversationIds)) {
                    DB::table('conversations')->delete();
                } else {
                    DB::table('conversations')->whereNotIn('id', $conversationIds)->delete();
                }
            } else {
                DB::table('conversations')->delete();
            }
        }

        DB::table('users')->whereNotIn('email', $allowedEmails)->delete();
    }
}
