<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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

        // ─────────────────────────────────────────
        // 2. CONSUMER USERS
        // ─────────────────────────────────────────
        \App\Models\User::firstOrCreate(
            ['email' => 'jane@example.com'],
            [
                'name' => 'Jane Smith',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'consumer',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'diana@example.com'],
            [
                'name' => 'Diana Davis',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'consumer',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        // ─────────────────────────────────────────
        // 3. COFFEE VARIETIES
        // ─────────────────────────────────────────
        $varieties = [
            [
                'name' => 'Liberica',
                'color' => '#4A6741',
                'description' => 'A rare coffee species native to the Philippines, known for its distinctive smoky, woody, and floral flavor with a full body and irregular-shaped beans.',
            ],
            [
                'name' => 'Excelsa',
                'color' => '#D4AF37',
                'description' => 'Grown mostly in Southeast Asia, Excelsa produces a tart, fruity, and complex flavor profile. It is often used to add depth and character to coffee blends.',
            ],
            [
                'name' => 'Robusta',
                'color' => '#8B4513',
                'description' => 'A hardy and widely cultivated coffee species known for its strong, bitter taste and high caffeine content. Commonly used in espresso blends and instant coffee.',
            ],
            [
                'name' => 'Arabica',
                'color' => '#C0392B',
                'description' => 'The most popular coffee species worldwide, prized for its smooth, mild flavor with hints of sweetness and acidity. Grown at high altitudes for optimal quality.',
            ],
        ];

        foreach ($varieties as $variety) {
            \App\Models\CoffeeVariety::firstOrCreate(
                ['name' => $variety['name']],
                $variety
            );
        }

        // ─────────────────────────────────────────
        // 4. ESTABLISHMENTS
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
        // 5. RATINGS
        // ─────────────────────────────────────────
        $diana = \App\Models\User::where('email', 'diana@example.com')->first();
        $jane = \App\Models\User::where('email', 'jane@example.com')->first();
        $freya = \App\Models\Establishment::where('name', 'Freya Studio Café & Bakery')->first();
        $gratia = \App\Models\Establishment::where('name', 'Grātia by: Abbey\'s Kitchenette')->first();
        $katys = \App\Models\Establishment::where('name', 'Katy\'s Farm')->first();

        if (\App\Models\Rating::count() === 0) {
            // Diana reviews Freya - high rating green
            \App\Models\Rating::create([
                'user_id' => $diana->id,
                'establishment_id' => $freya->id,
                'taste_rating' => 5,
                'environment_rating' => 5,
                'cleanliness_rating' => 4,
                'service_rating' => 5,
                'image' => 'ratings/sample1.jpg',
                'owner_response' => 'Thank you so much for your kind words, Diana! We are thrilled you enjoyed your experience at Freya Studio. Hope to see you again soon!',
            ]);

            // Diana reviews Gratia - low rating red
            \App\Models\Rating::create([
                'user_id' => $diana->id,
                'establishment_id' => $gratia->id,
                'taste_rating' => 2,
                'environment_rating' => 2,
                'cleanliness_rating' => 3,
                'service_rating' => 2,
                'image' => null,
                'owner_response' => 'We are sorry to hear about your experience, Diana. Please reach out to us directly so we can make it right for you.',
            ]);

            // Jane reviews Katy's Farm - medium rating yellow
            \App\Models\Rating::create([
                'user_id' => $jane->id,
                'establishment_id' => $katys->id,
                'taste_rating' => 3,
                'environment_rating' => 3,
                'cleanliness_rating' => 3,
                'service_rating' => 3,
                'image' => null,
                'owner_response' => null,
            ]);
        }

        // ─────────────────────────────────────────
        // 6. COUPON PROMOS
        // ─────────────────────────────────────────
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

        // ─────────────────────────────────────────
        // 7. MARKETPLACE DATA
        // ─────────────────────────────────────────
        $this->call([
            MarketplaceSeeder::class,
            CafeOwnerSeeder::class,
            ResellerUserSeeder::class,
        ]);
    }
}
