<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\Establishment;
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

        $recommendationTimestampColumn = Schema::hasColumn('recommendations', 'generated_at')
            ? 'generated_at'
            : 'created_at';
        $recosThisWeek = $establishmentId
            ? Recommendation::query()
                ->where('establishment_id', $establishmentId)
                ->where($recommendationTimestampColumn, '>=', now()->startOfWeek(Carbon::MONDAY))
                ->count()
            : 0;

        $recentActivity = $this->resolveRecentActivity((int) $userId, $establishmentId);

        return view('cafe-owner.dashboard', compact(
            'totalVisits',
            'cafeClicks',
            'popularityScore',
            'popularityWindow',
            'recosThisWeek',
            'recentActivity',
            'trailTrend',
            'clickTrend',
            'popularityTrend',
            'establishment'
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

        $chatNotifications = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()
            ->messageableParticipants()
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

        $items = collect($chatNotifications)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        return response()->json([
            'counts' => [
                'pending_orders' => 0,
                'unread_chats' => $unreadChatCount,
                'total' => $unreadChatCount,
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

    protected function resolveRecentActivity(int $userId, ?int $establishmentId): Collection
    {
        if (!$establishmentId || !Schema::hasColumn('establishments', 'menu_image')) {
            return collect();
        }

        $establishment = Establishment::query()->find($establishmentId);

        if (!$establishment || blank($establishment->menu_image)) {
            return collect();
        }

        $uploadedAt = $establishment->menu_uploaded_at ?? $establishment->updated_at;

        if (!$uploadedAt) {
            return collect();
        }

        return collect([[
            'type' => 'menu',
            'title' => 'Cafe menu uploaded',
            'meta' => $establishment->name,
            'occurred_at' => $uploadedAt,
        ]]);
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
