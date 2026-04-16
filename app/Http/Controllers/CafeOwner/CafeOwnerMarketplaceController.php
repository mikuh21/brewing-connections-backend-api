<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CafeOwnerMarketplaceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $userId = (int) $user->id;
        $establishment = $this->resolveOwnedEstablishment($userId);

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

            if ($type = request('type')) {
                if ($type === 'coffee_beans') {
                    $productsQuery->where('category', 'Coffee Beans');
                } elseif ($type === 'ground_coffee') {
                    $productsQuery->where('category', 'Ground Coffee');
                }
            }

            $productsQuery->latest();
        }

        $products = $productsQuery->paginate(12)->withQueryString();

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

        if ($type = request('type')) {
            if ($type === 'coffee_beans') {
                $marketplaceQuery->where('products.category', 'Coffee Beans');
            } elseif ($type === 'ground_coffee') {
                $marketplaceQuery->where('products.category', 'Ground Coffee');
            }
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
        if ($establishment && Schema::hasColumn('products', 'establishment_id')) {
            $orders = Order::with(['user:id,name', 'product:id,name,establishment_id'])
                ->whereHas('product', function ($query) use ($establishment) {
                    $query->where('establishment_id', $establishment->id);
                })
                ->latest()
                ->get();
        } elseif (Schema::hasColumn('products', 'seller_id')) {
            $orders = Order::with(['user:id,name', 'product:id,name,seller_id,seller_type'])
                ->whereHas('product', function ($query) use ($userId) {
                    $query->where('seller_id', $userId);

                    if (Schema::hasColumn('products', 'seller_type')) {
                        $query->where('seller_type', 'cafe_owner');
                    }
                })
                ->latest()
                ->get();
        }

        return view('cafe-owner.marketplace', compact('products', 'marketplaceProducts', 'orders'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

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
            'roast_level' => $validated['roast_level'] ?? null,
            'grind_type' => $validated['grind_type'] ?? null,
            'price_per_unit' => $validated['price_per_unit'],
            'unit' => $validated['unit'] ?? 'kg',
            'moq' => $validated['moq'] ?? 1,
            'stock_quantity' => $validated['stock_quantity'],
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
            $storedPath = $request->file('image')->store('products', 'public');
            $payload['image_url'] = Storage::url($storedPath);
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

        $imageUrl = $product->image_url;
        if ($request->hasFile('image') && Schema::hasColumn('products', 'image_url')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path);
        }

        $updatePayload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price_per_unit' => $validated['price_per_unit'],
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'] ?? ($product->unit ?: 'kg'),
            'moq' => $validated['moq'] ?? ($product->moq ?: 1),
        ];

        if (Schema::hasColumn('products', 'image_url')) {
            $updatePayload['image_url'] = $imageUrl;
        }

        $product->update($updatePayload);

        return back()->with('status', 'Product updated successfully.');
    }

    public function updateOrder(Request $request, Order $order)
    {
        $userId = (int) Auth::id();

        $order->loadMissing('product:id,establishment_id,seller_id,seller_type,user_id');

        if (!$order->product || !$this->productBelongsToUser($order->product, $userId)) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,canceled,cancelled',
        ]);

        $status = $validated['status'] === 'canceled' ? 'cancelled' : $validated['status'];

        $order->update([
            'status' => $status,
        ]);

        return back()->with('status', 'Order status updated successfully.');
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
