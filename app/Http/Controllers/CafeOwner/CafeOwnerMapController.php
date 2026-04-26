<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\User;

class CafeOwnerMapController extends Controller
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

        return view('cafe-owner.map', compact('mapboxToken', 'googleMapsKey', 'establishments', 'verifiedResellers'));
    }
}
