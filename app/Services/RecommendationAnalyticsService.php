<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

class RecommendationAnalyticsService
{
    public function getOverallAnalytics()
    {
        $stats = DB::table('rating')
            ->selectRaw('
                AVG(taste_rating) as taste_avg,
                AVG(environment_rating) as environment_avg,
                AVG(cleanliness_rating) as cleanliness_avg,
                AVG(service_rating) as service_avg,
                AVG(overall_rating) as overall_avg,
                COUNT(*) as total_reviews
            ')
            ->first();

        if ($stats->total_reviews == 0) {
            return [
                'averages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
                'overall_average' => 0,
                'total_reviews' => 0,
                'needs_attention' => null,
                'impact_percentages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
            ];
        }

        $categories = [
            'taste' => (float) ($stats->taste_avg ?? 0),
            'environment' => (float) ($stats->environment_avg ?? 0),
            'cleanliness' => (float) ($stats->cleanliness_avg ?? 0),
            'service' => (float) ($stats->service_avg ?? 0),
        ];

        $lowestCategory = array_keys($categories, min($categories))[0];

        $impactPercentages = $this->calculateImpactPercentages($categories, $stats->overall_avg);

        return [
            'averages' => $categories,
            'overall_average' => (float) ($stats->overall_avg ?? 0),
            'total_reviews' => $stats->total_reviews,
            'needs_attention' => $lowestCategory,
            'impact_percentages' => $impactPercentages,
        ];
    }

    public function getAnalyticsByEstablishment($establishmentId)
    {
        $stats = DB::table('rating')
            ->where('establishment_id', $establishmentId)
            ->selectRaw('
                AVG(taste_rating) as taste_avg,
                AVG(environment_rating) as environment_avg,
                AVG(cleanliness_rating) as cleanliness_avg,
                AVG(service_rating) as service_avg,
                AVG(overall_rating) as overall_avg,
                COUNT(*) as total_reviews
            ')
            ->first();

        if ($stats->total_reviews == 0) {
            return [
                'averages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
                'overall_average' => 0,
                'total_reviews' => 0,
                'needs_attention' => null,
                'impact_percentages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
            ];
        }

        $categories = [
            'taste' => (float) ($stats->taste_avg ?? 0),
            'environment' => (float) ($stats->environment_avg ?? 0),
            'cleanliness' => (float) ($stats->cleanliness_avg ?? 0),
            'service' => (float) ($stats->service_avg ?? 0),
        ];

        $lowestCategory = array_keys($categories, min($categories))[0];

        $impactPercentages = $this->calculateImpactPercentages($categories, $stats->overall_avg);

        return [
            'averages' => $categories,
            'overall_average' => (float) ($stats->overall_avg ?? 0),
            'total_reviews' => $stats->total_reviews,
            'needs_attention' => $lowestCategory,
            'impact_percentages' => $impactPercentages,
        ];
    }

    public function generateInsights()
    {
        $establishments = Establishment::all();

        foreach ($establishments as $establishment) {
            $stats = DB::table('rating')
                ->where('establishment_id', $establishment->id)
                ->selectRaw('
                    AVG(taste_rating) as taste_avg,
                    AVG(environment_rating) as environment_avg,
                    AVG(cleanliness_rating) as cleanliness_avg,
                    AVG(service_rating) as service_avg,
                    COUNT(*) as review_count
                ')
                ->first();

            if ($stats->review_count == 0) {
                continue;
            }

            $categories = [
                'taste' => $stats->taste_avg,
                'environment' => $stats->environment_avg,
                'cleanliness' => $stats->cleanliness_avg,
                'service' => $stats->service_avg,
            ];

            $lowestCategory = array_keys($categories, min($categories))[0];
            $lowestAvg = $categories[$lowestCategory];

            $priority = $this->calculatePriority($lowestAvg);
            $impactScore = round((5 - $lowestAvg) * 0.15, 2);

            $insight = $this->getInsightText($lowestCategory);
            $suggestedAction = $this->getSuggestedAction($lowestCategory);

            Recommendation::updateOrCreate(
                [
                    'establishment_id' => $establishment->id,
                    'category' => $lowestCategory,
                ],
                [
                    'priority' => $priority,
                    'insight' => $insight,
                    'suggested_action' => $suggestedAction,
                    'impact_score' => $impactScore,
                    'based_on_reviews' => $stats->review_count,
                    'generated_at' => now(),
                ]
            );
        }
    }

    private function calculateImpactPercentages(array $categories, $overallAvg): array
    {
        $overallAvg = (float) ($overallAvg ?? 0);

        if ($overallAvg <= 0) {
            return [
                'taste' => 0,
                'environment' => 0,
                'cleanliness' => 0,
                'service' => 0,
            ];
        }

        $impactPercentages = [];
        foreach ($categories as $category => $average) {
            $impactPercentages[$category] = round(((float) $average / $overallAvg) * 25, 2);
        }

        return $impactPercentages;
    }

    private function calculatePriority($avg)
    {
        if ($avg < 3.5) {
            return 'high';
        } elseif ($avg <= 4.0) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private function getInsightText($category)
    {
        $insights = [
            'taste' => 'Customers are dissatisfied with the taste of your coffee.',
            'environment' => 'The environment ratings are low.',
            'cleanliness' => 'Cleanliness is a concern for customers.',
            'service' => 'Service quality needs improvement.',
        ];

        return $insights[$category] ?? 'General improvement needed.';
    }

    private function getSuggestedAction($category)
    {
        $actions = [
            'taste' => 'Consider upgrading your coffee beans or training baristas on brewing techniques.',
            'environment' => 'Improve the ambiance by updating decor or lighting.',
            'cleanliness' => 'Enhance cleaning protocols and maintain higher hygiene standards.',
            'service' => 'Train staff on customer service and response times.',
        ];

        return $actions[$category] ?? 'Focus on improving this area.';
    }

    public function generateInsightsForEstablishment($establishmentId)
    {
        $establishment = Establishment::find($establishmentId);

        if (!$establishment) {
            return;
        }

        $stats = DB::table('rating')
            ->where('establishment_id', $establishment->id)
            ->selectRaw('
                AVG(taste_rating) as taste_avg,
                AVG(environment_rating) as environment_avg,
                AVG(cleanliness_rating) as cleanliness_avg,
                AVG(service_rating) as service_avg,
                COUNT(*) as review_count
            ')
            ->first();

        if ($stats->review_count == 0) {
            return;
        }

        $categories = [
            'taste' => $stats->taste_avg,
            'environment' => $stats->environment_avg,
            'cleanliness' => $stats->cleanliness_avg,
            'service' => $stats->service_avg,
        ];

        $lowestCategory = array_keys($categories, min($categories))[0];
        $lowestAvg = $categories[$lowestCategory];

        $priority = $this->calculatePriority($lowestAvg);
        $impactScore = round((5 - $lowestAvg) * 0.15, 2);

        $insight = $this->getInsightText($lowestCategory);
        $suggestedAction = $this->getSuggestedAction($lowestCategory);

        Recommendation::updateOrCreate(
            [
                'establishment_id' => $establishment->id,
                'category' => $lowestCategory,
            ],
            [
                'priority' => $priority,
                'insight' => $insight,
                'suggested_action' => $suggestedAction,
                'impact_score' => $impactScore,
                'based_on_reviews' => $stats->review_count,
                'generated_at' => now(),
            ]
        );
    }

    public function getRecentReviews()
    {
        return DB::table('rating')
            ->join('users', 'rating.user_id', '=', 'users.id')
            ->join('establishments', 'rating.establishment_id', '=', 'establishments.id')
            ->select(
                'users.name as user_name',
                'establishments.name as establishment_name',
                'rating.created_at',
                'rating.taste_rating',
                'rating.environment_rating',
                'rating.cleanliness_rating',
                'rating.service_rating',
                'rating.owner_response'
            )
            ->orderBy('rating.created_at', 'desc')
            ->limit(5)
            ->get();
    }
}