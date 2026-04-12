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

        $products = [
            [
                'name' => 'Signature Espresso Blend',
                'description' => 'A bold and smooth espresso blend with notes of dark chocolate and caramel.',
                'category' => 'Ground Coffee',
                'price' => 350.00,
                'stock' => 50,
            ],
            [
                'name' => 'Single Origin Arabica Beans',
                'description' => 'Light roast Arabica beans sourced from Benguet highlands.',
                'category' => 'Coffee Beans',
                'price' => 420.00,
                'stock' => 30,
            ],
        ];

        foreach ($products as $product) {
            $productData = [
                'name' => $product['name'],
                'description' => $product['description'],
                'category' => $product['category'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('products', 'user_id')) {
                $productData['user_id'] = $cafeOwnerId;
            }

            if (Schema::hasColumn('products', 'seller_id')) {
                $productData['seller_id'] = $cafeOwnerId;
            }

            if (Schema::hasColumn('products', 'seller_type')) {
                $productData['seller_type'] = 'cafe_owner';
            }

            if ($establishmentId && Schema::hasColumn('products', 'establishment_id')) {
                $productData['establishment_id'] = $establishmentId;
            }

            if (Schema::hasColumn('products', 'price')) {
                $productData['price'] = $product['price'];
            }

            if (Schema::hasColumn('products', 'price_per_unit')) {
                $productData['price_per_unit'] = $product['price'];
            }

            if (Schema::hasColumn('products', 'stock')) {
                $productData['stock'] = $product['stock'];
            }

            if (Schema::hasColumn('products', 'stock_quantity')) {
                $productData['stock_quantity'] = $product['stock'];
            }

            if (Schema::hasColumn('products', 'unit')) {
                $productData['unit'] = 'kg';
            }

            if (Schema::hasColumn('products', 'moq')) {
                $productData['moq'] = 1;
            }

            if (Schema::hasColumn('products', 'is_active')) {
                $productData['is_active'] = true;
            }

            DB::table('products')->updateOrInsert(
                ['name' => $product['name']],
                $productData
            );
        }

        $randomUserIds = DB::table('users')
            ->whereNot('email', 'cafeowner@brewhub.com')
            ->inRandomOrder()
            ->limit(5)
            ->pluck('id')
            ->values();

        $scores = [5, 4, 4, 3, 2];

        foreach ($scores as $index => $score) {
            $fallbackUserId = $randomUserIds->isNotEmpty()
                ? $randomUserIds[$index % $randomUserIds->count()]
                : $cafeOwnerId;

            $ratingData = [
                'user_id' => (int) $fallbackUserId,
                'establishment_id' => $establishmentId,
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('rating', 'score')) {
                $ratingData['score'] = $score;
            }

            if (Schema::hasColumn('rating', 'taste_rating')) {
                $ratingData['taste_rating'] = $score;
            }

            if (Schema::hasColumn('rating', 'environment_rating')) {
                $ratingData['environment_rating'] = $score;
            }

            if (Schema::hasColumn('rating', 'cleanliness_rating')) {
                $ratingData['cleanliness_rating'] = $score;
            }

            if (Schema::hasColumn('rating', 'service_rating')) {
                $ratingData['service_rating'] = $score;
            }

            if (Schema::hasColumn('rating', 'owner_response')) {
                $ratingData['owner_response'] = null;
            }

            if (Schema::hasColumn('rating', 'image')) {
                $ratingData['image'] = null;
            }

            DB::table('rating')->insert($ratingData);
        }
    }
}
