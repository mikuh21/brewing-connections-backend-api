<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

class RecommendationAnalyticsService
{
    private const ATTENTION_THRESHOLD = 3.0;
    private const MEDIUM_PRIORITY_THRESHOLD = 4.0;

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

        $lowestCategory = $this->determineNeedsAttentionCategory($categories);

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

        $lowestCategory = $this->determineNeedsAttentionCategory($categories);

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

            $this->syncRecommendationsForEstablishment($establishment->id, $stats);
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
        $avg = round((float) $avg, 1);

        if ($avg < self::ATTENTION_THRESHOLD) {
            return 'high';
        } elseif ($avg < self::MEDIUM_PRIORITY_THRESHOLD) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    private function syncRecommendationsForEstablishment(int $establishmentId, object $stats): void
    {
        $categories = [
            'taste' => (float) ($stats->taste_avg ?? 0),
            'environment' => (float) ($stats->environment_avg ?? 0),
            'cleanliness' => (float) ($stats->cleanliness_avg ?? 0),
            'service' => (float) ($stats->service_avg ?? 0),
        ];

        Recommendation::where('establishment_id', $establishmentId)
            ->whereNotIn('category', array_keys($categories))
            ->delete();

        foreach ($categories as $category => $average) {
            Recommendation::updateOrCreate(
                [
                    'establishment_id' => $establishmentId,
                    'category' => $category,
                ],
                [
                    'priority' => $this->calculatePriority($average),
                    'insight' => $this->getInsightText($category),
                    'suggested_action' => $this->getSuggestedAction($category),
                    'impact_score' => round((5 - $average) * 0.15, 2),
                    'based_on_reviews' => (int) ($stats->review_count ?? 0),
                    'generated_at' => now(),
                ]
            );
        }
    }

    private function determineNeedsAttentionCategory(array $categories): ?string
    {
        if (empty($categories)) {
            return null;
        }

        $roundedCategories = collect($categories)
            ->map(fn ($avg) => round((float) $avg, 1))
            ->toArray();

        $lowestAverage = min($roundedCategories);
        if ($lowestAverage >= self::ATTENTION_THRESHOLD) {
            return null;
        }

        return array_keys($roundedCategories, $lowestAverage)[0] ?? null;
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

        $this->syncRecommendationsForEstablishment($establishment->id, $stats);
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
                'rating.image',
                'rating.overall_rating',
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