<?php

use App\Services\RecommendationAnalyticsService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('recommendations:backfill-history {establishmentId?}', function (RecommendationAnalyticsService $analyticsService) {
    $establishmentId = $this->argument('establishmentId');
    $rebuiltCount = $analyticsService->rebuildHistoricalSnapshots(
        $establishmentId !== null ? (int) $establishmentId : null
    );

    $scope = $establishmentId !== null
        ? 'establishment '.$establishmentId
        : 'all establishments';

    $this->info('Rebuilt '.$rebuiltCount.' recommendation snapshots for '.$scope.'.');
})->purpose('Replay ratings history into recommendation snapshot history.');
