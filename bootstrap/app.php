<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'cafe.owner' => \App\Http\Middleware\CafeOwnerMiddleware::class,
            'reseller.verified' => \App\Http\Middleware\EnsureResellerVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (QueryException $exception, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::error('API database failure', [
                    'path' => $request->path(),
                    'message' => $exception->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Service is temporarily unavailable. Please try again shortly.',
                ], 503);
            }

            return null;
        });
    })->create();
