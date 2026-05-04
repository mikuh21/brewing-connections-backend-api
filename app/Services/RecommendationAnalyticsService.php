<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\Rating;
use App\Models\Recommendation;
use App\Models\RecommendationSnapshot;
use Illuminate\Support\Facades\DB;

class RecommendationAnalyticsService
{
    private const ATTENTION_THRESHOLD = 3.0;
    private const MEDIUM_PRIORITY_THRESHOLD = 4.0;

    private function baseCafeRatingsQuery(?int $establishmentId = null)
    {
        return Rating::query()
            ->whereNotNull('establishment_id')
            ->when($establishmentId, fn ($query) => $query->where('establishment_id', $establishmentId));
    }

    private function aggregateCafeRatingStats(?int $establishmentId = null): object
    {
        return $this->baseCafeRatingsQuery($establishmentId)
            ->selectRaw('
                AVG(taste_rating) as taste_avg,
                AVG(environment_rating) as environment_avg,
                AVG(cleanliness_rating) as cleanliness_avg,
                AVG(service_rating) as service_avg,
                AVG(overall_rating) as overall_avg,
                COUNT(*) as total_reviews
            ')
            ->first();
    }

    public function getOverallAnalytics()
    {
        $stats = $this->aggregateCafeRatingStats();

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
        $stats = $this->aggregateCafeRatingStats((int) $establishmentId);

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
            $stats = $this->baseCafeRatingsQuery((int) $establishment->id)
                ->selectRaw('
                    AVG(taste_rating) as taste_avg,
                    AVG(environment_rating) as environment_avg,
                    AVG(cleanliness_rating) as cleanliness_avg,
                    AVG(service_rating) as service_avg,
                    COUNT(*) as review_count
                ')
                ->first();

            if ($stats->review_count == 0) {
                $this->clearInsightsForEstablishment((int) $establishment->id);
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

        $this->persistRecommendationSnapshot(
            $establishmentId,
            $categories,
            (int) ($stats->review_count ?? 0)
        );
    }

    private function clearInsightsForEstablishment(int $establishmentId): void
    {
        RecommendationSnapshot::query()
            ->where('establishment_id', $establishmentId)
            ->delete();

        Recommendation::query()
            ->where('establishment_id', $establishmentId)
            ->delete();
    }

    public function rebuildHistoricalSnapshots(?int $establishmentId = null): int
    {
        $establishmentIds = $establishmentId
            ? collect([$establishmentId])
            : $this->baseCafeRatingsQuery()
                ->select('establishment_id')
                ->groupBy('establishment_id')
                ->pluck('establishment_id')
                ->merge(RecommendationSnapshot::query()->pluck('establishment_id'))
                ->merge(Recommendation::query()->pluck('establishment_id'))
                ->filter()
                ->unique()
                ->values();

        $rebuiltCount = 0;

        foreach ($establishmentIds as $currentEstablishmentId) {
            DB::transaction(function () use ($currentEstablishmentId, &$rebuiltCount) {
                $this->clearInsightsForEstablishment((int) $currentEstablishmentId);

                $ratings = $this->baseCafeRatingsQuery((int) $currentEstablishmentId)
                    ->orderBy('created_at')
                    ->get([
                        'taste_rating',
                        'environment_rating',
                        'cleanliness_rating',
                        'service_rating',
                        'created_at',
                    ]);

                if ($ratings->isEmpty()) {
                    return;
                }

                $runningTotals = [
                    'taste' => 0.0,
                    'environment' => 0.0,
                    'cleanliness' => 0.0,
                    'service' => 0.0,
                ];

                $reviewCount = 0;

                foreach ($ratings as $rating) {
                    $reviewCount++;
                    $runningTotals['taste'] += (float) ($rating->taste_rating ?? 0);
                    $runningTotals['environment'] += (float) ($rating->environment_rating ?? 0);
                    $runningTotals['cleanliness'] += (float) ($rating->cleanliness_rating ?? 0);
                    $runningTotals['service'] += (float) ($rating->service_rating ?? 0);

                    $categories = collect($runningTotals)
                        ->map(fn (float $total) => round($total / max(1, $reviewCount), 2))
                        ->all();

                    $this->persistRecommendationSnapshot(
                        (int) $currentEstablishmentId,
                        $categories,
                        $reviewCount,
                        $rating->created_at
                    );

                    $rebuiltCount++;
                }
            });
        }

        return $rebuiltCount;
    }

    private function persistRecommendationSnapshot(
        int $establishmentId,
        array $categories,
        int $reviewCount,
        $generatedAt = null
    ): void {
        $generatedAt = $generatedAt ?? now();

        Recommendation::where('establishment_id', $establishmentId)
            ->whereNotIn('category', array_keys($categories))
            ->delete();

        $snapshot = RecommendationSnapshot::query()->create([
            'establishment_id' => $establishmentId,
            'review_count' => $reviewCount,
            'generated_at' => $generatedAt,
        ]);

        foreach ($categories as $category => $average) {
            $priority = $this->calculatePriority($average);
            $insight = $this->getInsightText($category, $priority);
            $suggestedAction = $this->getSuggestedAction($category, $priority);
            $impactScore = round((5 - $average) * 0.15, 2);

            $snapshot->items()->create([
                'category' => $category,
                'priority' => $priority,
                'average_score' => round((float) $average, 2),
                'insight' => $insight,
                'suggested_action' => $suggestedAction,
                'impact_score' => $impactScore,
                'based_on_reviews' => $reviewCount,
                'generated_at' => $generatedAt,
            ]);

            Recommendation::updateOrCreate(
                [
                    'establishment_id' => $establishmentId,
                    'category' => $category,
                ],
                [
                    'priority' => $priority,
                    'insight' => $insight,
                    'suggested_action' => $suggestedAction,
                    'impact_score' => $impactScore,
                    'based_on_reviews' => $reviewCount,
                    'generated_at' => $generatedAt,
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

    private function getInsightText($category, $priority)
    {
        $rules = [
            'taste' => [
                'high' => 'Customer journey feedback shows flavor inconsistency and weak cup quality are hurting repeat intent.',
                'medium' => 'Flavor quality is acceptable but not memorable; guests are likely neutral rather than delighted.',
                'low' => 'Taste performance is strong and consistently supports a positive cafe experience.',
            ],
            'environment' => [
                'high' => 'Ambiance signals indicate discomfort in seating, lighting, or noise control.',
                'medium' => 'Environment is functional but lacks distinctive comfort cues that extend dwell time.',
                'low' => 'Environment quality is helping guests stay longer and supports positive perception of the cafe.',
            ],
            'cleanliness' => [
                'high' => 'Hygiene touchpoints are creating visible trust gaps for customers.',
                'medium' => 'Cleanliness is generally acceptable but some inconsistencies remain.',
                'low' => 'Cleanliness standards are consistently supporting customer confidence and comfort.',
            ],
            'service' => [
                'high' => 'Service delivery issues are weakening satisfaction and order flow.',
                'medium' => 'Service is stable but could be more proactive and personalized.',
                'low' => 'Service quality is strong and contributes positively to the guest experience.',
            ],
        ];

        return data_get($rules, $category.'.'.$priority, 'General improvement needed.');
    }

    private function getSuggestedAction($category, $priority)
    {
        $rules = [
            'taste' => [
                'high' => 'Run a weekly espresso and filter calibration checklist before opening.',
                'medium' => 'Refine best-selling drinks with micro-adjustments and collect quick taste feedback cards.',
                'low' => 'Maintain calibration logs and keep quarterly sensory training for baristas.',
            ],
            'environment' => [
                'high' => 'Redesign seating flow to reduce congestion and improve movement comfort.',
                'medium' => 'Introduce small comfort upgrades such as better seat spacing and table stability checks.',
                'low' => 'Preserve current ambiance standards and run monthly environment walk-through audits.',
            ],
            'cleanliness' => [
                'high' => 'Implement hourly sanitation checkpoints for tables, counters, and high-touch surfaces.',
                'medium' => 'Tighten mid-shift deep-clean micro-routines for bar and dining zones.',
                'low' => 'Sustain hygiene standards with periodic verification and retraining refreshers.',
            ],
            'service' => [
                'high' => 'Set service-time targets per order type and monitor queue response in each shift.',
                'medium' => 'Add proactive check-ins at pickup and dine-in midpoint touchpoints.',
                'low' => 'Maintain strong service through peer shadowing and monthly role-play drills.',
            ],
        ];

        return data_get($rules, $category.'.'.$priority, 'Focus on improving this area.');
    }

    public function generateInsightsForEstablishment($establishmentId)
    {
        $establishment = Establishment::find($establishmentId);

        if (!$establishment) {
            return;
        }

        $stats = $this->baseCafeRatingsQuery((int) $establishment->id)
            ->selectRaw('
                AVG(taste_rating) as taste_avg,
                AVG(environment_rating) as environment_avg,
                AVG(cleanliness_rating) as cleanliness_avg,
                AVG(service_rating) as service_avg,
                COUNT(*) as review_count
            ')
            ->first();

        if ($stats->review_count == 0) {
            $this->clearInsightsForEstablishment((int) $establishment->id);
            return;
        }

        $this->syncRecommendationsForEstablishment($establishment->id, $stats);
    }

    public function getRecentReviews(string $range = 'all')
    {
        $query = $this->baseCafeRatingsQuery()
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
            );

        if ($range === 'this_week') {
            $query->whereBetween('rating.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($range === 'this_month') {
            $query->whereBetween('rating.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        return $query
            ->orderBy('rating.created_at', 'desc')
            ->get();
    }
}