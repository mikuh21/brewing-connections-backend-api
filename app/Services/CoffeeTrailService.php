<?php

namespace App\Services;

use App\Models\CoffeeTrail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoffeeTrailService
{
    protected MapboxService $mapbox;

    public function __construct(MapboxService $mapbox)
    {
        $this->mapbox = $mapbox;
    }

    /**
     * Generate a personalized Coffee Trail
     * based on user location and variety preference
     */
    public function generate(
        float  $userLat,
        float  $userLng,
        string $variety = 'All',
        int    $limit   = 5
    ): array {
        try {
            $params = [
                $userLng, $userLat,
                $userLng, $userLat,
                10000
            ];

            $varietyClause = '';
            if ($variety !== 'All') {
                $varietyClause = "AND cv.variety = ?";
                $params[]      = $variety;
            }

            $params[] = $limit;

            // Step 1 — Find nearby establishments
            $establishments = DB::select("
                SELECT DISTINCT
                    e.id,
                    e.name,
                    e.type,
                    e.address,
                    e.barangay,
                    e.latitude,
                    e.longitude,
                    e.image,
                    ROUND(CAST(
                        ST_Distance(
                            e.geom,
                            ST_MakePoint(?, ?)::geography
                        ) AS NUMERIC
                    ), 2) AS distance_meters
                FROM establishments e
                LEFT JOIN coffee_varieties cv
                    ON e.id = cv.establishment_id
                WHERE
                    ST_DWithin(
                        e.geom,
                        ST_MakePoint(?, ?)::geography,
                        ?
                    )
                    AND e.deleted_at IS NULL
                    {$varietyClause}
                ORDER BY distance_meters
                LIMIT ?
            ", $params);

            if (empty($establishments)) {
                return [
                    'success' => false,
                    'message' => 'No establishments found nearby',
                ];
            }

            // Step 2 — Build coordinates for Mapbox
            $coordinates = [[
                'latitude'  => $userLat,
                'longitude' => $userLng,
            ]];

            foreach ($establishments as $est) {
                $coordinates[] = [
                    'latitude'  => (float) $est->latitude,
                    'longitude' => (float) $est->longitude,
                ];
            }

            // Step 3 — Get optimized route
            $route = $this->mapbox
                ->getOptimizedRoute($coordinates);

            if (!$route['success']) {
                return [
                    'success' => false,
                    'message' => 'Route optimization failed',
                ];
            }

            // Step 4 — Calculate totals
            $trip     = $route['data']['trips'][0] ?? [];
            $distance = round(
                ($trip['distance'] ?? 0) / 1000, 2
            );
            $duration = (int) (
                ($trip['duration'] ?? 0) / 60
            );

            return [
                'success'            => true,
                'establishments'     => $establishments,
                'route'              => $trip['geometry'] ?? [],
                'number_of_stops'    => count($establishments),
                'total_distance_km'  => $distance,
                'estimated_minutes'  => $duration,
                'variety_preference' => $variety,
            ];

        } catch (\Exception $e) {
            Log::error('CoffeeTrail error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}