<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\ResellerProduct;
use App\Models\Order;
use App\Models\BulkOrder;
use App\Models\Establishment;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. CREATE MARKETPLACE USERS
        // ─────────────────────────────────────────

        // Farm Owners
        $farmOwner1 = User::firstOrCreate(
            ['email' => 'farm.owner1@example.com'],
            [
                'name' => 'Maria Santos',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'farm_owner',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        $farmOwner2 = User::firstOrCreate(
            ['email' => 'farm.owner2@example.com'],
            [
                'name' => 'Juan dela Cruz',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'farm_owner',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        // Cafe Owners
        $cafeOwner1 = User::firstOrCreate(
            ['email' => 'cafe.owner1@example.com'],
            [
                'name' => 'Ana Reyes',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'cafe_owner',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        $cafeOwner2 = User::firstOrCreate(
            ['email' => 'cafe.owner2@example.com'],
            [
                'name' => 'Carlos Mendoza',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'cafe_owner',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => false,
            ]
        );

        // Resellers
        $reseller1 = User::firstOrCreate(
            ['email' => 'reseller1@example.com'],
            [
                'name' => 'Pedro Garcia',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'reseller',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => true,
            ]
        );

        $reseller2 = User::firstOrCreate(
            ['email' => 'reseller2@example.com'],
            [
                'name' => 'Rosa Lopez',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'reseller',
                'status' => 'active',
                'deactivated_at' => null,
                'is_verified_reseller' => true,
            ]
        );

        // Existing consumers for orders
        $consumer1 = User::where('email', 'jane@example.com')->first();
        $consumer2 = User::where('email', 'diana@example.com')->first();

        // ─────────────────────────────────────────
        // 2. GET EXISTING ESTABLISHMENTS
        // ─────────────────────────────────────────
        $barakoBrew = Establishment::where('name', 'Barako Brew Cafe')->first();
        $freyaStudio = Establishment::where('name', 'Freya Studio Café & Bakery')->first();

        // ─────────────────────────────────────────
        // 3. CREATE PRODUCTS
        // ─────────────────────────────────────────

        // Farm Owner Products
        $libericaBeans = Product::firstOrCreate(
            ['name' => 'Liberica Coffee Beans'],
            [
                'description' => 'Premium Liberica coffee beans sourced directly from local Batangas farms. Known for their unique smoky and woody flavor profile.',
                'category' => 'Coffee Beans',
                'roast_level' => 'Medium Roast',
                'grind_type' => null,
                'price_per_unit' => 800.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 150,
                'image_url' => 'products/liberica-beans.jpg',
                'seller_type' => 'farm_owner',
                'seller_id' => $farmOwner1->id,
                'establishment_id' => null,
                'is_active' => true,
            ]
        );

        $arabicaBeans = Product::firstOrCreate(
            ['name' => 'Arabica Coffee Beans'],
            [
                'description' => 'High-quality Arabica beans with smooth, mild flavor and hints of sweetness. Perfect for specialty coffee brewing.',
                'category' => 'Coffee Beans',
                'roast_level' => 'Light Roast',
                'grind_type' => null,
                'price_per_unit' => 600.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 200,
                'image_url' => 'products/arabica-beans.jpg',
                'seller_type' => 'farm_owner',
                'seller_id' => $farmOwner1->id,
                'establishment_id' => null,
                'is_active' => true,
            ]
        );

        $excelsaBeans = Product::firstOrCreate(
            ['name' => 'Excelsa Coffee Beans'],
            [
                'description' => 'Rare Excelsa variety beans offering a tart, fruity flavor profile. Adds unique character to coffee blends.',
                'category' => 'Coffee Beans',
                'roast_level' => 'Dark Roast',
                'grind_type' => null,
                'price_per_unit' => 750.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 75,
                'image_url' => 'products/excelsa-beans.jpg',
                'seller_type' => 'farm_owner',
                'seller_id' => $farmOwner2->id,
                'establishment_id' => null,
                'is_active' => true,
            ]
        );

        // Cafe Owner Products
        $barakoBlend = Product::firstOrCreate(
            ['name' => 'Barako Blend'],
            [
                'description' => 'Our signature blend featuring premium Kapeng Barako with notes of chocolate and nuts. Roasted to perfection.',
                'category' => 'Coffee Beans',
                'roast_level' => 'Medium Roast',
                'grind_type' => null,
                'price_per_unit' => 900.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 120,
                'image_url' => 'products/barako-blend.jpg',
                'seller_type' => 'cafe_owner',
                'seller_id' => $cafeOwner1->id,
                'establishment_id' => $barakoBrew->id,
                'is_active' => true,
            ]
        );

        $houseBlendGround = Product::firstOrCreate(
            ['name' => 'House Blend Ground Coffee'],
            [
                'description' => 'Freshly ground house blend perfect for drip coffee makers. Balanced flavor with medium body.',
                'category' => 'Ground Coffee',
                'roast_level' => 'Medium Roast',
                'grind_type' => 'Medium Grind',
                'price_per_unit' => 700.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 180,
                'image_url' => 'products/house-blend-ground.jpg',
                'seller_type' => 'cafe_owner',
                'seller_id' => $cafeOwner2->id,
                'establishment_id' => $freyaStudio->id,
                'is_active' => true,
            ]
        );

        // Reseller Products
        $libericaGround = Product::firstOrCreate(
            ['name' => 'Liberica Ground Coffee'],
            [
                'description' => 'Conveniently ground Liberica coffee for easy brewing. Retains the distinctive smoky flavor of whole beans.',
                'category' => 'Ground Coffee',
                'roast_level' => 'Medium Roast',
                'grind_type' => 'Coarse Grind',
                'price_per_unit' => 700.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 90,
                'image_url' => 'products/liberica-ground.jpg',
                'seller_type' => 'reseller',
                'seller_id' => $reseller1->id,
                'establishment_id' => null,
                'is_active' => true,
            ]
        );

        $arabicaDripBags = Product::firstOrCreate(
            ['name' => 'Arabica Drip Bags'],
            [
                'description' => 'Single-serve drip bags made with premium Arabica beans. Perfect for office or home use.',
                'category' => 'Merchandise',
                'roast_level' => 'Medium Roast',
                'grind_type' => 'Fine Grind',
                'price_per_unit' => 1200.00,
                'unit' => 'kg',
                'moq' => 1,
                'stock_quantity' => 60,
                'image_url' => 'products/arabica-drip-bags.jpg',
                'seller_type' => 'reseller',
                'seller_id' => $reseller2->id,
                'establishment_id' => null,
                'is_active' => true,
            ]
        );

        // ─────────────────────────────────────────
        // 4. CREATE RESELLER PRODUCTS
        // ─────────────────────────────────────────

        // Reseller 1 selling farm owner products with markup
        ResellerProduct::firstOrCreate(
            ['product_id' => $libericaBeans->id, 'reseller_id' => $reseller1->id],
            [
                'reseller_price' => 920.00, // 15% markup
                'stock_quantity' => 25,
            ]
        );

        ResellerProduct::firstOrCreate(
            ['product_id' => $arabicaBeans->id, 'reseller_id' => $reseller1->id],
            [
                'reseller_price' => 690.00, // 15% markup
                'stock_quantity' => 30,
            ]
        );

        // Reseller 2 selling farm owner products with markup
        ResellerProduct::firstOrCreate(
            ['product_id' => $excelsaBeans->id, 'reseller_id' => $reseller2->id],
            [
                'reseller_price' => 862.50, // 15% markup
                'stock_quantity' => 15,
            ]
        );

        ResellerProduct::firstOrCreate(
            ['product_id' => $libericaBeans->id, 'reseller_id' => $reseller2->id],
            [
                'reseller_price' => 880.00, // 10% markup
                'stock_quantity' => 20,
            ]
        );

        // ─────────────────────────────────────────
        // 5. CREATE ORDERS
        // ─────────────────────────────────────────

        Order::firstOrCreate(
            ['user_id' => $consumer1->id, 'product_id' => $libericaBeans->id, 'created_at' => '2026-03-20 10:00:00'],
            [
                'quantity' => 2,
                'total_price' => 1600.00,
                'status' => 'completed',
                'notes' => 'Great quality beans!',
            ]
        );

        Order::firstOrCreate(
            ['user_id' => $consumer2->id, 'product_id' => $barakoBlend->id, 'created_at' => '2026-03-21 14:30:00'],
            [
                'quantity' => 1,
                'total_price' => 900.00,
                'status' => 'confirmed',
                'notes' => 'Excited to try this blend',
            ]
        );

        Order::firstOrCreate(
            ['user_id' => $consumer1->id, 'product_id' => $houseBlendGround->id, 'created_at' => '2026-03-22 09:15:00'],
            [
                'quantity' => 3,
                'total_price' => 2100.00,
                'status' => 'pending',
                'notes' => null,
            ]
        );

        Order::firstOrCreate(
            ['user_id' => $consumer2->id, 'product_id' => $arabicaDripBags->id, 'created_at' => '2026-03-23 16:45:00'],
            [
                'quantity' => 1,
                'total_price' => 1200.00,
                'status' => 'completed',
                'notes' => 'Perfect for the office',
            ]
        );

        Order::firstOrCreate(
            ['user_id' => $consumer1->id, 'product_id' => $excelsaBeans->id, 'created_at' => '2026-03-24 11:20:00'],
            [
                'quantity' => 2,
                'total_price' => 1500.00,
                'status' => 'cancelled',
                'notes' => 'Changed my mind',
            ]
        );

        // ─────────────────────────────────────────
        // 6. CREATE BULK ORDERS
        // ─────────────────────────────────────────

        BulkOrder::firstOrCreate(
            ['reseller_id' => $reseller1->id, 'product_id' => $libericaBeans->id, 'created_at' => '2026-03-18 08:00:00'],
            [
                'quantity_kg' => 50.5,
                'total_price' => 40400.00,
                'status' => 'completed',
                'delivery_date' => '2026-03-25',
                'notes' => 'Bulk order for wholesale distribution',
            ]
        );

        BulkOrder::firstOrCreate(
            ['reseller_id' => $reseller2->id, 'product_id' => $arabicaBeans->id, 'created_at' => '2026-03-19 13:30:00'],
            [
                'quantity_kg' => 25.0,
                'total_price' => 15000.00,
                'status' => 'confirmed',
                'delivery_date' => '2026-03-28',
                'notes' => 'Need this by end of month',
            ]
        );

        BulkOrder::firstOrCreate(
            ['reseller_id' => $reseller1->id, 'product_id' => $excelsaBeans->id, 'created_at' => '2026-03-20 15:45:00'],
            [
                'quantity_kg' => 30.0,
                'total_price' => 22500.00,
                'status' => 'pending',
                'delivery_date' => null,
                'notes' => 'Please confirm availability',
            ]
        );
    }
}
