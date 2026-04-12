<?php

namespace App\Observers;

use App\Models\Rating;
use App\Services\RecommendationAnalyticsService;

class RatingObserver
{
    protected $analyticsService;

    public function __construct(RecommendationAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Handle the Rating "created" event.
     */
    public function created(Rating $rating): void
    {
        // Generate insights for the establishment that received the new rating
        $this->analyticsService->generateInsightsForEstablishment($rating->establishment_id);
    }
}