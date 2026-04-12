<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GisService
{
    /**
     * Find establishments near a location
     * Uses PostGIS spatial query
     */
    public function getNearbyEstablishments(
        float  $latitude,
        float  $longitude,
        int    $radiusMeters = 5000,
        string $variety      = null,
        string $type         = null
    ): array {
        try {
            $params = [$longitude, $latitude,
                       $longitude, $latitude,
                       $radiusMeters];

            $varietyClause = '';
            $typeClause    = '';

            if ($variety && $variety !== 'All') {
                $varietyClause = "AND cv.variety = ?";
                $params[]      = $variety;
            }

            if ($type) {
                $typeClause = "AND e.type = ?";
                $params[]   = $type;
            }

            $results = DB::select("
                SELECT DISTINCT
                    e.id,
                    e.name,
                    e.type,
                    e.description,
                    e.address,
                    e.barangay,
                    e.latitude,
                    e.longitude,
                    e.contact_number,
                    e.email,
                    e.image,
                    ROUND(CAST(
                        ST_Distance(
                            e.geom,
                            ST_MakePoint(?, ?)::geography
                        ) AS NUMERIC
                    ), 2) AS distance_meters,
                    ARRAY_AGG(DISTINCT cv.variety)
                        AS varieties
                FROM establishments e
                LEFT JOIN coffee_varieties cv
                    ON e.id = cv.establishment_id
                WHERE
                    ST_DWithin(
                        e.geom,
                        ST_MakePoint(?, ?)::geography,
                        ?
                    )
                    {$varietyClause}
                    {$typeClause}
                    AND e.deleted_at IS NULL
                GROUP BY
                    e.id, e.name, e.type,
                    e.description, e.address,
                    e.barangay, e.latitude,
                    e.longitude, e.contact_number,
                    e.email, e.image,
                    distance_meters
                ORDER BY distance_meters
            ", $params);

            return [
                'success' => true,
                'data'    => $results,
            ];

        } catch (\Exception $e) {
            Log::error('GIS error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all approved establishments
     * as GeoJSON for map display
     */
    public function getEstablishmentsGeoJson(
        string $variety = null
    ): array {
        try {
            $params        = [];
            $varietyClause = '';

            if ($variety && $variety !== 'All') {
                $varietyClause = "AND cv.variety = ?";
                $params[]      = $variety;
            }

            $results = DB::select("
                SELECT
                    e.id,
                    e.name,
                    e.type,
                    e.address,
                    e.barangay,
                    e.latitude,
                    e.longitude,
                    e.image,
                    ST_AsGeoJSON(e.geom) AS geojson,
                    ARRAY_AGG(DISTINCT cv.variety)
                        AS varieties,
                    COALESCE(
                        AVG(
                            (r.taste_rating +
                             r.environment_rating +
                             r.cleanliness_rating +
                             r.service_rating) / 4.0
                        ), 0
                    ) AS avg_rating,
                    COUNT(DISTINCT r.id) AS review_count
                FROM establishments e
                LEFT JOIN coffee_varieties cv
                    ON e.id = cv.establishment_id
                LEFT JOIN rating r
                    ON e.id = r.establishment_id
                WHERE
                    e.deleted_at IS NULL
                    {$varietyClause}
                GROUP BY
                    e.id, e.name, e.type,
                    e.address, e.barangay,
                    e.latitude, e.longitude,
                    e.image, e.geom
                ORDER BY e.name
            ", $params);

            // Format as GeoJSON
            $features = array_map(function ($row) {
                return [
                    'type'     => 'Feature',
                    'geometry' => json_decode($row->geojson),
                    'properties' => [
                        'id'           => $row->id,
                        'name'         => $row->name,
                        'type'         => $row->type,
                        'address'      => $row->address,
                        'barangay'     => $row->barangay,
                        'latitude'     => $row->latitude,
                        'longitude'    => $row->longitude,
                        'image'        => $row->image,
                        'varieties'    => $row->varieties,
                        'avg_rating'   => round($row->avg_rating, 2),
                        'review_count' => $row->review_count,
                    ],
                ];
            }, $results);

            return [
                'success' => true,
                'data'    => [
                    'type'     => 'FeatureCollection',
                    'features' => $features,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('GIS GeoJSON error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update establishment geom from lat/lng
     */
    public function updateGeometry(
        int   $establishmentId,
        float $latitude,
        float $longitude
    ): bool {
        try {
            DB::statement("
                UPDATE establishments
                SET geom = ST_MakePoint(?, ?)::geography
                WHERE id = ?
            ", [$longitude, $latitude, $establishmentId]);

            return true;

        } catch (\Exception $e) {
            Log::error('GIS update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate distance between two points
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        try {
            $result = DB::selectOne("
                SELECT ST_Distance(
                    ST_MakePoint(?, ?)::geography,
                    ST_MakePoint(?, ?)::geography
                ) AS distance
            ", [$lng1, $lat1, $lng2, $lat2]);

            return round($result->distance, 2);

        } catch (\Exception $e) {
            Log::error('GIS distance error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get establishments within a bounding box
     * Used for map viewport filtering
     */
    public function getEstablishmentsInBounds(
        float $swLat,
        float $swLng,
        float $neLat,
        float $neLng
    ): array {
        try {
            $results = DB::select("
                SELECT
                    e.id,
                    e.name,
                    e.type,
                    e.latitude,
                    e.longitude,
                    e.image,
                    ARRAY_AGG(DISTINCT cv.variety)
                        AS varieties
                FROM establishments e
                LEFT JOIN coffee_varieties cv
                    ON e.id = cv.establishment_id
                WHERE
                    ST_Within(
                        e.geom::geometry,
                        ST_MakeEnvelope(
                            ?, ?, ?, ?, 4326
                        )
                    )
                    AND e.deleted_at IS NULL
                GROUP BY
                    e.id, e.name, e.type,
                    e.latitude, e.longitude,
                    e.image
            ", [$swLng, $swLat, $neLng, $neLat]);

            return [
                'success' => true,
                'data'    => $results,
            ];

        } catch (\Exception $e) {
            Log::error('GIS bounds error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}