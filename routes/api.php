<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EstablishmentGeoJsonController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Consumer\PublicEstablishmentGeoJsonController;
use App\Http\Controllers\Web\LandingReservationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordResetController::class, 'sendOtp']);
Route::post('/password/reset', [PasswordResetController::class, 'resetWithOtp']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp']);
Route::post('/password/email', [PasswordResetController::class, 'sendOtp']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'me']);
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'sendOtp'])
        ->middleware(['throttle:6,1']);
    Route::post('/email/verify-otp', [EmailVerificationController::class, 'verifyOtp'])
        ->middleware(['throttle:6,1']);
    Route::get('/orders', [MarketplaceController::class, 'orders']);
    Route::post('/orders', [MarketplaceController::class, 'storeOrder']);
    Route::patch('/orders/{order}', [MarketplaceController::class, 'updateOrder']);
    Route::get('/conversations', [ChatController::class, 'conversations']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);
    Route::post('/conversations', [ChatController::class, 'storeConversation']);
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);
    Route::get('/chat/recipients', [ChatController::class, 'recipients']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::post('/reservations/landing/prefill-token', [LandingReservationController::class, 'createPrefillToken']);
});

Route::get('/products', [MarketplaceController::class, 'products']);

// Admin-protected GeoJSON endpoint (session auth for web, admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/establishments/geojson', [EstablishmentGeoJsonController::class, 'index']);
});

// Public read-only GeoJSON endpoint
Route::get('/public/establishments/geojson', [PublicEstablishmentGeoJsonController::class, 'index']);
Route::get('/mobile/establishments/geojson', [PublicEstablishmentGeoJsonController::class, 'index']);

// Save (or accept) user location from client
Route::middleware(['auth'])->put('/user-location', [\App\Http\Controllers\UserLocationController::class, 'update']);

// Coffee trail generation via Mapbox Optimization
Route::middleware(['auth:api'])->post('/coffee-trail/preview', [\App\Http\Controllers\Api\CoffeeTrailController::class, 'preview']);
Route::middleware(['auth:api'])->post('/coffee-trail/generate', [\App\Http\Controllers\Api\CoffeeTrailController::class, 'generate']);
Route::middleware(['auth:api'])->get('/coffee-trail/history', [\App\Http\Controllers\Api\CoffeeTrailController::class, 'history']);
Route::middleware(['auth:api'])->post('/coffee-trail/marker-view', [\App\Http\Controllers\Api\CoffeeTrailController::class, 'trackMarkerView']);

// Consumer ratings feed and submission
Route::middleware(['auth:api'])->get('/ratings', [RatingController::class, 'index']);
Route::middleware(['auth:api'])->post('/ratings', [RatingController::class, 'store']);

// Navigation directions via Google Maps
Route::middleware(['auth:api'])->post('/navigation/directions', [\App\Http\Controllers\NavigationController::class, 'getDirections']);
Route::middleware(['auth:api'])->post('/mobile/navigation/directions', [\App\Http\Controllers\NavigationController::class, 'getDirections']);

// Mobile coupon promos
Route::get('/coupon-promos', [\App\Http\Controllers\Api\CouponPromoController::class, 'index']);
Route::middleware(['auth:api'])->post('/coupon-promos/verify-qr', [\App\Http\Controllers\Api\CouponPromoController::class, 'verifyQr']);
