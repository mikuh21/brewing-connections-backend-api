<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ResellerProduct;
use App\Services\DeliveryFeeEstimator;
use App\Services\MenuImageExtractionService;
use App\Services\OrderReceiptNotifier;
use App\Services\OrderStockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CafeOwnerMarketplaceController extends Controller
{
    public function __construct(
        private readonly MenuImageExtractionService $menuImageExtractionService,
        private readonly OrderReceiptNotifier $orderReceiptNotifier,
        private readonly OrderStockManager $orderStockManager,
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $userId = (int) $user->id;
        $establishment = $this->resolveOwnedEstablishment($userId);
        $typeCategoryMap = [
            'coffee_beans' => 'Coffee Beans',
            'ground_coffee' => 'Ground Coffee',
            'hot_drinks' => 'Hot Drinks',
            'iced_drinks' => 'Iced Drinks',
            'iced_blended_drinks' => 'Iced Blended Drinks',
            'tea' => 'Tea',
            'rice_meals' => 'Rice Meals',
            'pastas' => 'Pastas',
            'appetizers' => 'Appetizers',
            'sandwiches' => 'Sandwiches',
            'burgers' => 'Burgers',
            'pastries' => 'Pastries',
        ];

        $productsQuery = Product::query()->whereRaw('1 = 0');

        if ($establishment || Schema::hasColumn('products', 'seller_id') || Schema::hasColumn('products', 'user_id')) {
            $productsQuery = Product::query();

            if ($establishment && Schema::hasColumn('products', 'establishment_id')) {
                $productsQuery->where('establishment_id', $establishment->id);
            } elseif (Schema::hasColumn('products', 'seller_id')) {
                $productsQuery->where('seller_id', $userId);

                if (Schema::hasColumn('products', 'seller_type')) {
                    $productsQuery->where('seller_type', 'cafe_owner');
                }
            } elseif (Schema::hasColumn('products', 'user_id')) {
                $productsQuery->where('user_id', $userId);
            }

            if ($search = trim((string) request('search'))) {
                $searchLower = mb_strtolower($search);
                $productsQuery->where(function ($query) use ($searchLower) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchLower . '%'])
                        ->orWhereRaw('LOWER(category) LIKE ?', ['%' . $searchLower . '%']);
                });
            }

            if (($type = request('type')) && isset($typeCategoryMap[$type])) {
                $productsQuery->where('category', $typeCategoryMap[$type]);
            }

            $productsQuery->latest();
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        $marketplaceQuery = Product::query();

        if (Schema::hasTable('users') && Schema::hasColumn('products', 'seller_id')) {
            $marketplaceQuery->leftJoin('users as sellers', 'sellers.id', '=', 'products.seller_id');
        }

        if (
            Schema::hasTable('reseller_products')
            && Schema::hasColumn('reseller_products', 'product_id')
            && Schema::hasColumn('reseller_products', 'reseller_id')
            && Schema::hasColumn('reseller_products', 'reseller_price')
            && Schema::hasColumn('products', 'seller_id')
        ) {
            $marketplaceQuery->leftJoin('reseller_products as reseller_prices', function ($join) {
                $join->on('reseller_prices.product_id', '=', 'products.id')
                    ->on('reseller_prices.reseller_id', '=', 'products.seller_id');
            });
        }

        if (Schema::hasColumn('products', 'seller_type')) {
            $marketplaceQuery->whereIn('products.seller_type', ['farm_owner', 'reseller', 'roaster']);
        }

        $marketplaceQuery->whereIn('products.category', ['Coffee Beans', 'Ground Coffee']);

        if (Schema::hasColumn('products', 'is_active')) {
            $marketplaceQuery->where(function ($builder) {
                $builder->where('products.is_active', true)
                    ->orWhereNull('products.is_active');
            });
        }

        if (Schema::hasColumn('products', 'seller_id')) {
            $marketplaceQuery->where(function ($builder) use ($userId) {
                $builder->whereNull('products.seller_id')
                    ->orWhere('products.seller_id', '!=', $userId);
            });
        }

        if (Schema::hasColumn('products', 'user_id')) {
            $marketplaceQuery->where(function ($builder) use ($userId) {
                $builder->whereNull('products.user_id')
                    ->orWhere('products.user_id', '!=', $userId);
            });
        }

        if ($establishment && Schema::hasColumn('products', 'establishment_id')) {
            $marketplaceQuery->where(function ($builder) use ($establishment) {
                $builder->whereNull('products.establishment_id')
                    ->orWhere('products.establishment_id', '!=', (int) $establishment->id);
            });
        }

        if ($search = trim((string) request('search'))) {
            $searchLower = mb_strtolower($search);
            $marketplaceQuery->where(function ($builder) use ($searchLower) {
                $builder->whereRaw('LOWER(products.name) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(products.category) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(products.description) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(sellers.name, "")) LIKE ?', ['%' . $searchLower . '%']);
            });
        }

        if (($type = request('type')) && isset($typeCategoryMap[$type])) {
            $marketplaceQuery->where('products.category', $typeCategoryMap[$type]);
        }

        $marketplaceProducts = $marketplaceQuery
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.category',
                DB::raw(Schema::hasColumn('products', 'weight_grams') ? 'products.weight_grams' : 'NULL as weight_grams'),
                DB::raw(Schema::hasTable('reseller_products')
                    && Schema::hasColumn('reseller_products', 'product_id')
                    && Schema::hasColumn('reseller_products', 'reseller_id')
                    && Schema::hasColumn('reseller_products', 'reseller_price')
                    && Schema::hasColumn('products', 'seller_id')
                    ? 'COALESCE(reseller_prices.reseller_price, products.price_per_unit) as order_price'
                    : 'products.price_per_unit as order_price'),
                'products.price_per_unit',
                'products.stock_quantity',
                'products.unit',
                'products.moq',
                'products.image_url',
                'products.seller_type',
                'products.seller_id',
                'sellers.name as seller_name',
            ])
            ->orderByDesc('products.updated_at')
            ->get();

        $orders = Order::query()
            ->with(['product.seller'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(50)
            ->get();

        return view('cafe-owner.marketplace', compact('products', 'marketplaceProducts', 'orders'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $menuCategories = [
            'Hot Drinks',
            'Iced Drinks',
            'Iced Blended Drinks',
            'Tea',
            'Rice Meals',
            'Pastas',
            'Appetizers',
            'Sandwiches',
            'Burgers',
            'Pastries',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:' . implode(',', $menuCategories),
            'image' => 'nullable|image|max:5120',
        ]);

        $establishment = Establishment::query()
            ->when(
                Schema::hasColumn('establishments', 'user_id'),
                fn ($query) => $query->where('user_id', $userId),
                fn ($query) => $query->where('owner_id', $userId)
            )
            ->first();

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'roast_level' => null,
            'grind_type' => null,
            'price_per_unit' => 0,
            'unit' => 'serving',
            'moq' => 1,
            'stock_quantity' => 10,
            'is_active' => true,
        ];

        if (Schema::hasColumn('products', 'user_id')) {
            $payload['user_id'] = $userId;
        }

        if (Schema::hasColumn('products', 'seller_id')) {
            $payload['seller_id'] = $userId;
        }

        if (Schema::hasColumn('products', 'seller_type')) {
            $payload['seller_type'] = 'cafe_owner';
        }

        if ($establishment && Schema::hasColumn('products', 'establishment_id')) {
            $payload['establishment_id'] = $establishment->id;
        }

        if ($request->hasFile('image') && Schema::hasColumn('products', 'image_url')) {
            $storedPath = $request->file('image')->store('products', 'supabase');
            $payload['image_url'] = $storedPath;
        }

        Product::create($payload);

        return back()->with('status', 'Product created successfully.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if (!$this->productBelongsToUser($product, (int) $user->id)) {
            abort(403);
        }

        $productCategories = [
            'Coffee Beans',
            'Ground Coffee',
            'Hot Drinks',
            'Iced Drinks',
            'Iced Blended Drinks',
            'Tea',
            'Rice Meals',
            'Pastas',
            'Appetizers',
            'Sandwiches',
            'Burgers',
            'Pastries',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:' . implode(',', $productCategories),
            'stock_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'moq' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:5120',
        ]);

        $isCoffeeProduct = in_array($validated['category'], ['Coffee Beans', 'Ground Coffee'], true);

        if ($isCoffeeProduct && !array_key_exists('stock_quantity', $validated)) {
            return back()
                ->withErrors(['stock_quantity' => 'Stock quantity is required for Coffee Beans and Ground Coffee.'])
                ->withInput();
        }

        $imageUrl = $product->getRawOriginal('image_url') ?: $product->image_url;
        if ($request->hasFile('image') && Schema::hasColumn('products', 'image_url')) {
            $path = $request->file('image')->store('products', 'supabase');
            $imageUrl = $path;
        }

        $updatePayload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'stock_quantity' => $validated['stock_quantity'] ?? ($product->stock_quantity ?? ($isCoffeeProduct ? 0 : 10)),
            'unit' => $validated['unit'] ?? ($product->unit ?: ($isCoffeeProduct ? 'kg' : 'serving')),
            'moq' => $validated['moq'] ?? ($product->moq ?: 1),
        ];

        if (Schema::hasColumn('products', 'image_url')) {
            $updatePayload['image_url'] = $imageUrl;
        }

        $product->update($updatePayload);

        return back()->with('status', 'Product updated successfully.');
    }

    public function extractMenuItems(Request $request)
    {
        $validated = $request->validate([
            'menu_image' => ['required', 'image', 'max:10240'],
        ]);

        try {
            $items = $this->menuImageExtractionService->extractDraftProducts($validated['menu_image']);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Menu image extracted successfully.',
            'items' => $items,
        ]);
    }

    public function confirmExtractedMenuItems(Request $request)
    {
        $userId = (int) Auth::id();

        $menuCategories = implode(',', [
            'Coffee Beans',
            'Ground Coffee',
            'Hot Drinks',
            'Iced Drinks',
            'Iced Blended Drinks',
            'Tea',
            'Rice Meals',
            'Pastas',
            'Appetizers',
            'Sandwiches',
            'Burgers',
            'Pastries',
        ]);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.category' => ['required', 'in:' . $menuCategories],
        ]);

        $establishment = $this->resolveOwnedEstablishment($userId);
        $createdProducts = [];

        foreach ($validated['items'] as $item) {
            $payload = [
                'name' => trim((string) $item['name']),
                'description' => $item['description'] ?? null,
                'category' => $item['category'],
                'price_per_unit' => 0,
                'stock_quantity' => 10,
                'unit' => 'serving',
                'moq' => 1,
                'is_active' => true,
            ];

            if (Schema::hasColumn('products', 'user_id')) {
                $payload['user_id'] = $userId;
            }

            if (Schema::hasColumn('products', 'seller_id')) {
                $payload['seller_id'] = $userId;
            }

            if (Schema::hasColumn('products', 'seller_type')) {
                $payload['seller_type'] = 'cafe_owner';
            }

            if ($establishment && Schema::hasColumn('products', 'establishment_id')) {
                $payload['establishment_id'] = $establishment->id;
            }

            $createdProduct = Product::create($payload);
            $createdProducts[] = [
                'id' => (int) $createdProduct->id,
                'name' => (string) $createdProduct->name,
            ];
        }

        return response()->json([
            'message' => 'Menu items imported successfully.',
            'created_count' => count($createdProducts),
            'products' => $createdProducts,
        ]);
    }

    public function updateProductVisibility(Request $request, Product $product)
    {
        $userId = (int) Auth::id();

        if (!$this->productBelongsToUser($product, $userId)) {
            abort(403);
        }

        if (!Schema::hasColumn('products', 'is_active')) {
            return response()->json([
                'message' => 'Product visibility is not supported in this environment.',
            ], 422);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $product->update([
            'is_active' => (bool) $validated['is_active'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product visibility updated successfully.',
                'product' => [
                    'id' => (int) $product->id,
                    'is_active' => (bool) $product->is_active,
                ],
            ]);
        }

        return back()->with('status', 'Product visibility updated successfully.');
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_mode' => ['required', 'in:pickup,cod'],
            'pickup_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'required_if:payment_mode,pickup'],
            'pickup_time' => ['nullable', 'date_format:H:i', 'required_if:payment_mode,pickup'],
            'address' => ['nullable', 'required_if:payment_mode,cod', 'string', 'max:255'],
            'phone' => ['nullable', 'required_if:payment_mode,cod', 'string', 'regex:/^\\d{11}$/'],
            'buyer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $buyer = Auth::user();

        $order = DB::transaction(function () use ($validated, $buyer) {
            $product = Product::query()->lockForUpdate()->findOrFail((int) $validated['product_id']);
            $sellerType = strtolower((string) ($product->seller_type ?? ''));

            if (!in_array($sellerType, ['farm_owner', 'reseller', 'roaster'], true)
                || !in_array((string) $product->category, ['Coffee Beans', 'Ground Coffee'], true)) {
                abort(422, 'Only Coffee Beans and Ground Coffee from approved supplier listings can be ordered here.');
            }

            if ((int) ($product->seller_id ?? 0) === (int) $buyer->id) {
                abort(422, 'You cannot order your own listing.');
            }

            if ($product->is_active === false || $product->is_active === 0 || $product->is_active === '0') {
                abort(422, 'This product is currently unavailable.');
            }

            $price = (float) ($product->price_per_unit ?? 0);
            $availableStock = max(0, (int) ($product->stock_quantity ?? 0));

            if ($sellerType === 'reseller') {
                $resellerListing = ResellerProduct::query()
                    ->where('product_id', $product->id)
                    ->where('reseller_id', $product->seller_id)
                    ->lockForUpdate()
                    ->first();

                if ($resellerListing) {
                    $price = (float) $resellerListing->reseller_price;
                    $availableStock = min($availableStock, max(0, (int) $resellerListing->stock_quantity));
                }
            }

            if ($price <= 0) {
                abort(422, 'This supplier has not set a valid price for this product yet.');
            }

            $quantity = (int) $validated['quantity'];
            $minimumQuantity = max(1, (int) ($product->moq ?? 1));

            if ($quantity < $minimumQuantity) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'The minimum order quantity is ' . $minimumQuantity . '.',
                ]);
            }

            if ($availableStock <= 0 || $quantity > $availableStock) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Requested quantity exceeds available stock (' . $availableStock . ').',
                ]);
            }

            $receiptToken = Str::random(40);
            $productTotal = round($price * $quantity, 2);
            $paymentMode = (string) $validated['payment_mode'];
            $deliveryFee = $paymentMode === 'cod'
                ? DeliveryFeeEstimator::estimateForProduct($product, $quantity)
                : 0.0;
            $totalAmount = round($productTotal + $deliveryFee, 2);
            $deliveryAddress = trim((string) ($validated['address'] ?? ''));
            $deliveryPhone = trim((string) ($validated['phone'] ?? ''));
            $metadata = [
                'source' => 'cafe-owner-store',
                'buyer_name' => (string) ($buyer->name ?? 'Coffee Shop Owner'),
                'full_name' => (string) ($buyer->name ?? 'Coffee Shop Owner'),
                'receipt_email' => (string) ($buyer->email ?? ''),
                'receipt_token' => $receiptToken,
                'seller_type' => $sellerType,
                'buyer_notes' => trim((string) ($validated['buyer_notes'] ?? '')),
                'payment_mode' => $paymentMode,
                'product_total' => $productTotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'address' => $paymentMode === 'cod' ? $deliveryAddress : '',
                'phone' => $paymentMode === 'cod' ? $deliveryPhone : '',
                'pickup_date' => $paymentMode === 'pickup' ? ($validated['pickup_date'] ?? null) : null,
                'pickup_time' => $paymentMode === 'pickup' ? ($validated['pickup_time'] ?? null) : null,
            ];

            return Order::query()->create([
                'user_id' => (int) $buyer->id,
                'product_id' => (int) $product->id,
                'quantity' => $quantity,
                'total_price' => $productTotal,
                'total_amount' => $totalAmount,
                'delivery_fee' => $deliveryFee,
                'payment_mode' => $paymentMode,
                'status' => 'pending',
                'stock_reserved' => false,
                'pickup_date' => $paymentMode === 'pickup' ? ($validated['pickup_date'] ?? null) : null,
                'pickup_time' => $paymentMode === 'pickup' ? ($validated['pickup_time'] ?? null) : null,
                'notes' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            ]);
        });

        try {
            $this->orderReceiptNotifier->sendOrderCreated($order);
        } catch (\Throwable $exception) {
            Log::warning('Cafe owner order receipt notification failed.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('cafe-owner.marketplace', ['tab' => 'orders'])
            ->with('status', 'Your order/reservation was submitted. The supplier will review it.');
    }

    public function cancelOrder(Order $order)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 403);

        if (strtolower((string) $order->status) !== 'pending') {
            return redirect()
                ->route('cafe-owner.marketplace', ['tab' => 'orders'])
                ->withErrors(['order' => 'Only pending orders can be cancelled.']);
        }

        $order = $this->orderStockManager->applyStatusTransition($order, 'cancelled');

        try {
            $this->orderReceiptNotifier->sendStatusUpdated($order);
        } catch (\Throwable $exception) {
            Log::warning('Cafe owner order cancellation notification failed.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('cafe-owner.marketplace', ['tab' => 'orders'])
            ->with('status', 'Order cancelled successfully.');
    }

    protected function resolveOwnedEstablishment(int $userId): ?Establishment
    {
        $query = Establishment::query();

        if (Schema::hasColumn('establishments', 'user_id')) {
            return $query->where('user_id', $userId)->first();
        }

        if (Schema::hasColumn('establishments', 'owner_id')) {
            return $query->where('owner_id', $userId)->first();
        }

        return null;
    }

    protected function productBelongsToUser(Product $product, int $userId): bool
    {
        if (Schema::hasColumn('products', 'establishment_id')) {
            $establishment = $this->resolveOwnedEstablishment($userId);
            if ($establishment && (int) $product->establishment_id === (int) $establishment->id) {
                return true;
            }
        }

        if (Schema::hasColumn('products', 'seller_id') && (int) $product->seller_id === $userId) {
            if (!Schema::hasColumn('products', 'seller_type')) {
                return true;
            }

            return (string) $product->seller_type === 'cafe_owner';
        }

        if (Schema::hasColumn('products', 'user_id') && (int) $product->user_id === $userId) {
            return true;
        }

        return false;
    }
}
