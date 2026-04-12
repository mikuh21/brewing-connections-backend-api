<?php

namespace Database\Seeders;

use App\Services\RecommendationAnalyticsService;
use Illuminate\Database\Seeder;

class RecommendationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $analyticsService = app(RecommendationAnalyticsService::class);
        $analyticsService->generateInsights();
    }
}