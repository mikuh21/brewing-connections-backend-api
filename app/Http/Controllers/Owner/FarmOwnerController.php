<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\CoffeeVariety;
use App\Models\Conversation;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use App\Services\OrderReceiptNotifier;
use App\Services\OrderStockManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FarmOwnerController extends Controller
{
    public function __construct(
        private readonly OrderReceiptNotifier $orderReceiptNotifier,
        private readonly OrderStockManager $orderStockManager,
    ) {
    }

    protected function farmOwnerEstablishmentsQuery(User $user)
    {
        $query = Establishment::query();

        $hasOwnerId = Schema::hasColumn('establishments', 'owner_id');
        $hasUserId = Schema::hasColumn('establishments', 'user_id');

        if ($hasOwnerId && $hasUserId) {
            $query->where(function ($ownerQuery) use ($user) {
                $ownerQuery->where('owner_id', $user->id)
                    ->orWhere('user_id', $user->id);
            });
        } elseif ($hasOwnerId) {
            $query->where('owner_id', $user->id);
        } elseif ($hasUserId) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (Schema::hasColumn('establishments', 'type')) {
            $query->where('type', 'farm');
        }

        return $query;
    }

    protected function resolveActiveFarm(User $user, ?int $requestedFarmId = null, bool $createIfMissing = false): ?Establishment
    {
        $baseQuery = $this->farmOwnerEstablishmentsQuery($user)->with('varieties');

        if (!empty($requestedFarmId)) {
            $selectedFarm = (clone $baseQuery)
                ->whereKey((int) $requestedFarmId)
                ->first();

            if ($selectedFarm) {
                return $selectedFarm;
            }
        }

        $fallbackFarm = (clone $baseQuery)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($fallbackFarm || !$createIfMissing) {
            return $fallbackFarm;
        }

        $createPayload = [
            'name' => $user->name . "'s Farm",
            'type' => 'farm',
        ];

        if (Schema::hasColumn('establishments', 'owner_id')) {
            $createPayload['owner_id'] = $user->id;
        }

        if (Schema::hasColumn('establishments', 'user_id')) {
            $createPayload['user_id'] = $user->id;
        }

        return Establishment::create($createPayload)->load('varieties');
    }

    protected function activeFarmIdFromRequest(Request $request): ?int
    {
        $farmId = (int) $request->input('farm_id', 0);

        return $farmId > 0 ? $farmId : null;
    }

    protected function isFarmManagedByUser(Establishment $establishment, User $user): bool
    {
        $matchesOwner = Schema::hasColumn('establishments', 'owner_id')
            && (int) $establishment->owner_id === (int) $user->id;

        $matchesUser = Schema::hasColumn('establishments', 'user_id')
            && (int) $establishment->user_id === (int) $user->id;

        return $matchesOwner || $matchesUser;
    }

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

    public function dashboard(Request $request)
    {
        $popularityWindow = $this->resolvePopularityWindow($request->query('popularity_window'));
        $windowSince = $this->resolvePopularityWindowSince($popularityWindow);
        $windowDays = $this->resolvePopularityWindowDays($popularityWindow);
        $previousWindowStart = $windowDays ? now()->subDays($windowDays * 2) : null;
        $previousWindowEnd = $windowDays ? now()->subDays($windowDays) : null;

        $user = Auth::user();
        /** @var \App\Models\User|null $user */
        $establishment = $user
            ? $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest(request()))
            : null;

        if (!$establishment) {
            return view('farm-owner.dashboard', [
                'productsListed' => 0,
                'ordersThisWeek' => 0,
                'totalVisits' => 0,
                'farmClicks' => 0,
                'popularityScore' => 0,
                'popularityWindow' => $popularityWindow,
                'trailTrend' => $this->buildTrendMeta(0, null),
                'clickTrend' => $this->buildTrendMeta(0, null),
                'popularityTrend' => $this->buildTrendMeta(0, null),
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

        $totalVisits = $this->resolveTrailVisitsCount($establishmentId, $windowSince);
        $farmClicks = $this->resolveFarmClicksCount($establishmentId, $windowSince);
        $popularityScore = ($totalVisits * 3) + $farmClicks;

        $previousTrailVisits = $windowDays
            ? $this->resolveTrailVisitsCount($establishmentId, $previousWindowStart, $previousWindowEnd)
            : null;
        $previousFarmClicks = $windowDays
            ? $this->resolveFarmClicksCount($establishmentId, $previousWindowStart, $previousWindowEnd)
            : null;
        $previousPopularityScore = ($previousTrailVisits !== null && $previousFarmClicks !== null)
            ? (($previousTrailVisits * 3) + $previousFarmClicks)
            : null;

        $trailTrend = $this->buildTrendMeta($totalVisits, $previousTrailVisits);
        $clickTrend = $this->buildTrendMeta($farmClicks, $previousFarmClicks);
        $popularityTrend = $this->buildTrendMeta($popularityScore, $previousPopularityScore);

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
            'popularityScore',
            'popularityWindow',
            'trailTrend',
            'clickTrend',
            'popularityTrend',
            'recentActivity'
        ));
    }

    protected function resolveTrailVisitsCount(int $establishmentId, $since = null, $until = null): int
    {
        if (!Schema::hasTable('coffee_trails')) {
            return 0;
        }

        $trails = CoffeeTrail::query()
            ->when($since, function ($query) use ($since) {
                $query->where('created_at', '>=', $since);
            })
            ->when($until, function ($query) use ($until) {
                $query->where('created_at', '<', $until);
            })
            ->get(['trail_data']);

        $count = 0;

        foreach ($trails as $trail) {
            $trailData = is_array($trail->trail_data) ? $trail->trail_data : [];

            foreach ($trailData as $point) {
                if (!is_array($point) || !isset($point['id'])) {
                    continue;
                }

                if ((int) $point['id'] === $establishmentId) {
                    $count++;
                }
            }
        }

        return $count;
    }

    protected function resolveFarmClicksCount(int $establishmentId, $since = null, $until = null): int
    {
        if (!Schema::hasTable('coffee_trail_marker_views')) {
            return 0;
        }

        return (int) CoffeeTrailMarkerView::query()
            ->where('establishment_id', $establishmentId)
            ->when($since, function ($query) use ($since) {
                $query->where('viewed_at', '>=', $since);
            })
            ->when($until, function ($query) use ($until) {
                $query->where('viewed_at', '<', $until);
            })
            ->count();
    }

    protected function resolvePopularityWindow($rawWindow): string
    {
        $window = strtolower(trim((string) ($rawWindow ?? '30d')));

        return in_array($window, ['7d', '30d', 'all'], true)
            ? $window
            : '30d';
    }

    protected function resolvePopularityWindowSince(string $window)
    {
        return match ($window) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };
    }

    protected function resolvePopularityWindowDays(string $window): ?int
    {
        return match ($window) {
            '7d' => 7,
            '30d' => 30,
            default => null,
        };
    }

    protected function buildTrendMeta(int $currentValue, ?int $previousValue): array
    {
        if ($previousValue === null) {
            return [
                'direction' => 'neutral',
                'percent' => null,
                'label' => 'No previous-period comparison',
            ];
        }

        $delta = $currentValue - $previousValue;

        if ($previousValue <= 0) {
            if ($currentValue > 0) {
                return [
                    'direction' => 'up',
                    'percent' => 100.0,
                    'label' => 'Up from zero last period',
                ];
            }

            return [
                'direction' => 'flat',
                'percent' => 0.0,
                'label' => 'No change vs previous period',
            ];
        }

        $percent = round((abs($delta) / $previousValue) * 100, 1);

        if ($delta > 0) {
            return [
                'direction' => 'up',
                'percent' => $percent,
                'label' => $percent . '% up vs previous period',
            ];
        }

        if ($delta < 0) {
            return [
                'direction' => 'down',
                'percent' => $percent,
                'label' => $percent . '% down vs previous period',
            ];
        }

        return [
            'direction' => 'flat',
            'percent' => 0.0,
            'label' => 'No change vs previous period',
        ];
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

        $activeFarmId = $this->activeFarmIdFromRequest(request());
        $establishment = $this->resolveActiveFarm($user, $activeFarmId, true);

        if (!$this->isFarmManagedByUser($establishment, $user)) {
            abort(403);
        }

        $managedFarms = $this->farmOwnerEstablishmentsQuery($user)
            ->orderBy('name')
            ->get(['id', 'name']);

        $allVarieties = CoffeeVariety::query()->orderBy('name')->get();
        $selectedVarietyIds = $establishment->varieties->pluck('id')->all();
        $primaryVarietyId = optional($establishment->varieties->firstWhere('pivot.is_primary', true))->id;

        return view('farm-owner.my-farm', compact(
            'establishment',
            'allVarieties',
            'selectedVarietyIds',
            'primaryVarietyId',
            'managedFarms'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $requestedFarmId = $this->activeFarmIdFromRequest($request);
        $establishment = $this->resolveActiveFarm($user, $requestedFarmId);

        if (!$establishment) {
            abort(404, 'Farm profile not found.');
        }

        if (!$this->isFarmManagedByUser($establishment, $user)) {
            abort(403);
        }

        $validated = $request->validate([
            'farm_id' => 'nullable|integer|exists:establishments,id',
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
            'contact_number' => $validated['contact_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
        ];

        // Keep existing coordinates unless the request explicitly sends non-null values.
        if (array_key_exists('latitude', $validated) && $validated['latitude'] !== null) {
            $updatePayload['latitude'] = $validated['latitude'];
        }

        if (array_key_exists('longitude', $validated) && $validated['longitude'] !== null) {
            $updatePayload['longitude'] = $validated['longitude'];
        }

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
            $path = $request->file('image')->store('establishments', 'supabase');
            /** @var \Illuminate\Filesystem\FilesystemAdapter $supabaseDisk */
            $supabaseDisk = Storage::disk('supabase');
            $updatePayload['image'] = $supabaseDisk->url($path);
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
            ->route('farm-owner.my-farm', ['farm_id' => $establishment->id])
            ->with('status', 'Farm profile updated successfully.');
    }

    public function marketplace()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest(request()));

        $productsQuery = Product::query()->whereRaw('1 = 0');

        if ($establishment && $this->isFarmManagedByUser($establishment, $user)) {
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
        $productRatings = collect();
        if ($establishment && (int) $establishment->owner_id === (int) $user->id) {
            $orders = Order::with(['user:id,name', 'product:id,name,establishment_id'])
                ->whereHas('product', function ($query) use ($establishment) {
                    $query->where('establishment_id', $establishment->id);
                })
                ->latest()
                ->get();

            $productRatings = Rating::with([
                'user:id,name',
                'product:id,name,image_url,establishment_id',
                'order:id,status,quantity,total_price,created_at',
            ])
                ->whereNotNull('product_id')
                ->whereHas('product', function ($query) use ($establishment) {
                    $query->where('establishment_id', $establishment->id);
                })
                ->latest()
                ->get();
        }

        return view('farm-owner.marketplace', compact('products', 'marketplaceProducts', 'orders', 'productRatings'));
    }

    public function updateMarketplaceOrder(Request $request, Order $order)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest($request));

        if (!$establishment || !$this->isFarmManagedByUser($establishment, $user)) {
            abort(403);
        }

        $order->loadMissing('product:id,establishment_id');

        if ((int) optional($order->product)->establishment_id !== (int) $establishment->id) {
            abort(403);
        }

        if (in_array(strtolower((string) $order->status), ['canceled', 'cancelled'], true)) {
            return back()->withErrors([
                'status' => 'Cancelled orders can no longer be updated.',
            ]);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,canceled,cancelled',
        ]);

        $status = $validated['status'] === 'canceled' ? 'cancelled' : $validated['status'];

        $order = $this->orderStockManager->applyStatusTransition($order, $status);

        $this->orderReceiptNotifier->sendStatusUpdated($order);

        return back()->with('status', 'Order status updated successfully.');
    }

    public function notifications()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest(request()));

        $orderNotifications = collect();
        $pendingOrdersCount = 0;

        if ($establishment && $this->isFarmManagedByUser($establishment, $user)) {
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
                ->map(function (Order $order) use ($establishment) {
                    return [
                        'id' => 'order-' . $order->id . '-' . strtolower((string) $order->status),
                        'type' => 'order',
                        'title' => 'New order from ' . (optional($order->user)->name ?? 'Customer'),
                        'subtitle' => (optional($order->product)->name ?? 'Product') . ' • Qty: ' . (int) $order->quantity,
                        'status' => strtolower((string) $order->status),
                        'time' => optional($order->created_at)->diffForHumans(),
                        'timestamp' => optional($order->created_at)?->timestamp ?? 0,
                        'url' => route('farm-owner.marketplace', ['farm_id' => $establishment->id]) . '#orders',
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
                'url' => route('farm-owner.messages.show', ['conversation' => $conversation->id, 'farm_id' => $establishment?->id]),
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

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest($request));

        if (!$establishment || !$this->isFarmManagedByUser($establishment, $user)) {
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
            $path = $request->file('image')->store('products', 'supabase');
            /** @var \Illuminate\Filesystem\FilesystemAdapter $supabaseDisk */
            $supabaseDisk = Storage::disk('supabase');
            $imageUrl = $supabaseDisk->url($path);
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

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest($request));

        if (!$establishment || !$this->isFarmManagedByUser($establishment, $user)) {
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
            $path = $request->file('image')->store('products', 'supabase');
            /** @var \Illuminate\Filesystem\FilesystemAdapter $supabaseDisk */
            $supabaseDisk = Storage::disk('supabase');
            $imageUrl = $supabaseDisk->url($path);
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

    public function updateMarketplaceProductVisibility(Request $request, Product $product)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $establishment = $this->resolveActiveFarm($user, $this->activeFarmIdFromRequest($request));

        if (!$establishment || !$this->isFarmManagedByUser($establishment, $user)) {
            abort(403);
        }

        if ((int) $product->establishment_id !== (int) $establishment->id) {
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

    public function map()
    {
        $mapboxToken = config('services.mapbox.api_key');
        $googleMapsKey = config('services.google_maps.key');
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
            $reviews = $e->reviews ?? collect();
            $reviewCount = (int) $reviews->count();
            $overallAverage = $reviewCount > 0 ? round((float) $reviews->avg('overall_rating'), 1) : null;
            $tasteAverage = $reviewCount > 0 ? round((float) $reviews->avg('taste_rating'), 1) : null;
            $environmentAverage = $reviewCount > 0 ? round((float) $reviews->avg('environment_rating'), 1) : null;
            $cleanlinessAverage = $reviewCount > 0 ? round((float) $reviews->avg('cleanliness_rating'), 1) : null;
            $serviceAverage = $reviewCount > 0 ? round((float) $reviews->avg('service_rating'), 1) : null;

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
                'rating_average' => $overallAverage,
                'review_count' => $reviewCount,
                'taste_avg' => $tasteAverage,
                'environment_avg' => $environmentAverage,
                'cleanliness_avg' => $cleanlinessAverage,
                'service_avg' => $serviceAverage,
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
            'created_at' => $message->created_at->toIso8601String(),
        ]);
    }
}
