<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MarketplaceController;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderReceiptNotifier;
use App\Services\OrderStockManager;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ApiMarketplaceCancellationEmailTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_cancelled_order_sends_status_update_email(): void
    {
        $consumer = User::factory()->make([
            'id' => 101,
            'name' => 'Test Consumer',
            'email' => 'consumer@example.com',
        ]);

        $seller = User::factory()->make([
            'id' => 202,
            'name' => 'Test Seller',
        ]);

        $establishment = new Establishment([
            'id' => 303,
            'name' => 'Test Farm',
            'type' => 'farm',
        ]);

        $product = new Product([
            'id' => 404,
            'name' => 'Barako Beans',
            'category' => 'Coffee Beans',
            'price_per_unit' => 285.00,
            'unit' => 'kg',
            'seller_type' => 'farm_owner',
            'seller_id' => $seller->id,
            'establishment_id' => $establishment->id,
        ]);
        $product->setRelation('seller', $seller);
        $product->setRelation('establishment', $establishment);

        /** @var Order&\Mockery\MockInterface $order */
        $order = Mockery::mock(Order::class)->makePartial();
        $order->forceFill([
            'id' => 505,
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'total_price' => 570.00,
            'status' => 'pending',
            'notes' => json_encode([
                'receipt_email' => 'consumer@example.com',
                'full_name' => 'Test Consumer',
                'address' => 'Brgy. Pusil',
                'phone' => '09123456789',
                'source' => 'mobile-app',
                'receipt_token' => 'test-token',
            ], JSON_THROW_ON_ERROR),
            'pickup_date' => now()->addDay(),
            'pickup_time' => '15:00',
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);
        $order->setRelation('user', $consumer);
        $order->setRelation('product', $product);
        $order->setRelation('productRating', null);
        $order->shouldReceive('load')->once()->andReturnSelf();

        $stockManager = Mockery::mock(OrderStockManager::class);
        $stockManager->shouldReceive('applyStatusTransition')
            ->once()
            ->with($order, 'cancelled')
            ->andReturnUsing(function (Order $order): Order {
                $order->status = 'cancelled';

                return $order;
            });

        $notifier = Mockery::mock(OrderReceiptNotifier::class);
        $notifier->shouldReceive('sendStatusUpdated')
            ->once()
            ->with($order);

        $controller = new MarketplaceController($stockManager, $notifier);

        $request = Request::create('/api/orders/505', 'PATCH', [
            'status' => 'cancelled',
        ]);
        $request->setUserResolver(static fn () => $consumer);

        $response = $controller->updateOrder($request, $order);

        $this->assertSame(200, $response->getStatusCode());
        $payload = $response->getData(true);

        $this->assertSame('Order cancelled successfully.', $payload['message']);
        $this->assertSame('cancelled', $payload['order']['status']);
    }
}