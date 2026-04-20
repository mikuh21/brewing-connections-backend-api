<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderStockManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    public function __construct(
        private readonly OrderStockManager $orderStockManager,
    ) {
    }

    public function products(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $productsQuery = Product::query()
            ->with([
                'seller:id,name',
                'establishment:id,name,type',
            ])
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('is_active');
            })
            ->where('stock_quantity', '>', 0)
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $searchLike = '%' . mb_strtolower($search) . '%';
            $productsQuery->where(function ($query) use ($searchLike) {
                $query->whereRaw('LOWER(name) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(category) LIKE ?', [$searchLike])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$searchLike]);
            });
        }

        $products = $productsQuery->limit(150)->get()->map(function (Product $product) {
            return [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'description' => (string) ($product->description ?? ''),
                'category' => (string) ($product->category ?? ''),
                'roast_level' => $product->roast_level,
                'grind_type' => $product->grind_type,
                'price_per_unit' => (float) ($product->price_per_unit ?? 0),
                'unit' => (string) ($product->unit ?? 'kg'),
                'moq' => max(1, (int) ($product->moq ?? 1)),
                'stock_quantity' => max(0, (int) ($product->stock_quantity ?? 0)),
                'image_url' => $product->image_url,
                'seller_type' => (string) ($product->seller_type ?? ''),
                'seller_id' => $product->seller_id ? (int) $product->seller_id : null,
                'seller_user_id' => $product->seller_id ? (int) $product->seller_id : null,
                'seller_name' => (string) ($product->seller?->name ?? 'Seller'),
                'establishment_id' => $product->establishment_id ? (int) $product->establishment_id : null,
                'establishment_name' => (string) ($product->establishment?->name ?? ''),
                'establishment_type' => (string) ($product->establishment?->type ?? ''),
            ];
        })->values();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with([
                'product:id,name,category,price_per_unit,unit,image_url,seller_type,seller_id,establishment_id',
                'product.seller:id,name',
                'product.establishment:id,name,type',
            ])
            ->where('user_id', (int) $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Order $order) => $this->serializeOrder($order))
            ->values();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'pickup_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            /** @var Product|null $product */
            $product = Product::query()
                ->lockForUpdate()
                ->find($validated['product_id']);

            if (!$product) {
                abort(422, 'Product not found.');
            }

            if ($product->is_active === false) {
                abort(422, 'This product is currently unavailable.');
            }

            $requestedQty = (int) $validated['quantity'];
            $minimumQty = max(1, (int) ($product->moq ?? 1));

            if ($requestedQty < $minimumQty) {
                abort(422, 'Quantity is below minimum order quantity.');
            }

            $availableStock = max(0, (int) ($product->stock_quantity ?? 0));
            if ($requestedQty > $availableStock) {
                abort(422, 'Not enough stock available for this order.');
            }

            $totalPrice = round(((float) $product->price_per_unit) * $requestedQty, 2);

            return Order::query()->create([
                'user_id' => (int) $request->user()->id,
                'product_id' => (int) $product->id,
                'quantity' => $requestedQty,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'stock_reserved' => false,
                'notes' => $validated['notes'] ?? null,
                'pickup_date' => $validated['pickup_date'] ?? null,
                'pickup_time' => $validated['pickup_time'] ?? null,
            ]);
        });

        $order->load([
            'product:id,name,category,price_per_unit,unit,image_url,seller_type,seller_id,establishment_id',
            'product.seller:id,name',
            'product.establishment:id,name,type',
        ]);

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => $this->serializeOrder($order),
        ], 201);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403, 'You are not allowed to update this order.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:cancelled,canceled'],
        ]);

        $currentStatus = strtolower((string) ($order->status ?? 'pending'));
        if (in_array($currentStatus, ['completed', 'cancelled', 'canceled'], true)) {
            return response()->json([
                'message' => 'This order can no longer be cancelled.',
                'order' => $this->serializeOrder($order->loadMissing([
                    'product:id,name,category,price_per_unit,unit,image_url,seller_type,seller_id,establishment_id',
                    'product.seller:id,name',
                    'product.establishment:id,name,type',
                ])),
            ], 422);
        }

        $order = $this->orderStockManager->applyStatusTransition($order, 'cancelled');

        $order->load([
            'product:id,name,category,price_per_unit,unit,image_url,seller_type,seller_id,establishment_id',
            'product.seller:id,name',
            'product.establishment:id,name,type',
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order' => $this->serializeOrder($order),
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id' => (int) $order->id,
            'status' => (string) ($order->status ?? 'pending'),
            'quantity' => (int) ($order->quantity ?? 0),
            'total_price' => (float) ($order->total_price ?? 0),
            'notes' => $order->notes,
            'pickup_date' => optional($order->pickup_date)?->toDateString() ?? $order->pickup_date,
            'pickup_time' => $order->pickup_time,
            'created_at' => optional($order->created_at)?->toIso8601String(),
            'updated_at' => optional($order->updated_at)?->toIso8601String(),
            'product' => [
                'id' => (int) ($order->product?->id ?? 0),
                'name' => (string) ($order->product?->name ?? 'Product'),
                'category' => (string) ($order->product?->category ?? ''),
                'price_per_unit' => (float) ($order->product?->price_per_unit ?? 0),
                'unit' => (string) ($order->product?->unit ?? 'kg'),
                'image_url' => $order->product?->image_url,
                'seller_type' => (string) ($order->product?->seller_type ?? ''),
                'seller_id' => $order->product?->seller_id ? (int) $order->product->seller_id : null,
                'seller_user_id' => $order->product?->seller_id ? (int) $order->product->seller_id : null,
                'seller_name' => (string) ($order->product?->seller?->name ?? 'Seller'),
                'establishment_name' => (string) ($order->product?->establishment?->name ?? ''),
            ],
        ];
    }
}
