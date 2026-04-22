<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CafeOwnerDashboardController extends Controller
{
    public function index()
    {
        $popularityWindow = $this->resolvePopularityWindow(request()->query('popularity_window'));
        $windowSince = $this->resolvePopularityWindowSince($popularityWindow);
        $windowDays = $this->resolvePopularityWindowDays($popularityWindow);
        $previousWindowStart = $windowDays ? now()->subDays($windowDays * 2) : null;
        $previousWindowEnd = $windowDays ? now()->subDays($windowDays) : null;

        $userId = Auth::id();
        $establishment = $this->resolveCafeEstablishment($userId);
        $establishmentId = $establishment?->id;

        $totalVisits = $establishmentId ? $this->resolveTrailVisitsCount((int) $establishmentId, $windowSince) : 0;
        $cafeClicks = $establishmentId ? $this->resolveCafeClicksCount((int) $establishmentId, $windowSince) : 0;
        $popularityScore = ($totalVisits * 3) + $cafeClicks;

        $previousTrailVisits = ($windowDays && $establishmentId)
            ? $this->resolveTrailVisitsCount((int) $establishmentId, $previousWindowStart, $previousWindowEnd)
            : null;
        $previousCafeClicks = ($windowDays && $establishmentId)
            ? $this->resolveCafeClicksCount((int) $establishmentId, $previousWindowStart, $previousWindowEnd)
            : null;
        $previousPopularityScore = ($previousTrailVisits !== null && $previousCafeClicks !== null)
            ? (($previousTrailVisits * 3) + $previousCafeClicks)
            : null;

        $trailTrend = $this->buildTrendMeta($totalVisits, $previousTrailVisits);
        $clickTrend = $this->buildTrendMeta($cafeClicks, $previousCafeClicks);
        $popularityTrend = $this->buildTrendMeta($popularityScore, $previousPopularityScore);

        $productsListed = $this->resolveProductsListed((int) $userId, $establishmentId);
        $recosThisWeek = $establishmentId
            ? Recommendation::query()
                ->where('establishment_id', $establishmentId)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count()
            : 0;

        $recentActivity = $this->resolveRecentActivity((int) $userId, $establishmentId);

        return view('cafe-owner.dashboard', compact(
            'totalVisits',
            'cafeClicks',
            'popularityScore',
            'popularityWindow',
            'productsListed',
            'recosThisWeek',
            'recentActivity',
            'trailTrend',
            'clickTrend',
            'popularityTrend'
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

        $userId = (int) $user->id;
        $establishment = $this->resolveCafeEstablishment($userId);
        $establishmentId = $establishment?->id;

        $orderNotifications = collect();
        $pendingOrdersCount = 0;

        if (Schema::hasTable('orders') && Schema::hasTable('products')) {
            $orderQuery = Order::query()
                ->with(['user:id,name', 'product:id,name,establishment_id,seller_id,seller_type,user_id'])
                ->whereHas('product', function ($query) use ($userId, $establishmentId) {
                    if ($establishmentId && Schema::hasColumn('products', 'establishment_id')) {
                        $query->where('establishment_id', $establishmentId);
                        return;
                    }

                    if (Schema::hasColumn('products', 'seller_id')) {
                        $query->where('seller_id', $userId);

                        if (Schema::hasColumn('products', 'seller_type')) {
                            $query->where('seller_type', 'cafe_owner');
                        }

                        return;
                    }

                    if (Schema::hasColumn('products', 'user_id')) {
                        $query->where('user_id', $userId);
                    }
                });

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
                        'url' => route('cafe-owner.marketplace') . '#orders',
                    ];
                });
        }

        $chatNotifications = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()
            ->with(['latestMessage.sender:id,name'])
            ->get();

        foreach ($conversations as $conversation) {
            $unread = $conversation->unreadCount($userId);
            if ($unread <= 0) {
                continue;
            }

            $unreadChatCount += $unread;

            $participant = $conversation->participants()
                ->where('user_id', $userId)
                ->first();

            $latestUnreadIncoming = $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $userId)
                ->when(!empty($participant?->last_read_at), function ($query) use ($participant) {
                    $query->where('created_at', '>', $participant->last_read_at);
                }, function ($query) {
                    $query->whereNull('read_at');
                })
                ->latest()
                ->first();

            $latestIncoming = $latestUnreadIncoming ?: $conversation->messages()
                ->with('sender:id,name')
                ->where('sender_id', '!=', $userId)
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
                'url' => route('cafe-owner.messages.show', $conversation),
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

    protected function resolveCafeClicksCount(int $establishmentId, $since = null, $until = null): int
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

    protected function resolveCafeEstablishment(?int $userId): ?Establishment
    {
        if (!$userId) {
            return null;
        }

        $query = Establishment::query();

        if (Schema::hasColumn('establishments', 'owner_id')) {
            return $query->where('owner_id', $userId)->first();
        }

        if (Schema::hasColumn('establishments', 'user_id')) {
            return $query->where('user_id', $userId)->first();
        }

        return null;
    }

    protected function resolveTotalVisits(int $establishmentId): int
    {
        if (!Schema::hasTable('coffee_trails')) {
            return 0;
        }

        $trails = DB::table('coffee_trails');

        if (Schema::hasColumn('coffee_trails', 'establishment_id')) {
            return (int) $trails->where('establishment_id', $establishmentId)->count();
        }

        if (Schema::hasColumn('coffee_trails', 'establishment_ids')) {
            return (int) $trails->whereJsonContains('establishment_ids', $establishmentId)->count();
        }

        if (Schema::hasColumn('coffee_trails', 'trail_data')) {
            return (int) $trails
                ->where(function ($query) use ($establishmentId) {
                    $query->whereJsonContains('trail_data->establishment_ids', $establishmentId)
                        ->orWhereJsonContains('trail_data->establishments', $establishmentId);
                })
                ->count();
        }

        return 0;
    }

    protected function resolveProductsListed(int $userId, ?int $establishmentId): int
    {
        if (!Schema::hasTable('products')) {
            return 0;
        }

        $query = Product::query();

        if (Schema::hasColumn('products', 'user_id')) {
            return (int) $query->where('user_id', $userId)->count();
        }

        if (Schema::hasColumn('products', 'seller_id')) {
            $query->where('seller_id', $userId);

            if (Schema::hasColumn('products', 'seller_type')) {
                $query->where('seller_type', 'cafe_owner');
            }

            return (int) $query->count();
        }

        if ($establishmentId && Schema::hasColumn('products', 'establishment_id')) {
            return (int) $query->where('establishment_id', $establishmentId)->count();
        }

        return 0;
    }

    protected function resolveRecentActivity(int $userId, ?int $establishmentId): Collection
    {
        $latestProducts = collect();
        $latestOrders = collect();

        if (Schema::hasTable('products')) {
            $productQuery = Product::query();

            if (Schema::hasColumn('products', 'seller_id')) {
                $productQuery->where('seller_id', $userId);
                if (Schema::hasColumn('products', 'seller_type')) {
                    $productQuery->where('seller_type', 'cafe_owner');
                }
            } elseif (Schema::hasColumn('products', 'user_id')) {
                $productQuery->where('user_id', $userId);
            } elseif ($establishmentId && Schema::hasColumn('products', 'establishment_id')) {
                $productQuery->where('establishment_id', $establishmentId);
            }

            $latestProducts = $productQuery
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(function (Product $product) {
                    return [
                        'type' => 'product',
                        'title' => 'Updated product: ' . ($product->name ?: 'Untitled Product'),
                        'meta' => $product->category,
                        'occurred_at' => $product->updated_at ?? $product->created_at,
                    ];
                });
        }

        if (Schema::hasTable('orders') && Schema::hasTable('products')) {
            $latestOrders = Order::query()
                ->with('product:id,name,establishment_id,seller_id')
                ->whereHas('product', function ($query) use ($userId, $establishmentId) {
                    if ($establishmentId && Schema::hasColumn('products', 'establishment_id')) {
                        $query->where('establishment_id', $establishmentId);
                        return;
                    }

                    if (Schema::hasColumn('products', 'seller_id')) {
                        $query->where('seller_id', $userId);
                    }
                })
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(function (Order $order) {
                    return [
                        'type' => 'order',
                        'title' => 'Order #' . $order->id . ' (' . ucfirst((string) $order->status) . ')',
                        'meta' => $order->product?->name,
                        'occurred_at' => $order->updated_at ?? $order->created_at,
                    ];
                });
        }

        return $latestProducts
            ->merge($latestOrders)
            ->filter(fn ($activity) => !empty($activity['occurred_at']))
            ->sortByDesc('occurred_at')
            ->take(5)
            ->values();
    }

    protected function resolvePerformanceOverview(?int $establishmentId): array
    {
        $months = collect(range(5, 1))->map(function (int $offset) {
            return now()->startOfMonth()->subMonths($offset);
        })->push(now()->startOfMonth());

        $labels = $months->map(fn (Carbon $month) => $month->format('M Y'))->values()->all();
        $countsByMonth = [];

        if ($establishmentId && Schema::hasTable('establishments')) {
            $establishment = Establishment::query()->find($establishmentId);

            if ($establishment) {
                $metricColumn = null;

                if (Schema::hasColumn('establishments', 'clicks')) {
                    $metricColumn = 'clicks';
                } elseif (Schema::hasColumn('establishments', 'visits')) {
                    $metricColumn = 'visits';
                }

                if ($metricColumn) {
                    $currentMonthKey = now()->format('Y-m');

                    foreach ($months as $month) {
                        $monthKey = $month->format('Y-m');
                        $countsByMonth[$monthKey] = $monthKey === $currentMonthKey
                            ? (int) ($establishment->{$metricColumn} ?? 0)
                            : 0;
                    }
                }
            }
        }

        if (empty($countsByMonth) && $establishmentId && Schema::hasTable('coffee_trails')) {
            $trailQuery = DB::table('coffee_trails');

            if (Schema::hasColumn('coffee_trails', 'establishment_id')) {
                $trailQuery->where('establishment_id', $establishmentId);
            } elseif (Schema::hasColumn('coffee_trails', 'establishment_ids')) {
                $trailQuery->whereJsonContains('establishment_ids', $establishmentId);
            } elseif (Schema::hasColumn('coffee_trails', 'trail_data')) {
                $trailQuery->where(function ($query) use ($establishmentId) {
                    $query->whereJsonContains('trail_data->establishment_ids', $establishmentId)
                        ->orWhereJsonContains('trail_data->establishments', $establishmentId);
                });
            }

            if (Schema::hasColumn('coffee_trails', 'created_at')) {
                $from = now()->startOfMonth()->subMonths(5);
                $rows = $trailQuery
                    ->where('created_at', '>=', $from)
                    ->get(['created_at'])
                    ->groupBy(function ($row) {
                        return Carbon::parse($row->created_at)->format('Y-m');
                    })
                    ->map(fn (Collection $group) => $group->count())
                    ->toArray();

                foreach ($months as $month) {
                    $monthKey = $month->format('Y-m');
                    $countsByMonth[$monthKey] = (int) ($rows[$monthKey] ?? 0);
                }
            }
        }

        if (empty($countsByMonth)) {
            foreach ($months as $month) {
                $countsByMonth[$month->format('Y-m')] = 0;
            }
        }

        $series = $months->map(fn (Carbon $month) => (int) ($countsByMonth[$month->format('Y-m')] ?? 0))
            ->values()
            ->all();

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }
}
