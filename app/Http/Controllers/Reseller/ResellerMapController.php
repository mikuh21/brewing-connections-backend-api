<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ResellerMapController extends Controller
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

    public function index()
    {
        $mapboxToken = env('MAPBOX_API_KEY');
        $googleMapsKey = env('GOOGLE_MAPS_KEY');
        $verifiedResellers = $this->getVerifiedResellersForMap();
        $resellerUser = User::query()
            ->with('coffeeVarieties')
            ->find(Auth::id());

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
                'primary_variety' => optional($e->varieties->firstWhere('pivot.is_primary', true))->name,
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

        if ($resellerUser && ($resellerUser->latitude !== null) && ($resellerUser->longitude !== null)) {
            $resellerVarieties = collect($resellerUser->coffeeVarieties ?? []);

            $establishments->push([
                'id' => 'reseller-user-' . $resellerUser->id,
                'name' => ($resellerUser->name ?? 'Reseller') . ' (Reseller)',
                'type' => 'reseller',
                'description' => 'Reseller profile location',
                'address' => $resellerUser->address,
                'barangay' => $resellerUser->barangay,
                'contact_number' => $resellerUser->contact_number,
                'email' => $resellerUser->email,
                'website' => null,
                'visit_hours' => null,
                'activities' => null,
                'latitude' => $resellerUser->latitude,
                'longitude' => $resellerUser->longitude,
                'image' => $resellerUser->image_url,
                'coffee_varieties' => $resellerVarieties->pluck('name')->values()->all(),
                'primary_variety' => optional($resellerVarieties->firstWhere('pivot.is_primary', true))->name,
                'rating_average' => null,
                'review_count' => 0,
                'taste_avg' => null,
                'environment_avg' => null,
                'cleanliness_avg' => null,
                'service_avg' => null,
                'active_promos' => [],
            ]);
        }

        return view('reseller.map', compact('mapboxToken', 'googleMapsKey', 'establishments', 'verifiedResellers'));
    }
}
