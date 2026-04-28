<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\CouponPromo;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $popularityWindow = $this->resolvePopularityWindow($request->query('popularity_window'));

        $totalEstablishments = Establishment::count();

        $pendingRegistrations = User::query()
            ->where('role', 'consumer')
            ->count();

        $pendingReviews = CouponPromo::query()
            ->where('status', 'active')
            ->where('valid_until', '>=', now()->toDateString())
            ->count();

        $activeListings = Product::query()
            ->where('is_active', true)
            ->count();

        $frequentlyVisitedEstablishments = $this->getFrequentlyVisitedEstablishments($popularityWindow);

        return view('admin.dashboard', [
            'totalEstablishments' => $totalEstablishments,
            'pendingRegistrations' => $pendingRegistrations,
            'pendingReviews' => $pendingReviews,
            'activeListings' => $activeListings,
            'frequentlyVisitedEstablishments' => $frequentlyVisitedEstablishments,
            'popularityWindow' => $popularityWindow,
        ]);
    }

    protected function getFrequentlyVisitedEstablishments(string $popularityWindow = '30d')
    {
        $since = match ($popularityWindow) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };

        $trails = CoffeeTrail::query()
            ->when($since, function ($query) use ($since) {
                $query->where('created_at', '>=', $since);
            })
            ->get();

        $destinationVisits = collect();
        $destinationPoints = collect();

        foreach ($trails as $trail) {
            $trailData = is_array($trail->trail_data) ? $trail->trail_data : [];

            foreach ($trailData as $point) {
                if (!is_array($point) || !isset($point['id'])) {
                    continue;
                }

                $establishmentId = (int) $point['id'];
                $destinationVisits->put(
                    $establishmentId,
                    ($destinationVisits->get($establishmentId, 0) + 1)
                );
                $destinationPoints->put(
                    $establishmentId,
                    ($destinationPoints->get($establishmentId, 0) + 3)
                );
            }
        }

        $markerViewRows = CoffeeTrailMarkerView::query()
            ->when($since, function ($query) use ($since) {
                $query->where('viewed_at', '>=', $since);
            })
            ->selectRaw('establishment_id, COUNT(*) AS total_views')
            ->groupBy('establishment_id')
            ->get();

        $markerViews = $markerViewRows
            ->pluck('total_views', 'establishment_id')
            ->map(fn ($count) => (int) $count);

        $markerPoints = $markerViews->map(fn ($count) => (int) $count);

        $establishmentIds = $destinationPoints
            ->keys()
            ->merge($markerPoints->keys())
            ->unique()
            ->values();

        if ($establishmentIds->isEmpty()) {
            return [];
        }

        $allEstablishments = Establishment::query()
            ->whereIn('id', $establishmentIds->all())
            ->select(['id', 'name', 'barangay', 'address', 'latitude', 'longitude', 'image'])
            ->get()
            ->keyBy('id');

        $scoreByEstablishment = $establishmentIds
            ->mapWithKeys(function ($id) use ($destinationPoints, $markerPoints) {
                $establishmentId = (int) $id;
                $score = (int) $destinationPoints->get($establishmentId, 0)
                    + (int) $markerPoints->get($establishmentId, 0);

                return [$establishmentId => $score];
            })
            ->sortDesc()
            ->take(5);

        return $scoreByEstablishment
            ->map(function ($popularityScore, $establishmentId) use ($allEstablishments, $destinationVisits, $markerViews) {
                $establishment = $allEstablishments->get((int) $establishmentId);
                $trailDestinations = (int) $destinationVisits->get((int) $establishmentId, 0);
                $markerViewCount = (int) $markerViews->get((int) $establishmentId, 0);

                return [
                    'id' => (int) $establishmentId,
                    'name' => $establishment?->name ?? 'Unknown Establishment',
                    'city' => $establishment?->barangay
                        ?? $establishment?->address
                        ?? 'Unknown Location',
                    'visits' => (int) $popularityScore,
                    'popularity_score' => (int) $popularityScore,
                    'trail_destinations' => $trailDestinations,
                    'marker_views' => $markerViewCount,
                    'image_url' => $establishment?->image,
                ];
            })
            ->values()
            ->all();
    }

    protected function resolvePopularityWindow($rawWindow): string
    {
        $window = strtolower(trim((string) ($rawWindow ?? '30d')));

        return in_array($window, ['7d', '30d', 'all'], true)
            ? $window
            : '30d';
    }

    public function notifications(): JsonResponse
    {
        $user = User::query()->findOrFail(Auth::id());

        $registrationItems = User::query()
            ->where('role', 'consumer')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (User $consumer) {
                return [
                    'id' => 'registration-' . $consumer->id,
                    'type' => 'registration',
                    'title' => 'New consumer registration: ' . $consumer->name,
                    'subtitle' => $consumer->email,
                    'time' => optional($consumer->created_at)->diffForHumans(),
                    'timestamp' => optional($consumer->created_at)?->timestamp ?? 0,
                    'url' => route('admin.registrations.index'),
                ];
            });

        $resellerRegistrationItems = User::query()
            ->where('role', 'reseller')
            ->where('created_at', '>=', now()->subDays(7))
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (User $reseller) {
                return [
                    'id' => 'reseller-registration-' . $reseller->id,
                    'type' => 'reseller_registration',
                    'title' => 'New reseller registration: ' . $reseller->name,
                    'subtitle' => $reseller->email,
                    'time' => optional($reseller->created_at)->diffForHumans(),
                    'timestamp' => optional($reseller->created_at)?->timestamp ?? 0,
                    'url' => route('admin.resellers.index'),
                ];
            });

        $chatItems = collect();
        $unreadChatCount = 0;

        $conversations = $user->conversations()->get();

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

            if (!$latestUnreadIncoming) {
                continue;
            }

            $chatItems->push([
                'id' => 'chat-' . $conversation->id . '-' . $latestUnreadIncoming->id,
                'type' => 'chat',
                'title' => 'New message from ' . ($latestUnreadIncoming->sender->name ?? 'User'),
                'subtitle' => Str::limit((string) $latestUnreadIncoming->body, 60),
                'time' => optional($latestUnreadIncoming->created_at)->diffForHumans(),
                'timestamp' => optional($latestUnreadIncoming->created_at)?->timestamp ?? 0,
                    'url' => route('chat.show', $conversation->id),
            ]);
        }

        $items = collect($registrationItems->toArray())
            ->merge(collect($resellerRegistrationItems->toArray()))
            ->merge($chatItems)
            ->sortByDesc('timestamp')
            ->take(12)
            ->values();

        return response()->json([
            'counts' => [
                'registrations' => $registrationItems->count(),
                'reseller_registrations' => $resellerRegistrationItems->count(),
                'resellers_pending' => User::query()
                    ->where('role', 'reseller')
                    ->where(function ($query) {
                        $query->where('is_verified_reseller', false)
                            ->orWhereNull('is_verified_reseller');
                    })
                    ->count(),
                'unread_chats' => $unreadChatCount,
                'total' => $registrationItems->count() + $resellerRegistrationItems->count() + $unreadChatCount,
            ],
            'items' => $items,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
