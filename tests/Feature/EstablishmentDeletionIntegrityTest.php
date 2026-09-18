<?php

namespace Tests\Feature;

use App\Models\BulkOrder;
use App\Models\CoffeeTrailMarkerView;
use App\Models\CouponPromo;
use App\Models\CouponPromoRedemption;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Recommendation;
use App\Models\RecommendationSnapshot;
use App\Models\RecommendationSnapshotItem;
use App\Models\ResellerProduct;
use App\Models\User;
use App\Services\EstablishmentDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentDeletionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_establishment_deletion_removes_all_owned_records(): void
    {
        $owner = User::factory()->create();
        $seller = User::factory()->create();
        $consumer = User::factory()->create();
        $scanner = User::factory()->create();

        $establishment = Establishment::create([
            'owner_id' => $owner->id,
            'name' => 'Sunrise Farm',
            'type' => 'farm',
            'description' => 'Test estate',
            'address' => 'Test Address',
            'barangay' => 'Test Barangay',
            'latitude' => 13.95,
            'longitude' => 121.12,
        ]);

        $product = Product::create([
            'name' => 'Arabica Beans',
            'description' => 'Test beans',
            'category' => 'Coffee Beans',
            'roast_level' => 'Medium',
            'grind_type' => 'Whole Bean',
            'price_per_unit' => 250.00,
            'unit' => 'kg',
            'moq' => 1,
            'stock_quantity' => 5,
            'seller_type' => 'farm_owner',
            'seller_id' => $seller->id,
            'establishment_id' => $establishment->id,
            'is_active' => true,
        ]);

        $resellerProduct = ResellerProduct::create([
            'product_id' => $product->id,
            'reseller_id' => $seller->id,
            'reseller_price' => 320.00,
            'stock_quantity' => 4,
        ]);

        $order = Order::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 500.00,
            'status' => 'pending',
            'notes' => 'Test order',
        ]);

        $bulkOrder = BulkOrder::create([
            'reseller_id' => $seller->id,
            'product_id' => $product->id,
            'quantity_kg' => 10,
            'total_price' => 2500.00,
            'status' => 'pending',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Test bulk order',
        ]);

        $rating = Rating::create([
            'user_id' => $consumer->id,
            'establishment_id' => $establishment->id,
            'product_id' => $product->id,
            'taste_rating' => 5,
            'environment_rating' => 4,
            'cleanliness_rating' => 5,
            'service_rating' => 4,
        ]);

        $promo = CouponPromo::create([
            'establishment_id' => $establishment->id,
            'title' => 'Farm Promo',
            'description' => 'Test promo',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'qr_code_token' => 'promo-token-123',
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addDays(10)->toDateString(),
            'max_usage' => 10,
            'used_count' => 0,
            'status' => 'active',
        ]);

        $redemption = CouponPromoRedemption::create([
            'coupon_promo_id' => $promo->id,
            'consumer_user_id' => $consumer->id,
            'establishment_id' => $establishment->id,
            'scanned_by_user_id' => $scanner->id,
            'redeemed_at' => now(),
        ]);

        $recommendation = Recommendation::create([
            'establishment_id' => $establishment->id,
            'category' => 'taste',
            'priority' => 'high',
            'insight' => 'Strong bean quality',
            'suggested_action' => 'Promote this farm',
            'impact_score' => 8.1,
            'based_on_reviews' => 3,
            'generated_at' => now(),
        ]);

        $snapshot = RecommendationSnapshot::create([
            'establishment_id' => $establishment->id,
            'review_count' => 5,
            'generated_at' => now(),
        ]);

        $snapshotItem = RecommendationSnapshotItem::create([
            'recommendation_snapshot_id' => $snapshot->id,
            'category' => 'taste',
            'priority' => 'high',
            'average_score' => 4.6,
            'insight' => 'Strong taste profile',
            'suggested_action' => 'Highlight flavor notes',
            'impact_score' => 8.7,
            'based_on_reviews' => 4,
            'generated_at' => now(),
        ]);

        CoffeeTrailMarkerView::create([
            'user_id' => $consumer->id,
            'establishment_id' => $establishment->id,
            'map_session_id' => 'session-123',
            'viewed_at' => now(),
        ]);

        $service = app(EstablishmentDeletionService::class);
        $service->delete($establishment);

        $this->assertDatabaseMissing('establishments', ['id' => $establishment->id]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('reseller_products', ['id' => $resellerProduct->id]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('bulk_orders', ['id' => $bulkOrder->id]);
        $this->assertDatabaseMissing('rating', ['id' => $rating->id]);
        $this->assertDatabaseMissing('coupon_promos', ['id' => $promo->id]);
        $this->assertDatabaseMissing('coupon_promo_redemptions', ['id' => $redemption->id]);
        $this->assertDatabaseMissing('recommendations', ['id' => $recommendation->id]);
        $this->assertDatabaseMissing('recommendation_snapshots', ['id' => $snapshot->id]);
        $this->assertDatabaseMissing('recommendation_snapshot_items', ['id' => $snapshotItem->id]);
        $this->assertDatabaseMissing('coffee_trail_marker_views', ['establishment_id' => $establishment->id]);
    }
}
