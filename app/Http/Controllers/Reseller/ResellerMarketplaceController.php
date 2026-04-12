<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ResellerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ResellerMarketplaceController extends Controller
{
    public function index()
    {
        $userId = (int) Auth::id();

        $baseQuery = ResellerProduct::query();

        if (Schema::hasTable('products')) {
            $baseQuery->join('products', 'products.id', '=', 'reseller_products.product_id');
        }

        if (Schema::hasTable('users') && Schema::hasColumn('products', 'seller_id')) {
            $baseQuery->leftJoin('users as sellers', 'sellers.id', '=', 'products.seller_id');
        }

        if (Schema::hasTable('users')) {
            $baseQuery->leftJoin('users as resellers', 'resellers.id', '=', 'reseller_products.reseller_id');
        }

        if ($search = trim((string) request('search'))) {
            $searchLower = mb_strtolower($search);
            $baseQuery->where(function ($builder) use ($searchLower) {
                $builder->whereRaw('LOWER(products.name) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(products.category) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(products.description) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(sellers.name, \"\")) LIKE ?', ['%' . $searchLower . '%'])
                    ->orWhereRaw('LOWER(COALESCE(resellers.name, \"\")) LIKE ?', ['%' . $searchLower . '%']);
            });
        }

        if ($type = request('type')) {
            if ($type === 'coffee_beans') {
                $baseQuery->where('products.category', 'Coffee Beans');
            } elseif ($type === 'ground_coffee') {
                $baseQuery->where('products.category', 'Ground Coffee');
            }
        }

        if (($minPrice = request('min_price')) !== null && $minPrice !== '') {
            $baseQuery->where('reseller_products.reseller_price', '>=', (float) $minPrice);
        }

        if (($maxPrice = request('max_price')) !== null && $maxPrice !== '') {
            $baseQuery->where('reseller_products.reseller_price', '<=', (float) $maxPrice);
        }

        $selectColumns = [
            'reseller_products.id',
            'reseller_products.product_id',
            'reseller_products.reseller_id',
            'products.name',
            'products.description',
            'products.category',
            'reseller_products.reseller_price as price_per_unit',
            'reseller_products.stock_quantity',
            'products.unit',
            'products.moq',
            'products.image_url',
            'products.seller_type',
            'products.seller_id',
            'sellers.name as seller_name',
            'resellers.name as reseller_name',
        ];

        $products = (clone $baseQuery)
            ->where('reseller_products.reseller_id', $userId)
            ->select([
                ...$selectColumns,
            ])
            ->orderByDesc('reseller_products.updated_at')
            ->paginate(12, ['*'], 'my_page')
            ->withQueryString();

        $marketplaceQuery = Product::query();

        if (Schema::hasTable('users') && Schema::hasColumn('products', 'seller_id')) {
            $marketplaceQuery->leftJoin('users as sellers', 'sellers.id', '=', 'products.seller_id');
        }

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

        if (Schema::hasTable('reseller_products') && Schema::hasColumn('reseller_products', 'reseller_id') && Schema::hasColumn('reseller_products', 'product_id')) {
            $marketplaceQuery->whereNotIn('products.id', function ($query) use ($userId) {
                $query->from('reseller_products')
                    ->select('product_id')
                    ->where('reseller_id', $userId);
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

        if ($type = request('type')) {
            if ($type === 'coffee_beans') {
                $marketplaceQuery->where('products.category', 'Coffee Beans');
            } elseif ($type === 'ground_coffee') {
                $marketplaceQuery->where('products.category', 'Ground Coffee');
            }
        }

        if (($minPrice = request('min_price')) !== null && $minPrice !== '') {
            $marketplaceQuery->where('products.price_per_unit', '>=', (float) $minPrice);
        }

        if (($maxPrice = request('max_price')) !== null && $maxPrice !== '') {
            $marketplaceQuery->where('products.price_per_unit', '<=', (float) $maxPrice);
        }

        $marketplaceProducts = $marketplaceQuery
            ->select([
                'products.id',
                'products.name',
                'products.description',
                'products.category',
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
            ->paginate(12, ['*'], 'market_page')
            ->withQueryString();

        $orders = collect();
        if (Schema::hasTable('orders') && Schema::hasTable('products') && Schema::hasColumn('products', 'seller_id')) {
            $orders = Order::query()
                ->with(['user:id,name', 'product:id,name,seller_id,seller_type'])
                ->whereHas('product', function ($builder) use ($userId) {
                    $builder->where('seller_id', $userId);

                    if (Schema::hasColumn('products', 'seller_type')) {
                        $builder->where('seller_type', 'reseller');
                    }
                })
                ->latest()
                ->get();
        }

        return view('reseller.marketplace', compact('products', 'marketplaceProducts', 'orders'));
    }

    public function store(Request $request)
    {
        $userId = (int) Auth::id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:Coffee Beans,Ground Coffee',
            'roast_level' => 'nullable|string|max:100',
            'grind_type' => 'nullable|string|max:100',
            'price_per_unit' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'moq' => 'nullable|integer|min:1',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        $productPayload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'roast_level' => $validated['roast_level'] ?? null,
            'grind_type' => $validated['grind_type'] ?? null,
            'price_per_unit' => $validated['price_per_unit'],
            'unit' => $validated['unit'] ?? 'kg',
            'moq' => $validated['moq'] ?? 1,
            'stock_quantity' => $validated['stock_quantity'],
            'is_active' => true,
        ];

        if (Schema::hasColumn('products', 'seller_id')) {
            $productPayload['seller_id'] = $userId;
        }

        if (Schema::hasColumn('products', 'seller_type')) {
            $productPayload['seller_type'] = 'reseller';
        }

        if ($request->hasFile('image') && Schema::hasColumn('products', 'image_url')) {
            $storedPath = $request->file('image')->store('products', 'public');
            $productPayload['image_url'] = Storage::url($storedPath);
        }

        $product = Product::create($productPayload);

        ResellerProduct::create([
            'product_id' => $product->id,
            'reseller_id' => $userId,
            'reseller_price' => $validated['price_per_unit'],
            'stock_quantity' => $validated['stock_quantity'],
        ]);

        return back()->with('status', 'Product created successfully.');
    }

    public function updateProduct(Request $request, ResellerProduct $product)
    {
        $userId = (int) Auth::id();

        if ((int) $product->reseller_id !== $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:Coffee Beans,Ground Coffee',
            'price_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'moq' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:5120',
        ]);

        $productModel = Product::query()->find($product->product_id);

        if ($productModel) {
            $productPayload = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'unit' => $validated['unit'] ?? ($productModel->unit ?: 'kg'),
                'moq' => $validated['moq'] ?? ($productModel->moq ?: 1),
            ];

            if (Schema::hasColumn('products', 'image_url') && $request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $productPayload['image_url'] = Storage::url($path);
            }

            if (Schema::hasColumn('products', 'price_per_unit')) {
                $productPayload['price_per_unit'] = $validated['price_per_unit'];
            }

            if (Schema::hasColumn('products', 'stock_quantity')) {
                $productPayload['stock_quantity'] = $validated['stock_quantity'];
            }

            $productModel->update($productPayload);
        }

        $product->update([
            'reseller_price' => $validated['price_per_unit'],
            'stock_quantity' => $validated['stock_quantity'],
        ]);

        return back()->with('status', 'Product updated successfully.');
    }

    public function updateOrder(Request $request, Order $order)
    {
        $userId = (int) Auth::id();

        $order->loadMissing('product:id,seller_id,seller_type');

        if (!$order->product || (int) $order->product->seller_id !== $userId) {
            abort(403);
        }

        if (Schema::hasColumn('products', 'seller_type') && (string) $order->product->seller_type !== 'reseller') {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,canceled,cancelled',
        ]);

        $status = $validated['status'] === 'cancelled' ? 'canceled' : $validated['status'];

        $order->update([
            'status' => $status,
        ]);

        return back()->with('status', 'Order status updated successfully.');
    }
}
