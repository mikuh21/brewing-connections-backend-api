<?php

namespace App\Providers;

use App\Services\GA4Service;
use App\Services\BigQueryService;
use App\Services\VertexAIService;
use App\Services\GoogleMapsService;
use App\Services\MapboxService;
use App\Services\GisService;
use App\Services\CoffeeTrailService;
use App\Services\RecommendationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ─────────────────────────────────────────
        // No dependencies
        // ─────────────────────────────────────────
        $this->app->singleton(GA4Service::class,
            fn() => new GA4Service()
        );

        $this->app->singleton(BigQueryService::class,
            fn() => new BigQueryService()
        );

        $this->app->singleton(VertexAIService::class,
            fn() => new VertexAIService()
        );

        $this->app->singleton(GoogleMapsService::class,
            fn() => new GoogleMapsService()
        );

        $this->app->singleton(MapboxService::class,
            fn() => new MapboxService()
        );

        $this->app->singleton(GisService::class,
            fn() => new GisService()
        );

        // ─────────────────────────────────────────
        // Has dependencies — inject them
        // ─────────────────────────────────────────

        // CoffeeTrailService needs MapboxService
        $this->app->singleton(CoffeeTrailService::class,
            fn($app) => new CoffeeTrailService(
                $app->make(MapboxService::class)
            )
        );

        // RecommendationService needs GisService
        $this->app->singleton(RecommendationService::class,
            fn($app) => new RecommendationService(
                $app->make(GisService::class)
            )
        );
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}