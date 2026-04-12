<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Establishment;

class EstablishmentGeoJsonController extends Controller
{
    /**
     * Return all non-deleted establishments as GeoJSON FeatureCollection.
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
            ->with(['couponPromos' => function($query) {
                $query->where('status', 'active')
                      ->where('valid_until', '>=', now())
                      ->orderBy('created_at', 'desc');
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
                    'coupon_promos' => $est->couponPromos->map(function($promo) {
                        return [
                            'id' => $promo->id,
                            'title' => $promo->title,
                            'description' => $promo->description,
                            'discount_type' => $promo->discount_type,
                            'discount_value' => $promo->discount_value,
                            'qr_code_token' => $promo->qr_code_token,
                            'valid_from' => $promo->valid_from,
                            'valid_until' => $promo->valid_until,
                        ];
                    })->toArray(),
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
