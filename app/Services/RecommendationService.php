<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    protected GisService $gis;

    public function __construct(GisService $gis)
    {
        $this->gis = $gis;
    }

    /**
     * Get recommended establishments
     * based on ratings and proximity
     * Rule-based — no AI training needed
     */
    public function getRecommendations(
        float  $userLat,
        float  $userLng,
        string $variety  = 'All',
        string $userRole = 'consumer',
        int    $limit    = 10
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

            // Add limit at end
            $params[] = $limit;

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
                    ARRAY_AGG(DISTINCT cv.variety)
                        AS varieties,
                    COALESCE(AVG(
                        (r.taste_rating +
                         r.environment_rating +
                         r.cleanliness_rating +
                         r.service_rating) / 4.0
                    ), 0) AS avg_rating,
                    COUNT(DISTINCT r.id)
                        AS review_count,
                    ROUND(CAST(
                        ST_Distance(
                            e.geom,
                            ST_MakePoint(?, ?)::geography
                        ) AS NUMERIC
                    ) / 1000, 2) AS distance_km,
                    (
                        COALESCE(AVG(
                            (r.taste_rating +
                             r.environment_rating +
                             r.cleanliness_rating +
                             r.service_rating) / 4.0
                        ), 0) * 0.6
                        +
                        (1 - LEAST(
                            ST_Distance(
                                e.geom,
                                ST_MakePoint(?, ?)::geography
                            ) / ?, 1
                        )) * 0.4
                    ) AS score
                FROM establishments e
                LEFT JOIN coffee_varieties cv
                    ON e.id = cv.establishment_id
                LEFT JOIN rating r
                    ON e.id = r.establishment_id
                WHERE
                    ST_DWithin(
                        e.geom,
                        ST_MakePoint(?, ?)::geography,
                        ?
                    )
                    AND e.deleted_at IS NULL
                    {$varietyClause}
                GROUP BY
                    e.id, e.name, e.type,
                    e.address, e.barangay,
                    e.latitude, e.longitude,
                    e.image
                ORDER BY score DESC
                LIMIT ?
            ", $params);

            return [
                'success' => true,
                'data'    => $results,
            ];

        } catch (\Exception $e) {
            Log::error('Recommendation error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recommendations for business owners
     * Based on their review analytics
     * Shows what areas need improvement
     */
    public function getOwnerInsights(
        int $establishmentId
    ): array {
        try {
            $stats = DB::selectOne("
                SELECT
                    ROUND(AVG(taste_rating)::numeric, 2)
                        AS avg_taste,
                    ROUND(AVG(environment_rating)::numeric, 2)
                        AS avg_environment,
                    ROUND(AVG(cleanliness_rating)::numeric, 2)
                        AS avg_cleanliness,
                    ROUND(AVG(service_rating)::numeric, 2)
                        AS avg_service,
                    ROUND(AVG(
                        (taste_rating +
                         environment_rating +
                         cleanliness_rating +
                         service_rating) / 4.0
                    )::numeric, 2) AS avg_overall,
                    COUNT(*) AS total_reviews
                FROM rating
                WHERE establishment_id = ?
            ", [$establishmentId]);

            if (!$stats) {
                return [
                    'success' => true,
                    'data'    => [
                        'message' => 'No reviews yet',
                    ],
                ];
            }

            // Generate insights based on ratings
            $insights      = [];
            $improvements  = [];

            if ($stats->avg_taste < 3.5) {
                $improvements[] = [
                    'area'     => 'Taste',
                    'score'    => $stats->avg_taste,
                    'priority' => 'High',
                    'action'   =>
                        'Consider improving coffee '
                        . 'quality and preparation methods',
                ];
            }

            if ($stats->avg_environment < 3.5) {
                $improvements[] = [
                    'area'     => 'Environment',
                    'score'    => $stats->avg_environment,
                    'priority' => 'High',
                    'action'   =>
                        'Improve ambiance, seating, '
                        . 'and overall atmosphere',
                ];
            }

            if ($stats->avg_cleanliness < 3.5) {
                $improvements[] = [
                    'area'     => 'Cleanliness',
                    'score'    => $stats->avg_cleanliness,
                    'priority' => 'High',
                    'action'   =>
                        'Maintain higher cleanliness '
                        . 'standards throughout the establishment',
                ];
            }

            if ($stats->avg_service < 3.5) {
                $improvements[] = [
                    'area'     => 'Service',
                    'score'    => $stats->avg_service,
                    'priority' => 'High',
                    'action'   =>
                        'Train staff on customer '
                        . 'service and hospitality',
                ];
            }

            // Generate positive insights
            if ($stats->avg_taste >= 4.0) {
                $insights[] = 'Customers love your coffee taste!';
            }

            if ($stats->avg_service >= 4.0) {
                $insights[] = 'Your service is highly rated!';
            }

            if ($stats->avg_cleanliness >= 4.0) {
                $insights[] = 'Customers appreciate your cleanliness!';
            }

            if ($stats->avg_environment >= 4.0) {
                $insights[] = 'Your environment is well received!';
            }

            return [
                'success' => true,
                'data'    => [
                    'stats'        => $stats,
                    'insights'     => $insights,
                    'improvements' => $improvements,
                    'summary'      => $this->generateSummary(
                        $stats->avg_overall
                    ),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Owner insights error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get reseller product recommendations
     * based on top selling varieties
     */
    public function getResellerRecommendations(
        int $resellerId
    ): array {
        try {
            $topVarieties = DB::select("
                SELECT
                    p.variety,
                    COUNT(o.id) AS order_count,
                    SUM(o.total_price) AS total_revenue
                FROM orders o
                JOIN products p ON o.product_id = p.id
                GROUP BY p.variety
                ORDER BY order_count DESC
                LIMIT 3
            ");

            $suggestions = [];
            foreach ($topVarieties as $variety) {
                $suggestions[] = [
                    'variety'       => $variety->variety,
                    'order_count'   => $variety->order_count,
                    'total_revenue' => $variety->total_revenue,
                    'suggestion'    =>
                        "Consider stocking more "
                        . "{$variety->variety} — "
                        . "it has {$variety->order_count} "
                        . "orders this month",
                ];
            }

            return [
                'success' => true,
                'data'    => $suggestions,
            ];

        } catch (\Exception $e) {
            Log::error('Reseller recommendations: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate summary based on overall rating
     */
    private function generateSummary(
        float $avgRating
    ): string {
        if ($avgRating >= 4.5) {
            return 'Excellent — your establishment '
                 . 'is performing outstanding!';
        } elseif ($avgRating >= 4.0) {
            return 'Very Good — keep up the '
                 . 'great work!';
        } elseif ($avgRating >= 3.5) {
            return 'Good — a few improvements '
                 . 'could make you excellent!';
        } elseif ($avgRating >= 3.0) {
            return 'Average — focus on the '
                 . 'improvement areas below.';
        } else {
            return 'Needs Improvement — please '
                 . 'address the issues below urgently.';
        }
    }
}