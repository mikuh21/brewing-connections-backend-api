<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\MapDetailsService;

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
            ->with(['resellerProducts.product'])
            ->get(['id', 'name', 'barangay', 'latitude', 'longitude', 'updated_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'barangay' => $user->barangay,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'verified_at' => optional($user->updated_at)?->toIso8601String(),
                    'associated_products' => MapDetailsService::resellerProducts($user->resellerProducts),
                ];
            })
            ->values();
    }

    public function index()
    {
        $mapboxToken = config('services.mapbox.api_key');
        $googleMapsKey = config('services.google_maps.key');
        $verifiedResellers = $this->getVerifiedResellersForMap();
        $resellerUser = User::query()
            ->with(['coffeeVarieties', 'resellerProducts.product'])
            ->find(Auth::id());

        $establishments = Establishment::query()
            ->whereNull('deleted_at')
            ->with([
                'varieties:id,name',
                'products:id,establishment_id,name,description,category,price_per_unit,unit,image_url,is_active',
                'couponPromos' => function ($query) {
                    $query->where('status', 'active')
                        ->where('valid_until', '>=', now()->toDateString());
                },
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'overall_rating')
            ->withAvg('reviews', 'taste_rating')
            ->withAvg('reviews', 'environment_rating')
            ->withAvg('reviews', 'cleanliness_rating')
            ->withAvg('reviews', 'service_rating')
            ->get();

        $establishments = $establishments->map(function ($e) {
            $reviewCount = (int) ($e->reviews_count ?? 0);
            $overallAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_overall_rating ?? 0), 1) : null;
            $tasteAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_taste_rating ?? 0), 1) : null;
            $environmentAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_environment_rating ?? 0), 1) : null;
            $cleanlinessAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_cleanliness_rating ?? 0), 1) : null;
            $serviceAverage = $reviewCount > 0 ? round((float) ($e->reviews_avg_service_rating ?? 0), 1) : null;

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
                'rating_average' => $overallAverage,
                'review_count' => $reviewCount,
                'taste_avg' => $tasteAverage,
                'environment_avg' => $environmentAverage,
                'cleanliness_avg' => $cleanlinessAverage,
                'service_avg' => $serviceAverage,
                'associated_products' => MapDetailsService::products($e->products),
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
                'associated_products' => MapDetailsService::resellerProducts($resellerUser->resellerProducts),
                'active_promos' => [],
            ]);
        }

        return view('reseller.map', compact('mapboxToken', 'googleMapsKey', 'establishments', 'verifiedResellers'));
    }
}
