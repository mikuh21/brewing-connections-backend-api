<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Recommendation;
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
        $overallAnalytics = $this->analyticsService->getOverallAnalytics();
        $recentReviews = $this->analyticsService->getRecentReviews();
        if (in_array($priority, ['high', 'medium', 'low'])) {
            // When filtering by priority, only fetch recommendations for that priority
            $filteredRecommendations = Recommendation::with('establishment')
                ->where('priority', $priority)
                ->get();
            $recommendations = collect([
                'high' => $priority === 'high' ? $filteredRecommendations : collect(),
                'medium' => $priority === 'medium' ? $filteredRecommendations : collect(),
                'low' => $priority === 'low' ? $filteredRecommendations : collect(),
            ]);
        } else {
            $recommendations = Recommendation::with('establishment')->get()->groupBy('priority');
        }
        $establishments = Establishment::all()->map(function ($est) {
            return [
                'establishment' => $est,
                'analytics' => $this->analyticsService->getAnalyticsByEstablishment($est->id),
            ];
        });

        return view('admin.recommendations', compact('overallAnalytics', 'recentReviews', 'recommendations', 'establishments', 'priority'));
    }

    public function refresh(Request $request)
    {
        $this->analyticsService->generateInsights();

        return redirect()->back()->with('success', 'Insights generated successfully.');
    }
}