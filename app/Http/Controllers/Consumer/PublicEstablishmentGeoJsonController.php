<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Establishment;
use App\Models\User;

class PublicEstablishmentGeoJsonController extends Controller
{
    /**
     * Return all non-deleted establishments as GeoJSON FeatureCollection (public).
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $variety = $request->query('variety');

        $query = Establishment::query()
            ->whereNull('deleted_at');

        if ($type) {
            $query->where('type', $type);
        }

        if ($variety) {
            $query->whereHas('varieties', function ($q) use ($variety) {
                $q->whereRaw('LOWER(name) = ?', [strtolower($variety)]);
            });
        }

        $establishments = $query->with(['varieties'])
            ->with(['reviews' => function ($q) {
                $q->with('user:id,name')
                    ->latest('created_at');
            }])
            ->withAvg('reviews', 'overall_rating')
            ->withAvg('reviews', 'taste_rating')
            ->withAvg('reviews', 'environment_rating')
            ->withAvg('reviews', 'cleanliness_rating')
            ->withAvg('reviews', 'service_rating')
            ->withCount('reviews')
            ->get();

        $features = $establishments->map(function ($est) {
            $geometry = null;
            if ($est->geom) {
                $geojson = DB::selectOne('SELECT ST_AsGeoJSON(?) as geojson', [$est->geom]);
                $geometry = $geojson ? json_decode($geojson->geojson, true) : null;
            }
            if (!$geometry && $est->latitude && $est->longitude) {
                $geometry = [
                    'type' => 'Point',
                    'coordinates' => [(float)$est->longitude, (float)$est->latitude],
                ];
            }
            return [
                'type' => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id' => $est->id,
                    'name' => $est->name,
                    'type' => $est->type,
                    'description' => $est->description,
                    'address' => $est->address,
                    'barangay' => $est->barangay,
                    'contact_number' => $est->contact_number,
                    'email' => $est->email,
                    'website' => $est->website,
                    'visit_hours' => $est->visit_hours,
                    'activities' => $est->activities,
                    'image' => $est->image,
                    'coffee_varieties' => $est->varieties->pluck('name')->toArray(),
                    'rating_average' => is_numeric($est->reviews_avg_overall_rating) ? round($est->reviews_avg_overall_rating, 1) : null,
                    'review_count' => $est->reviews_count,
                    'taste_avg' => is_numeric($est->reviews_avg_taste_rating) ? round($est->reviews_avg_taste_rating, 1) : null,
                    'environment_avg' => is_numeric($est->reviews_avg_environment_rating) ? round($est->reviews_avg_environment_rating, 1) : null,
                    'cleanliness_avg' => is_numeric($est->reviews_avg_cleanliness_rating) ? round($est->reviews_avg_cleanliness_rating, 1) : null,
                    'service_avg' => is_numeric($est->reviews_avg_service_rating) ? round($est->reviews_avg_service_rating, 1) : null,
                    'recent_reviews' => $est->reviews->take(3)->map(function ($review) {
                        return [
                            'id' => (int) $review->id,
                            'reviewer' => $review->user?->name ?? 'Anonymous',
                            'taste_rating' => (int) ($review->taste_rating ?? 0),
                            'environment_rating' => (int) ($review->environment_rating ?? 0),
                            'cleanliness_rating' => (int) ($review->cleanliness_rating ?? 0),
                            'service_rating' => (int) ($review->service_rating ?? 0),
                            'owner_response' => $review->owner_response,
                            'created_at' => $review->created_at,
                        ];
                    })->values()->all(),
                ],
            ];
        });

        $resellerFeatures = User::query()
            ->where('role', 'reseller')
            ->where('is_verified_reseller', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'deactivated');
            })
            ->whereNull('deactivated_at')
            ->get(['id', 'name', 'barangay', 'latitude', 'longitude', 'contact_number', 'email'])
            ->map(function ($reseller) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float) $reseller->longitude, (float) $reseller->latitude],
                    ],
                    'properties' => [
                        'id' => $reseller->id,
                        'name' => $reseller->name,
                        'type' => 'reseller',
                        'description' => 'Verified reseller partner in BrewHub.',
                        'address' => null,
                        'barangay' => $reseller->barangay,
                        'contact_number' => $reseller->contact_number,
                        'email' => $reseller->email,
                        'website' => null,
                        'visit_hours' => null,
                        'activities' => null,
                        'image' => null,
                        'coffee_varieties' => [],
                        'rating_average' => null,
                        'review_count' => 0,
                        'taste_avg' => null,
                        'environment_avg' => null,
                        'cleanliness_avg' => null,
                        'service_avg' => null,
                        'recent_reviews' => [],
                        'is_reseller_user' => true,
                        'reseller_user_id' => $reseller->id,
                    ],
                ];
            });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->concat($resellerFeatures)->values(),
        ]);
    }
}
