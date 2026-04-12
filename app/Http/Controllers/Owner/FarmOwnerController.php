<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CoffeeVariety;
use App\Models\Conversation;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FarmOwnerController extends Controller
{
    protected function getVerifiedResellersForMap()
    {
        return User::query()
            ->where('role', 'reseller')
            ->where('is_verified_reseller', true)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'deactivated');
            })
            ->whereNull('deactivated_at')
            ->orderBy('name')
            ->get(['id', 'name', 'barangay', 'latitude', 'longitude', 'updated_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'barangay' => $user->barangay,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'verified_at' => optional($user->updated_at)?->toIso8601String(),
                ];
            })
            ->values();
    }

    public function dashboard()
    {
        $user = Auth::user();
        $establishment = $user?->establishment;

        if (!$establishment) {
            return view('farm-owner.dashboard', [
                'productsListed' => 0,
                'ordersThisWeek' => 0,
                'totalVisits' => 0,
                'farmClicks' => 0,
                'recentActivity' => collect(),
            ]);
        }

        $establishmentId = $establishment->id;

        $productsListed = Product::where('establishment_id', $establishmentId)->count();

        $ordersThisWeek = Order::where('created_at', '>=', now()->subDays(7))
            ->whereHas('product', function ($query) use ($establishmentId) {
                $query->where('establishment_id', $establishmentId);
            })
            ->count();

        $totalVisits = $this->resolveTrailVisitsCount($establishmentId);
        $farmClicks = $this->resolveFarmClicksCount($establishmentId);

        $latestOrders = Order::with('product:id,name,establishment_id')
            ->whereHas('product', function ($query) use ($establishmentId) {
                $query->where('establishment_id', $establishmentId);
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Order $order) {
                return [
                    'type' => 'order',
                    'title' => 'Order #' . $order->id . ' (' . ucfirst($order->status) . ')',
                    'meta' => $order->product?->name,
                    'occurred_at' => $order->created_at,
                ];
            });

        $latestProducts = Product::where('establishment_id', $establishmentId)
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function (Product $product) {
                $isUpdated = $product->updated_at && $product->updated_at->gt($product->created_at);

                return [
                    'type' => 'product',
                    'title' => ($isUpdated ? 'Updated product: ' : 'Added product: ') . $product->name,
                    'meta' => $product->category,
                    'occurred_at' => $product->updated_at ?? $product->created_at,
                ];
            });

        $recentActivity = collect($latestOrders)
            ->merge(collect($latestProducts))
            ->filter(fn ($item) => !empty($item['occurred_at']))
            ->sortByDesc('occurred_at')
            ->take(5)
            ->values();

        return view('farm-owner.dashboard', compact(
            'productsListed',
            'ordersThisWeek',
            'totalVisits',
            'farmClicks',
            'recentActivity'
        ));
    }

    protected function resolveTrailVisitsCount(int $establishmentId): int
    {
        if (!Schema::hasTable('coffee_trails')) {
            return 0;
        }

        $query = DB::table('coffee_trails');

        if (Schema::hasColumn('coffee_trails', 'establishment_id')) {
            return (int) $query->where('establishment_id', $establishmentId)->count();
        }

        if (Schema::hasColumn('coffee_trails', 'establishment_ids')) {
            return (int) $query->whereJsonContains('establishment_ids', $establishmentId)->count();
        }

        if (Schema::hasColumn('coffee_trails', 'trail_data')) {
            return (int) $query
                ->where(function ($subQuery) use ($establishmentId) {
                    $subQuery->whereJsonContains('trail_data->establishment_ids', $establishmentId)
                        ->orWhereJsonContains('trail_data->establishments', $establishmentId);
                })
                ->count();
        }

        return 0;
    }

    protected function resolveFarmClicksCount(int $establishmentId): int
    {
        if (Schema::hasTable('map_views')) {
            $query = DB::table('map_views');

            if (Schema::hasColumn('map_views', 'establishment_id')) {
                return (int) $query->where('establishment_id', $establishmentId)->count();
            }

            if (Schema::hasColumn('map_views', 'marker_establishment_id')) {
                return (int) $query->where('marker_establishment_id', $establishmentId)->count();
            }
        }

        if (Schema::hasTable('analytics')) {
            $query = DB::table('analytics');

            if (Schema::hasColumn('analytics', 'establishment_id')) {
                if (Schema::hasColumn('analytics', 'event_name')) {
                    return (int) $query
                        ->where('establishment_id', $establishmentId)
                        ->whereIn('event_name', ['map_view', 'marker_view', 'marker_click'])
                        ->count();
                }

                if (Schema::hasColumn('analytics', 'event_type')) {
                    return (int) $query
                        ->where('establishment_id', $establishmentId)
                        ->whereIn('event_type', ['map_view', 'marker_view', 'marker_click'])
                        ->count();
                }

                return (int) $query->where('establishment_id', $establishmentId)->count();
            }
        }

        return 0;
    }

    public function myFarm()
    {
        return $this->show();
    }

    public function updateMyFarm(Request $request)
    {
        return $this->update($request);
    }

    public function show()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment()->with('varieties')->first();

        if (!$establishment) {
            $establishment = Establishment::create([
                'owner_id' => $user->id,
                'name' => $user->name . "'s Farm",
                'type' => 'farm',
            ])->load('varieties');
        }

        if ((int) $establishment->owner_id !== (int) $user->id) {
            abort(403);
        }

        $allVarieties = CoffeeVariety::query()->orderBy('name')->get();
        $selectedVarietyIds = $establishment->varieties->pluck('id')->all();
        $primaryVarietyId = optional($establishment->varieties->firstWhere('pivot.is_primary', true))->id;

        return view('farm-owner.my-farm', compact(
            'establishment',
            'allVarieties',
            'selectedVarietyIds',
            'primaryVarietyId'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $establishment = $user->establishment;

        if (!$establishment) {
            abort(404, 'Farm profile not found.');
        }

        if ((int) $establishment->owner_id !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'address' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'operating_hours' => 'nullable|string|max:255',
            'banner_focus_x' => 'nullable|integer|between:0,100',
            'banner_focus_y' => 'nullable|integer|between:0,100',
            'profile_focus_x' => 'nullable|integer|between:0,100',
            'profile_focus_y' => 'nullable|integer|between:0,100',
            'varieties' => 'nullable|array',
            'varieties.*' => 'integer|exists:coffee_varieties,id',
            'primary_variety' => 'nullable|integer|exists:coffee_varieties,id',
        ]);

        $updatePayload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'] ?? null,
            'barangay' => $validated['barangay'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
        ];

        foreach (['banner_focus_x', 'banner_focus_y', 'profile_focus_x', 'profile_focus_y'] as $field) {
            if (Schema::hasColumn('establishments', $field) && array_key_exists($field, $validated)) {
                $updatePayload[$field] = (int) ($validated[$field] ?? 50);
            }
        }

        if (Schema::hasColumn('establishments', 'visit_hours')) {
            $updatePayload['visit_hours'] = $validated['operating_hours'] ?? null;
        }

        if (Schema::hasColumn('establishments', 'operating_hours')) {
            $updatePayload['operating_hours'] = $validated['operating_hours'] ?? null;
        }

        if ($request->hasFile('image') && Schema::hasColumn('establishments', 'image')) {
            $path = $request->file('image')->store('establishments', 'public');
            $updatePayload['image'] = Storage::url($path);
        }

        $establishment->update($updatePayload);

        $varietyIds = collect($validated['varieties'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $primaryVarietyId = isset($validated['primary_variety']) ? (int) $validated['primary_variety'] : null;

        $syncPayload = $varietyIds
            ->mapWithKeys(function (int $varietyId) use ($primaryVarietyId) {
                return [$varietyId => ['is_primary' => $primaryVarietyId === $varietyId]];
            })
            ->all();

        $establishment->varieties()->sync($syncPayload);

        // Always bump profile timestamp after a successful profile update flow.
        $establishment->touch();

        return redirect()
            ->route('farm-owner.my-farm')
            ->with('status', 'Farm profile updated successfully.');
    }

    public function marketplace()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment;

        $productsQuery = Product::query()->whereRaw('1 = 0');

        if ($establishment && (int) $establishment->owner_id === (int) $user->id) {
            $productsQuery = Product::query()
                ->where('establishment_id', $establishment->id)
                ->latest();

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
            $marketplaceQuery->where(function ($builder) use ($user) {
                $builder->whereNull('products.seller_id')
                    ->orWhere('products.seller_id', '!=', (int) $user->id);
            });
        }

        if (Schema::hasColumn('products', 'user_id')) {
            $marketplaceQuery->where(function ($builder) use ($user) {
                $builder->whereNull('products.user_id')
                    ->orWhere('products.user_id', '!=', (int) $user->id);
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
        if ($establishment && (int) $establishment->owner_id === (int) $user->id) {
            $orders = Order::with(['user:id,name', 'product:id,name,establishment_id'])
                ->whereHas('product', function ($query) use ($establishment) {
                    $query->where('establishment_id', $establishment->id);
                })
                ->latest()
                ->get();
        }

        return view('farm-owner.marketplace', compact('products', 'marketplaceProducts', 'orders'));
    }

    public function updateMarketplaceOrder(Request $request, Order $order)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment;

        if (!$establishment || (int) $establishment->owner_id !== (int) $user->id) {
            abort(403);
        }

        $order->loadMissing('product:id,establishment_id');

        if ((int) optional($order->product)->establishment_id !== (int) $establishment->id) {
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

    public function notifications()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment;

        $orderNotifications = collect();
        $pendingOrdersCount = 0;

        if ($establishment && (int) $establishment->owner_id === (int) $user->id) {
            $orderQuery = Order::with(['user:id,name', 'product:id,name,establishment_id'])
                ->whereHas('product', function ($query) use ($establishment) {
                    $query->where('establishment_id', $establishment->id);
                });

            $pendingOrdersCount = (clone $orderQuery)
                ->where('status', 'pending')
                ->count();

            $orderNotifications = $orderQuery
                ->latest()
                ->limit(5)
                ->get()
                ->map(function (Order $order) {
                    return [
                        'id' => 'order-' . $order->id . '-' . strtolower((string) $order->status),
                        'type' => 'order',
                        'title' => 'New order from ' . (optional($order->user)->name ?? 'Customer'),
                        'subtitle' => (optional($order->product)->name ?? 'Product') . ' • Qty: ' . (int) $order->quantity,
                        'status' => strtolower((string) $order->status),
                        'time' => optional($order->created_at)->diffForHumans(),
                        'timestamp' => optional($order->created_at)?->timestamp ?? 0,
                        'url' => route('farm-owner.marketplace') . '#orders',
                    ];
                });
        }

        $chatNotifications = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()
            ->with(['latestMessage.sender:id,name'])
            ->get();

        foreach ($conversations as $conversation) {
            $unread = $conversation->unreadCount($user->id);
            if ($unread <= 0) {
                continue;
            }

            $unreadChatCount += $unread;

            $participant = $conversation->participants()
                ->where('user_id', $user->id)
                ->first();

            $latestUnreadIncoming = $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $user->id)
                ->when(!empty($participant?->last_read_at), function ($query) use ($participant) {
                    $query->where('created_at', '>', $participant->last_read_at);
                }, function ($query) {
                    $query->whereNull('read_at');
                })
                ->latest()
                ->first();

            $latestIncoming = $latestUnreadIncoming ?: $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $user->id)
                ->latest()
                ->first();

            if (!$latestIncoming) {
                continue;
            }

            $messageId = $latestIncoming->id ?? $conversation->id;
            $subtitle = $latestIncoming->body
                ? \Illuminate\Support\Str::limit((string) $latestIncoming->body, 60)
                : ('You have ' . $unread . ' unread message' . ($unread > 1 ? 's' : ''));

            $chatNotifications->push([
                'id' => 'chat-' . $conversation->id . '-' . $messageId . '-u' . $unread,
                'type' => 'chat',
                'title' => 'New message from ' . (optional($latestIncoming->sender)->name ?? 'User'),
                'subtitle' => $subtitle,
                'status' => 'unread',
                'time' => optional($latestIncoming->created_at)->diffForHumans(),
                'timestamp' => optional($latestIncoming->created_at)?->timestamp ?? 0,
                'unread_count' => $unread,
                'url' => route('farm-owner.messages.show', $conversation),
            ]);
        }

        $items = collect($orderNotifications)
            ->merge(collect($chatNotifications))
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        return response()->json([
            'counts' => [
                'pending_orders' => $pendingOrdersCount,
                'unread_chats' => $unreadChatCount,
                'total' => $pendingOrdersCount + $unreadChatCount,
            ],
            'items' => $items,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function storeMarketplaceProduct(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment;

        if (!$establishment || (int) $establishment->owner_id !== (int) $user->id) {
            abort(403);
        }

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

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path);
        }

        Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'roast_level' => $validated['roast_level'] ?? null,
            'grind_type' => $validated['grind_type'] ?? null,
            'price_per_unit' => $validated['price_per_unit'],
            'unit' => $validated['unit'] ?? 'kg',
            'moq' => $validated['moq'] ?? 1,
            'stock_quantity' => $validated['stock_quantity'],
            'image_url' => $imageUrl,
            'seller_type' => 'farm_owner',
            'seller_id' => $user->id,
            'establishment_id' => $establishment->id,
            'is_active' => true,
        ]);

        return back()->with('status', 'Product created successfully.');
    }

    public function updateMarketplaceProduct(Request $request, Product $product)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $user->establishment;

        if (!$establishment || (int) $establishment->owner_id !== (int) $user->id) {
            abort(403);
        }

        if ((int) $product->establishment_id !== (int) $establishment->id) {
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
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path);
        }

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'price_per_unit' => $validated['price_per_unit'],
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'] ?? ($product->unit ?: 'kg'),
            'moq' => $validated['moq'] ?? ($product->moq ?: 1),
            'image_url' => $imageUrl,
        ]);

        return back()->with('status', 'Product updated successfully.');
    }

    public function map()
    {
        $mapboxToken = env('MAPBOX_API_KEY');
        $googleMapsKey = env('GOOGLE_MAPS_KEY');
        $verifiedResellers = $this->getVerifiedResellersForMap();

        $establishments = Establishment::with([
            'varieties',
            'reviews',
            'couponPromos' => function ($query) {
                $query->where('status', 'active')
                    ->where('valid_until', '>=', now()->toDateString());
            }
        ])->whereNull('deleted_at')->get();

        $establishments = $establishments->map(function ($e) {
            return [
                'id' => $e->id,
                'name' => $e->name,
                'type' => $e->type,
                'description' => $e->description,
                'address' => $e->address,
                'barangay' => $e->barangay,
                'contact_number' => $e->contact_number,
                'email' => $e->email,
                'website' => $e->website,
                'visit_hours' => $e->visit_hours,
                'activities' => $e->activities,
                'latitude' => $e->latitude,
                'longitude' => $e->longitude,
                'image' => $e->image,
                'coffee_varieties' => $e->varieties->pluck('name')->toArray(),
                'rating_average' => $e->reviews_avg_overall_rating ? round($e->reviews_avg_overall_rating, 1) : null,
                'review_count' => $e->reviews_count,
                'taste_avg' => $e->reviews_avg_taste_rating ? round($e->reviews_avg_taste_rating, 1) : null,
                'environment_avg' => $e->reviews_avg_environment_rating ? round($e->reviews_avg_environment_rating, 1) : null,
                'cleanliness_avg' => $e->reviews_avg_cleanliness_rating ? round($e->reviews_avg_cleanliness_rating, 1) : null,
                'service_avg' => $e->reviews_avg_service_rating ? round($e->reviews_avg_service_rating, 1) : null,
                'active_promos' => $e->couponPromos->map(function ($p) {
                    return [
                        'title' => $p->title,
                        'discount_type' => $p->discount_type,
                        'discount_value' => $p->discount_value,
                        'qr_code_token' => $p->qr_code_token,
                        'valid_from' => $p->valid_from,
                        'valid_until' => $p->valid_until,
                        'description' => $p->description,
                    ];
                }),
            ];
        });

        return view('farm-owner.map', compact('mapboxToken', 'googleMapsKey', 'establishments', 'verifiedResellers'));
    }

    public function messages(?Conversation $conversation = null)
    {
        $authUser = User::query()->findOrFail(Auth::id());

        if ($conversation) {
            abort_unless($conversation->users->contains(Auth::id()), 403);

            $conversation->participants()
                ->where('user_id', Auth::id())
                ->update(['last_read_at' => now()]);
        }

        $conversations = $authUser->conversations()
            ->with(['users', 'latestMessage.sender'])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1);
            })
            ->get();

        $users = User::where('id', '!=', Auth::id())->get();

        $messages = collect();

        if ($conversation) {
            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at')
                ->get();
        }

        return view('farm-owner.messages', compact('conversation', 'conversations', 'messages', 'users'));
    }

    public function messagesStore(Request $request)
    {
        $request->validate(['recipient_id' => 'required|exists:users,id']);

        $authUser = User::query()->findOrFail(Auth::id());

        $existingConversation = $authUser->conversations()
            ->whereHas('users', function ($query) use ($request) {
                $query->where('users.id', $request->recipient_id);
            })
            ->has('users', '=', 2)
            ->first();

        if ($existingConversation) {
            if ($request->wantsJson()) {
                return response()->json(['conversation_id' => $existingConversation->id]);
            }

            return redirect()->route('farm-owner.messages.show', $existingConversation);
        }

        $conversation = Conversation::create();
        $conversation->users()->attach([Auth::id(), $request->recipient_id]);

        if ($request->wantsJson()) {
            return response()->json(['conversation_id' => $conversation->id]);
        }

        return redirect()->route('farm-owner.messages.show', $conversation);
    }

    public function storeMessageConversation(Request $request)
    {
        return $this->messagesStore($request);
    }

    public function sendConversationMessage(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->users->contains(Auth::id()), 403);

        $request->validate(['body' => 'required|string|max:1000']);

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => Auth::user()->name,
            'created_at' => $message->created_at->format('M d, Y h:i A'),
        ]);
    }
}
