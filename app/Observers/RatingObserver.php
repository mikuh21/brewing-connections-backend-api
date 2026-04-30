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
        if ((int) ($rating->establishment_id ?? 0) <= 0) {
            return;
        }

        $this->analyticsService->generateInsightsForEstablishment((int) $rating->establishment_id);
    }
}