<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ResellerProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ResellerDashboardController extends Controller
{
    public function index()
    {
        $resellerId = (int) Auth::id();
        $reseller = Auth::user();
        $isResellerVerified = (bool) ($reseller->is_verified_reseller ?? false);

        $totalOrders = 0;
        $productsListed = [
            'coffee_beans' => 0,
            'ground_coffee' => 0,
            'total' => 0,
        ];
        $recentOrders = collect();
        $recentActivity = collect();
        $performanceOverview = [
            'this_week' => 0,
            'last_week' => 0,
        ];
        $recentOrdersThisWeek = 0;

        if ($isResellerVerified) {
            $totalOrders = $this->buildResellerOrdersQuery($resellerId)->count();

            $productsByType = $this->resolveProductsListedByType($resellerId);
            $productsListed = [
                'coffee_beans' => (int) ($productsByType['Coffee Beans'] ?? 0),
                'ground_coffee' => (int) ($productsByType['Ground Coffee'] ?? 0),
                'total' => (int) array_sum($productsByType),
            ];

            $recentOrders = $this->resolveRecentOrders($resellerId);
            $recentActivity = $this->resolveRecentActivity($resellerId, $recentOrders);
            $performanceOverview = $this->resolvePerformanceOverview($resellerId);
            $recentOrdersThisWeek = (int) ($performanceOverview['this_week'] ?? 0);
        }

        return view('reseller.dashboard', compact(
            'reseller',
            'isResellerVerified',
            'totalOrders',
            'productsListed',
            'recentOrders',
            'recentActivity',
            'performanceOverview',
            'recentOrdersThisWeek'
        ));
    }

    public function notifications()
    {
        $user = Auth::user();
        /** @var \App\Models\User|null $user */

        if (!$user) {
            return response()->json([
                'counts' => [
                    'pending_orders' => 0,
                    'unread_chats' => 0,
                    'total' => 0,
                ],
                'items' => [],
                'updated_at' => now()->toIso8601String(),
            ]);
        }

        $resellerId = (int) $user->id;

        $orderNotifications = collect();
        $pendingOrdersCount = 0;

        if (Schema::hasTable('orders')) {
            $orderQuery = $this->buildResellerOrdersQuery($resellerId)
                ->with(['user:id,name', 'product:id,name']);

            if (Schema::hasColumn('orders', 'status')) {
                $pendingOrdersCount = (int) (clone $orderQuery)
                    ->where('status', 'pending')
                    ->count();
            }

            $orderNotifications = $orderQuery
                ->latest()
                ->limit(5)
                ->get()
                ->map(function (Order $order) {
                    $status = strtolower((string) ($order->status ?? 'pending'));

                    return [
                        'id' => 'order-' . $order->id . '-' . $status,
                        'type' => 'order',
                        'title' => 'New order from ' . (optional($order->user)->name ?? 'Customer'),
                        'subtitle' => (optional($order->product)->name ?? 'Product') . ' • Qty: ' . (int) ($order->quantity ?? 0),
                        'status' => $status,
                        'time' => optional($order->created_at)->diffForHumans(),
                        'timestamp' => optional($order->created_at)?->timestamp ?? 0,
                        'url' => route('reseller.marketplace') . '#orders',
                    ];
                });
        }

        $chatNotifications = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()
            ->with(['latestMessage.sender:id,name'])
            ->get();

        foreach ($conversations as $conversation) {
            $unread = $conversation->unreadCount($resellerId);
            if ($unread <= 0) {
                continue;
            }

            $unreadChatCount += $unread;

            $participant = $conversation->participants()
                ->where('user_id', $resellerId)
                ->first();

            $latestUnreadIncoming = $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $resellerId)
                ->when(!empty($participant?->last_read_at), function ($query) use ($participant) {
                    $query->where('created_at', '>', $participant->last_read_at);
                }, function ($query) {
                    $query->whereNull('read_at');
                })
                ->latest()
                ->first();

            $latestIncoming = $latestUnreadIncoming ?: $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $resellerId)
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
                'url' => route('reseller.messages.show', $conversation),
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

    protected function buildResellerOrdersQuery(int $resellerId): Builder
    {
        $query = Order::query();

        if (Schema::hasColumn('orders', 'reseller_id')) {
            return $query->where('reseller_id', $resellerId);
        }

        if (Schema::hasTable('products') && Schema::hasColumn('orders', 'product_id')) {
            $query->whereHas('product', function (Builder $productQuery) use ($resellerId) {
                if (Schema::hasColumn('products', 'seller_id')) {
                    $productQuery->where('seller_id', $resellerId);
                }

                if (Schema::hasColumn('products', 'seller_type')) {
                    $productQuery->where('seller_type', 'reseller');
                }
            });
        }

        return $query;
    }

    protected function resolveProductsListedByType(int $resellerId): array
    {
        if (!Schema::hasTable('reseller_products') || !Schema::hasTable('products')) {
            return [
                'Coffee Beans' => 0,
                'Ground Coffee' => 0,
            ];
        }

        $grouped = ResellerProduct::query()
            ->join('products', 'products.id', '=', 'reseller_products.product_id')
            ->where('reseller_products.reseller_id', $resellerId)
            ->whereIn('products.category', ['Coffee Beans', 'Ground Coffee'])
            ->selectRaw('products.category as category, COUNT(reseller_products.id) as total')
            ->groupBy('products.category')
            ->pluck('total', 'category')
            ->map(fn ($count) => (int) $count)
            ->all();

        return [
            'Coffee Beans' => (int) ($grouped['Coffee Beans'] ?? 0),
            'Ground Coffee' => (int) ($grouped['Ground Coffee'] ?? 0),
        ];
    }

    protected function resolveRecentOrders(int $resellerId): Collection
    {
        return $this->buildResellerOrdersQuery($resellerId)
            ->with(['user:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => (int) $order->id,
                    'consumer_name' => optional($order->user)->name ?? 'Unknown Consumer',
                    'date' => optional($order->created_at)?->toDateString(),
                    'status' => (string) ($order->status ?? 'pending'),
                    'created_at' => $order->created_at,
                ];
            });
    }

    protected function resolveRecentActivity(int $resellerId, Collection $recentOrders): Collection
    {
        $productActivity = collect();

        if (Schema::hasTable('reseller_products')) {
            $productActivity = ResellerProduct::query()
                ->with('product:id,name,category')
                ->where('reseller_id', $resellerId)
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(function (ResellerProduct $resellerProduct) {
                    $occurredAt = $resellerProduct->updated_at ?? $resellerProduct->created_at;

                    return [
                        'type' => 'product_update',
                        'title' => 'Updated listing: ' . ($resellerProduct->product?->name ?? 'Unnamed Product'),
                        'meta' => $resellerProduct->product?->category,
                        'occurred_at' => $occurredAt,
                    ];
                });
        }

        $orderActivity = $recentOrders->map(function (array $order) {
            return [
                'type' => 'new_order',
                'title' => 'New order #' . $order['id'] . ' from ' . $order['consumer_name'],
                'meta' => ucfirst((string) ($order['status'] ?? 'pending')),
                'occurred_at' => $order['created_at'] ?? null,
            ];
        });

        return $productActivity
            ->merge($orderActivity)
            ->filter(fn (array $activity) => !empty($activity['occurred_at']))
            ->sortByDesc('occurred_at')
            ->take(5)
            ->values();
    }

    protected function resolvePerformanceOverview(int $resellerId): array
    {
        $ordersQuery = $this->buildResellerOrdersQuery($resellerId);

        $thisWeekCount = (clone $ordersQuery)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $lastWeekCount = (clone $ordersQuery)
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        return [
            'this_week' => (int) $thisWeekCount,
            'last_week' => (int) $lastWeekCount,
        ];
    }
}
