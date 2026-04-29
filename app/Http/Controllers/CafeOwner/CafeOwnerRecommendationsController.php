<?php

namespace App\Http\Controllers\CafeOwner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Rating;
use App\Models\Recommendation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CafeOwnerRecommendationsController extends Controller
{
    public function updateOwnerResponse(Request $request, Rating $rating)
    {
        $ownerUserId = Auth::id();

        $establishment = Establishment::query()
            ->whereKey($rating->establishment_id)
            ->when(
                Schema::hasColumn('establishments', 'user_id'),
                fn ($query) => $query->where('user_id', $ownerUserId),
                fn ($query) => $query->where('owner_id', $ownerUserId)
            )
            ->first();

        abort_unless($establishment, 403);

        $validated = $request->validate([
            'owner_response' => 'nullable|string|max:1500',
        ]);

        $ownerResponse = trim((string) ($validated['owner_response'] ?? ''));

        $rating->owner_response = $ownerResponse !== '' ? $ownerResponse : null;
        $rating->save();

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'owner_response' => $rating->owner_response,
                'rating_id' => (int) $rating->id,
            ]);
        }

        return back()->with('status', 'Owner response updated.');
    }

    public function index()
    {
        $userId = Auth::id();
        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $weekDateLabel = $weekStart->format('M d, Y').' - '.$weekEnd->format('M d, Y');
        $monthDateLabel = $monthStart->format('M d, Y').' - '.$monthEnd->format('M d, Y');
        $allTimeDateLabel = 'All Time';

        $establishment = Establishment::query()
            ->when(
                Schema::hasColumn('establishments', 'user_id'),
                fn ($query) => $query->where('user_id', $userId),
                fn ($query) => $query->where('owner_id', $userId)
            )
            ->first();

        if (!$establishment) {
            $avgRating = 0.0;
            $priorityLevel = $this->resolvePriorityLevel(0.0);

            $emptyInsightsFilterPayload = [
                'all' => [
                    'count' => 0,
                    'has_ratings' => false,
                    'date_label' => $allTimeDateLabel,
                    'journey_insights' => [],
                ],
                'month' => [
                    'count' => 0,
                    'has_ratings' => false,
                    'date_label' => $monthDateLabel,
                    'journey_insights' => [],
                ],
                'week' => [
                    'count' => 0,
                    'has_ratings' => false,
                    'date_label' => $weekDateLabel,
                    'journey_insights' => [],
                ],
            ];

            return view('cafe-owner.recommendations', [
                'avgRating' => $avgRating,
                'averageRating' => 0,
                'ratingDistribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'totalReviews' => 0,
                'monthlyRecommendationCount' => [
                    'labels' => $this->lastSixMonthLabels(),
                    'series' => [0, 0, 0, 0, 0, 0],
                ],
                'latestReviews' => collect(),
                'highCount' => 0,
                'mediumCount' => 0,
                'lowCount' => 0,
                'priorityLevel' => $priorityLevel,
                'priorityCategory' => 'taste',
                'priorityCategories' => ['taste'],
                'categoryAverages' => [
                    'taste' => 0,
                    'environment' => 0,
                    'cleanliness' => 0,
                    'service' => 0,
                ],
                'journeyInsights' => [],
                'currentWeekRecommendationCount' => 0,
                'weeklyHasRatings' => false,
                'insightsDateLabel' => $weekDateLabel,
                'insightsFilterPayload' => $emptyInsightsFilterPayload,
                'establishment' => null,
            ]);
        }

        $ratingColumn = Schema::hasColumn('rating', 'score') ? 'score' : 'overall_rating';

        $ratingsQuery = Rating::query()->where('establishment_id', $establishment->id);

        $averageRating = (float) ($ratingsQuery->clone()->avg($ratingColumn) ?? 0);
        $avgRating = round($averageRating, 2);
        $totalReviews = $ratingsQuery->clone()->count();

        $ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $highCount = 0;
        $mediumCount = 0;
        $lowCount = 0;
        $ratingsForDistribution = $ratingsQuery->clone()->get([$ratingColumn]);

        foreach ($ratingsForDistribution as $rating) {
            $rawScore = (float) ($rating->{$ratingColumn} ?? 0);
            $bucket = max(1, min(5, (int) round($rawScore)));
            $ratingDistribution[$bucket]++;

            if ($bucket <= 2) {
                $highCount++;
            } elseif ($bucket === 3) {
                $mediumCount++;
            } else {
                $lowCount++;
            }
        }

        $weeklyRatingsQuery = Rating::query()
            ->where('establishment_id', $establishment->id)
            ->whereBetween('created_at', [$weekStart, $weekEnd]);
        $weeklyTotalReviews = $weeklyRatingsQuery->clone()->count();
        $weeklyHasRatings = $weeklyTotalReviews > 0;

        $categoryColumns = [
            'taste' => 'taste_rating',
            'environment' => 'environment_rating',
            'cleanliness' => 'cleanliness_rating',
            'service' => 'service_rating',
        ];

        $recommendationTimestampColumn = Schema::hasColumn('recommendations', 'generated_at')
            ? 'generated_at'
            : 'created_at';

        $firstRatingAt = Rating::query()
            ->where('establishment_id', $establishment->id)
            ->min('created_at');
        $firstRecommendationAt = Recommendation::query()
            ->where('establishment_id', $establishment->id)
            ->min($recommendationTimestampColumn);

        $allTimeStart = collect([$firstRatingAt, $firstRecommendationAt])
            ->filter()
            ->map(fn ($value) => Carbon::parse((string) $value))
            ->sort()
            ->first();

        if ($allTimeStart instanceof Carbon) {
            $allTimeDateLabel = $allTimeStart->format('M d, Y').' - '.$now->format('M d, Y');
        }

        $buildPeriodInsightsPayload = function (?Carbon $start, ?Carbon $end, string $dateLabel) use ($establishment, $categoryColumns, $recommendationTimestampColumn) {
            $periodRatingsQuery = Rating::query()->where('establishment_id', $establishment->id);
            if ($start && $end) {
                $periodRatingsQuery->whereBetween('created_at', [$start, $end]);
            }

            $periodTotalReviews = (int) ($periodRatingsQuery->clone()->count() ?? 0);
            $periodHasRatings = $periodTotalReviews > 0;

            $periodCategoryAverages = collect($categoryColumns)
                ->mapWithKeys(function (string $column, string $key) use ($periodRatingsQuery) {
                    if (!Schema::hasColumn('rating', $column)) {
                        return [$key => 0.0];
                    }

                    $avg = (float) ($periodRatingsQuery->clone()->avg($column) ?? 0);
                    return [$key => round($avg, 2)];
                })
                ->toArray();

            $periodLowestScore = collect($periodCategoryAverages)->min() ?? 0.0;
            $periodPriorityCategories = collect($periodCategoryAverages)
                ->filter(fn (float $score) => round($score, 1) === round((float) $periodLowestScore, 1))
                ->keys()
                ->values()
                ->all();

            $periodPriorityCategory = $periodPriorityCategories[0] ?? 'taste';
            $periodPriorityLevel = $this->resolvePriorityLevel((float) $periodLowestScore);

            $periodRecommendationsQuery = Recommendation::query()
                ->where('establishment_id', $establishment->id);
            if ($start && $end) {
                $periodRecommendationsQuery->whereBetween($recommendationTimestampColumn, [$start, $end]);
            }

            $periodRecommendationsByCategory = $periodRecommendationsQuery->clone()
                ->orderByDesc('generated_at')
                ->orderByDesc('created_at')
                ->get()
                ->unique('category')
                ->keyBy('category');

            $periodRecommendationCount = (int) ($periodRecommendationsQuery->clone()->count() ?? 0);

            $periodJourneyInsights = $periodHasRatings
                ? collect($periodCategoryAverages)
                    ->map(function (float $score, string $category) use ($periodRecommendationsByCategory, $periodTotalReviews) {
                        return $this->buildJourneyInsight(
                            $category,
                            $score,
                            $periodTotalReviews,
                            $periodRecommendationsByCategory->get($category)
                        );
                    })
                    ->values()
                    ->all()
                : [];

            usort($periodJourneyInsights, function (array $a, array $b) {
                $priorityCmp = $this->priorityWeight($a['priority_key']) <=> $this->priorityWeight($b['priority_key']);
                if ($priorityCmp !== 0) {
                    return $priorityCmp;
                }

                return $a['score'] <=> $b['score'];
            });

            $priorityCategoryInsight = collect($periodJourneyInsights)
                ->first(fn (array $insight) => data_get($insight, 'category_key') === $periodPriorityCategory);

            if ($priorityCategoryInsight) {
                $periodJourneyInsights = array_values(array_filter(
                    $periodJourneyInsights,
                    fn (array $insight) => data_get($insight, 'category_key') !== $periodPriorityCategory
                ));

                array_unshift($periodJourneyInsights, $priorityCategoryInsight);
            }

            return [
                'count' => $periodRecommendationCount,
                'has_ratings' => $periodHasRatings,
                'date_label' => $dateLabel,
                'priority_level' => $periodPriorityLevel,
                'category_averages' => $periodCategoryAverages,
                'priority_category' => $periodPriorityCategory,
                'priority_categories' => $periodPriorityCategories,
                'journey_insights' => $periodJourneyInsights,
            ];
        };

        $insightsFilterPayload = [
            'all' => $buildPeriodInsightsPayload(null, null, $allTimeDateLabel),
            'month' => $buildPeriodInsightsPayload($monthStart, $monthEnd, $monthDateLabel),
            'week' => $buildPeriodInsightsPayload($weekStart, $weekEnd, $weekDateLabel),
        ];

        $weekInsightsPayload = $insightsFilterPayload['week'];
        $categoryAverages = (array) data_get($weekInsightsPayload, 'category_averages', []);
        $priorityLevel = (string) data_get($weekInsightsPayload, 'priority_level', $this->resolvePriorityLevel(0.0));
        $priorityCategory = (string) data_get($weekInsightsPayload, 'priority_category', 'taste');
        $priorityCategories = (array) data_get($weekInsightsPayload, 'priority_categories', [$priorityCategory]);
        $journeyInsights = (array) data_get($weekInsightsPayload, 'journey_insights', []);
        $currentWeekRecommendationCount = (int) data_get($weekInsightsPayload, 'count', 0);
        $weeklyHasRatings = (bool) data_get($weekInsightsPayload, 'has_ratings', false);
        $insightsDateLabel = (string) data_get($weekInsightsPayload, 'date_label', $weekDateLabel);

        $latestReviews = Rating::query()
            ->with('user:id,name')
            ->where('establishment_id', $establishment->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function (Rating $rating) use ($ratingColumn) {
                $image = (string) ($rating->image ?? '');
                $imageUrl = null;

                if ($image !== '') {
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $supabaseDisk */
                    $supabaseDisk = Storage::disk('supabase');
                    $imageUrl = Str::startsWith($image, ['http://', 'https://', '/'])
                        ? $image
                        : $supabaseDisk->url($image);
                }

                return [
                    'review_id' => (int) $rating->id,
                    'user_name' => $rating->user?->name ?? 'Anonymous',
                    'score' => (float) ($rating->{$ratingColumn} ?? 0),
                    'taste_rating' => (int) ($rating->taste_rating ?? 0),
                    'environment_rating' => (int) ($rating->environment_rating ?? 0),
                    'cleanliness_rating' => (int) ($rating->cleanliness_rating ?? 0),
                    'service_rating' => (int) ($rating->service_rating ?? 0),
                    'owner_response' => $rating->owner_response,
                    'image_url' => $imageUrl,
                    'created_at' => $rating->created_at,
                ];
            });

        $months = collect(range(5, 1))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset))
            ->push(now()->startOfMonth());

        $recommendationRows = Recommendation::query()
            ->where('establishment_id', $establishment->id)
            ->where($recommendationTimestampColumn, '>=', now()->startOfMonth()->subMonths(5))
            ->get([$recommendationTimestampColumn]);

        $countByMonth = $recommendationRows
            ->groupBy(fn ($row) => Carbon::parse(data_get($row, $recommendationTimestampColumn))->format('Y-m'))
            ->map(fn ($group) => $group->count())
            ->toArray();

        $monthlyRecommendationCount = [
            'labels' => $months->map(fn (Carbon $month) => $month->format('M Y'))->values()->all(),
            'series' => $months->map(fn (Carbon $month) => (int) ($countByMonth[$month->format('Y-m')] ?? 0))->values()->all(),
        ];

        return view('cafe-owner.recommendations', compact(
            'avgRating',
            'averageRating',
            'ratingDistribution',
            'totalReviews',
            'monthlyRecommendationCount',
            'latestReviews',
            'highCount',
            'mediumCount',
            'lowCount',
            'priorityLevel',
            'priorityCategory',
            'priorityCategories',
            'categoryAverages',
            'journeyInsights',
            'currentWeekRecommendationCount',
            'weeklyHasRatings',
            'insightsDateLabel',
            'insightsFilterPayload',
            'establishment'
        ));
    }

    protected function buildJourneyInsight(string $category, float $score, int $totalReviews, ?Recommendation $generated): array
    {
        $labels = [
            'taste' => 'Taste',
            'environment' => 'Environment',
            'cleanliness' => 'Cleanliness',
            'service' => 'Service',
        ];

        $priorityKey = $this->priorityKeyFromScore($score);
        $descriptive = $this->descriptiveRecommendation($category, $priorityKey);
        $prescriptive = $this->prescriptiveRecommendations($category, $priorityKey);

        if ($generated && filled($generated->suggested_action)) {
            array_unshift($prescriptive, (string) $generated->suggested_action);
            $prescriptive = collect($prescriptive)->unique()->values()->take(4)->all();
        }

        $generatedInsight = $generated && filled($generated->insight)
            ? (string) $generated->insight
            : null;

        $evidence = $totalReviews > 0
            ? 'Rule basis: '.number_format($score, 2).'/5 average from '.$totalReviews.' customer ratings.'
            : 'Rule basis: insufficient ratings yet; recommendations are default safeguards for this journey stage.';

        return [
            'category_key' => $category,
            'category_label' => $labels[$category] ?? ucfirst($category),
            'priority_key' => $priorityKey,
            'priority_label' => ucfirst($priorityKey).' Priority',
            'score' => round($score, 2),
            'score_bucket' => max(0, min(5, (int) round($score))),
            'descriptive' => $generatedInsight ?: $descriptive,
            'prescriptive' => $prescriptive,
            'evidence' => $evidence,
            'generated_at' => $generated?->generated_at,
        ];
    }

    protected function priorityKeyFromScore(float $score): string
    {
        $score = round($score, 1);

        if ($score < 3.0) {
            return 'high';
        }

        if ($score < 4.0) {
            return 'medium';
        }

        return 'low';
    }

    protected function priorityWeight(string $priorityKey): int
    {
        return match ($priorityKey) {
            'high' => 0,
            'medium' => 1,
            default => 2,
        };
    }

    protected function descriptiveRecommendation(string $category, string $priorityKey): string
    {
        $rules = [
            'taste' => [
                'high' => 'Customer journey feedback shows flavor inconsistency and weak cup quality are hurting repeat intent.',
                'medium' => 'Flavor quality is acceptable but not memorable; guests are likely neutral rather than delighted.',
                'low' => 'Taste performance is strong and consistently supports a positive cafe experience.',
            ],
            'environment' => [
                'high' => 'Ambiance signals in the experience journey indicate discomfort in seating, lighting, or noise control.',
                'medium' => 'Environment is functional but lacks distinctive comfort cues that extend dwell time.',
                'low' => 'Environment quality is helping guests stay longer and supports positive perception of the cafe.',
            ],
            'cleanliness' => [
                'high' => 'Hygiene-related journey touchpoints are creating trust gaps for customers.',
                'medium' => 'Cleanliness is generally acceptable but inconsistencies are still visible to guests.',
                'low' => 'Cleanliness standards are consistently supporting customer confidence and comfort.',
            ],
            'service' => [
                'high' => 'Service journey signals show delays or interaction quality issues that weaken satisfaction.',
                'medium' => 'Service delivery is stable but can be more proactive and personalized.',
                'low' => 'Service quality is strong and contributes positively to overall guest experience.',
            ],
        ];

        return data_get($rules, $category.'.'.$priorityKey, 'Customer experience in this category needs continuous improvement.');
    }

    protected function prescriptiveRecommendations(string $category, string $priorityKey): array
    {
        $rules = [
            'taste' => [
                'high' => [
                    'Run a weekly espresso and filter calibration checklist before opening.',
                    'Standardize brew recipes by dose, yield, and extraction time for every barista shift.',
                    'Perform blind cupping on best-sellers and retire low-performing beans or roast profiles.',
                ],
                'medium' => [
                    'Refine best-selling drinks with micro-adjustments and collect quick taste feedback cards.',
                    'Introduce a quality control cup test every two hours during peak periods.',
                    'Pair menu items with recommended roast profiles to improve consistency.',
                ],
                'low' => [
                    'Maintain calibration logs and keep quarterly sensory training for baristas.',
                    'Document signature recipe standards to preserve consistency as staff rotates.',
                ],
            ],
            'environment' => [
                'high' => [
                    'Redesign seating flow to reduce congestion and improve movement comfort.',
                    'Adjust lighting zones for table usability and visual warmth during peak hours.',
                    'Set a noise-management routine with playlist and equipment volume thresholds.',
                ],
                'medium' => [
                    'Introduce small comfort upgrades such as better seat spacing and table stability checks.',
                    'Add clear ambiance anchors such as scent consistency and zoning music by daypart.',
                    'Monitor heat and ventilation by time block and correct hotspots.',
                ],
                'low' => [
                    'Preserve current ambiance standards and run monthly environment walk-through audits.',
                    'Use guest dwell-time observations to keep high-performing layout choices.',
                ],
            ],
            'cleanliness' => [
                'high' => [
                    'Implement hourly sanitation checkpoints for tables, counters, and high-touch surfaces.',
                    'Assign clear ownership per shift for restroom and hand-wash station readiness.',
                    'Display visible cleaning logs to reinforce trust during customer visits.',
                ],
                'medium' => [
                    'Tighten mid-shift deep-clean micro-routines for bar and dining zones.',
                    'Audit cleaning supply availability at opening and pre-rush windows.',
                    'Run weekly spot inspections with photo evidence for recurring issues.',
                ],
                'low' => [
                    'Sustain hygiene standards with periodic verification and retraining refreshers.',
                    'Keep preventive maintenance for drains, restrooms, and waste areas on schedule.',
                ],
            ],
            'service' => [
                'high' => [
                    'Set service-time targets per order type and monitor queue response in each shift.',
                    'Deploy a service recovery script for delayed or incorrect orders.',
                    'Coach frontline staff on acknowledgment within 10 seconds of customer arrival.',
                ],
                'medium' => [
                    'Add proactive check-ins at pickup and dine-in midpoint touchpoints.',
                    'Use pre-shift briefing to align on rush roles and handoff responsibilities.',
                    'Track repeat complaints weekly and close loop with targeted coaching.',
                ],
                'low' => [
                    'Maintain strong service through peer shadowing and monthly role-play drills.',
                    'Capture and share positive service moments as internal best practices.',
                ],
            ],
        ];

        return data_get($rules, $category.'.'.$priorityKey, ['Monitor this category and continuously optimize customer journey touchpoints.']);
    }

    protected function resolvePriorityLevel(float $avgRating): string
    {
        $avgRating = round($avgRating, 1);

        if ($avgRating < 3.0) {
            return 'High Priority';
        }

        if ($avgRating < 4.0) {
            return 'Medium Priority';
        }

        return 'Low Priority';
    }

    protected function lastSixMonthLabels(): array
    {
        return collect(range(5, 1))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset)->format('M Y'))
            ->push(now()->startOfMonth()->format('M Y'))
            ->values()
            ->all();
    }
}
