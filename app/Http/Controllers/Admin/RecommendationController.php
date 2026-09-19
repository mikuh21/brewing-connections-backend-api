<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Recommendation;
use App\Models\Rating;
use App\Services\RecommendationAnalyticsService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected $analyticsService;

    public function __construct(RecommendationAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $priority = $request->query('priority', 'all');
        $recentReviewsRange = $request->query('recent_reviews_range', 'all');

        if (!in_array($recentReviewsRange, ['all', 'this_week', 'this_month'], true)) {
            $recentReviewsRange = 'all';
        }

        $overallAnalytics = $this->analyticsService->getOverallAnalytics();
        $recentReviews = $this->analyticsService->getRecentReviews($recentReviewsRange);
        $recommendations = Recommendation::query()
            ->with('establishment')
            ->whereHas('establishment')
            ->get()
            ->groupBy('priority');

        // Only calculate establishment analytics for establishments that
        // actually have visible recommendations. This avoids loading every
        // establishment and running one rating query per establishment.
        $recommendationEstablishmentIds = $recommendations
            ->flatten()
            ->pluck('establishment_id')
            ->filter()
            ->unique()
            ->values();

        $analyticsByEstablishment = Rating::query()
            ->whereIn('establishment_id', $recommendationEstablishmentIds)
            ->selectRaw('
                establishment_id,
                AVG(taste_rating) AS taste_avg,
                AVG(environment_rating) AS environment_avg,
                AVG(cleanliness_rating) AS cleanliness_avg,
                AVG(service_rating) AS service_avg,
                AVG(overall_rating) AS overall_avg,
                COUNT(*) AS total_reviews
            ')
            ->groupBy('establishment_id')
            ->get()
            ->keyBy('establishment_id');

        $establishments = Establishment::query()
            ->whereIn('id', $recommendationEstablishmentIds)
            ->get()
            ->map(function ($est) use ($analyticsByEstablishment) {
                $stats = $analyticsByEstablishment->get($est->id);

                if (!$stats) {
                    $analytics = [
                        'averages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
                        'overall_average' => 0,
                        'total_reviews' => 0,
                        'needs_attention' => null,
                        'impact_percentages' => ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
                    ];
                } else {
                    $categories = [
                        'taste' => (float) ($stats->taste_avg ?? 0),
                        'environment' => (float) ($stats->environment_avg ?? 0),
                        'cleanliness' => (float) ($stats->cleanliness_avg ?? 0),
                        'service' => (float) ($stats->service_avg ?? 0),
                    ];
                    $overallAverage = (float) ($stats->overall_avg ?? 0);
                    $lowestCategory = collect($categories)->sort()->keys()->first();

                    $analytics = [
                        'averages' => $categories,
                        'overall_average' => $overallAverage,
                        'total_reviews' => (int) $stats->total_reviews,
                        'needs_attention' => $overallAverage > 0 && min($categories) < 3.0 ? $lowestCategory : null,
                        'impact_percentages' => $overallAverage > 0
                            ? collect($categories)->map(fn ($average) => round(((float) $average / $overallAverage) * 25, 2))->all()
                            : ['taste' => 0, 'environment' => 0, 'cleanliness' => 0, 'service' => 0],
                    ];
                }

                return [
                    'establishment' => $est,
                    'analytics' => $analytics,
                ];
            });

        return view('admin.recommendations', compact('overallAnalytics', 'recentReviews', 'recommendations', 'establishments', 'priority', 'recentReviewsRange'));
    }

    public function refresh(Request $request)
    {
        $this->analyticsService->generateInsights();

        return redirect()->back()->with('success', 'Insights generated successfully.');
    }
}