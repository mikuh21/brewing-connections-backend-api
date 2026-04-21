<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ResellerRegistrationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EstablishmentController;
use App\Http\Controllers\Admin\EstablishmentGeoJsonController;
use App\Http\Controllers\Admin\CouponPromoController;
use App\Http\Controllers\Consumer\PublicEstablishmentGeoJsonController;
use App\Http\Controllers\Owner\FarmOwnerController;
use App\Http\Controllers\UserLocationController;
use App\Http\Controllers\CafeOwner\CafeOwnerDashboardController;
use App\Http\Controllers\CafeOwner\CafeOwnerMyCafeController;
use App\Http\Controllers\CafeOwner\CafeOwnerMarketplaceController;
use App\Http\Controllers\CafeOwner\CafeOwnerRecommendationsController;
use App\Http\Controllers\CafeOwner\CafeOwnerMapController;
use App\Http\Controllers\CafeOwner\CafeOwnerMessagesController;
use App\Http\Controllers\CafeOwner\CafeOwnerCouponPromoController;
use App\Http\Controllers\Reseller\ResellerDashboardController;
use App\Http\Controllers\Web\LandingReservationController;
use App\Models\Establishment;
use App\Models\Product;

Route::get('/', function () {
    $farmProducts = Product::query()
        ->with(['establishment'])
        ->where('seller_type', 'farm_owner')
        ->latest()
        ->take(8)
        ->get();

    $featuredFarms = Establishment::query()
        ->whereNull('deleted_at')
        ->where('type', 'farm')
        ->latest()
        ->take(3)
        ->get();

    $featuredCoffeeShops = Establishment::query()
        ->whereNull('deleted_at')
        ->where('type', 'cafe')
        ->withAvg('reviews', 'overall_rating')
        ->with(['couponPromos' => function ($query) {
            $query->active()->latest('valid_until');
        }])
        ->latest()
        ->take(3)
        ->get();

    return view('landing', compact('farmProducts', 'featuredFarms', 'featuredCoffeeShops'));
});

Route::post('/reservations/orders', [LandingReservationController::class, 'store'])
    ->name('reservations.orders.store');
Route::get('/reservations/prefill/{token}', [LandingReservationController::class, 'getPrefillData'])
    ->where('token', '[A-Za-z0-9]+')
    ->name('reservations.prefill.show');
Route::get('/reservations/orders/{order}/receipt', [LandingReservationController::class, 'showReceipt'])
    ->name('reservations.orders.receipt');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset.submit');
Route::post('/reseller/register', [ResellerRegistrationController::class, 'store'])->name('reseller.register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'sendMessage'])
        ->name('chat.messages.store');
});

// Shared map endpoint for admin and owner/reseller roles
Route::middleware(['auth', 'role:admin,farm_owner,cafe_owner,reseller'])->group(function () {
    Route::get('/api/establishments/geojson', [EstablishmentGeoJsonController::class, 'index']);
    Route::put('/api/user-location', [UserLocationController::class, 'update']);
    Route::post('/api/navigation/directions', [\App\Http\Controllers\NavigationController::class, 'getDirections']);
});

// Admin dashboard (admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/notifications', [DashboardController::class, 'notifications'])->name('admin.notifications');

    // GeoJSON endpoints
    Route::get('/api/public/establishments/geojson', [PublicEstablishmentGeoJsonController::class, 'index']);
    // Map routes
    Route::get('/admin/map', [MapController::class, 'index'])->name('admin.map');
    Route::get('/admin/map/resellers/verified', [MapController::class, 'verifiedResellers'])->name('admin.map.resellers.verified');
    Route::post('/admin/map', [MapController::class, 'store'])->name('admin.map.store');
    Route::patch('/admin/map/{id}', [MapController::class, 'update'])->name('admin.map.update');
    Route::patch('/admin/map/resellers/{id}/location', [MapController::class, 'updateResellerLocation'])->name('admin.map.resellers.update-location');
    Route::delete('/admin/map/{id}', [MapController::class, 'destroy'])->name('admin.map.destroy');

    // Establishments routes
    Route::get('/admin/establishments', [EstablishmentController::class, 'index'])->name('admin.establishments.index');
    Route::delete('/admin/establishments/{establishment}', [EstablishmentController::class, 'destroy'])->name('admin.establishments.destroy');

    // Registration routes
    Route::get('/admin/registrations', [\App\Http\Controllers\Admin\RegistrationController::class, 'index'])->name('admin.registrations.index');
    Route::post('/admin/registrations/{user}/deactivate', [\App\Http\Controllers\Admin\RegistrationController::class, 'deactivate'])->name('admin.registrations.deactivate');

    // Reseller routes
    Route::get('/admin/resellers', [\App\Http\Controllers\Admin\ResellerController::class, 'index'])->name('admin.resellers.index');
    Route::post('/admin/resellers/{user}/verify', [\App\Http\Controllers\Admin\ResellerController::class, 'verify'])->name('admin.resellers.verify');
    Route::post('/admin/resellers/{user}/deactivate', [\App\Http\Controllers\Admin\ResellerController::class, 'deactivate'])->name('admin.resellers.deactivate');

    // Coupon Promo routes
    Route::get('/admin/coupon-promos', [CouponPromoController::class, 'index'])->name('admin.coupon-promos.index');
    Route::get('/admin/coupon-promos/{id}', [CouponPromoController::class, 'show'])->name('admin.coupon-promos.show');
    Route::delete('/admin/coupon-promos/{id}', [CouponPromoController::class, 'destroy'])->name('admin.coupon-promos.destroy');

    // Rating Moderation routes
    Route::get('/admin/rating-moderation', [App\Http\Controllers\Admin\RatingModerationController::class, 'index'])->name('admin.rating-moderation.index');
    Route::delete('/admin/rating-moderation/{rating}', [App\Http\Controllers\Admin\RatingModerationController::class, 'destroy'])->name('admin.rating-moderation.destroy');

    // Recommendations routes
    Route::get('/admin/recommendations', [App\Http\Controllers\Admin\RecommendationController::class, 'index'])->name('admin.recommendations');
    Route::post('/admin/recommendations/refresh', [App\Http\Controllers\Admin\RecommendationController::class, 'refresh'])->name('admin.recommendations.refresh');

    // Marketplace routes
    Route::get('/admin/marketplace', [App\Http\Controllers\Admin\MarketplaceController::class, 'index'])->name('admin.marketplace.index');
    Route::delete('/admin/marketplace/products/{id}', [App\Http\Controllers\Admin\MarketplaceController::class, 'destroyProduct'])->name('admin.marketplace.products.destroy');
    Route::delete('/admin/marketplace/reseller-products/{id}', [App\Http\Controllers\Admin\MarketplaceController::class, 'destroyResellerProduct'])->name('admin.marketplace.reseller-products.destroy');
    // Route::delete('/admin/marketplace/orders/{id}', [App\Http\Controllers\Admin\MarketplaceController::class, 'destroyOrder'])->name('admin.marketplace.orders.destroy');
    // Route::delete('/admin/marketplace/bulk-orders/{id}', [App\Http\Controllers\Admin\MarketplaceController::class, 'destroyBulkOrder'])->name('admin.marketplace.bulk-orders.destroy');

    // Add more /admin/* routes here
});

// Cafe owner routes (cafe_owner only)
Route::prefix('cafe-owner')
    ->name('cafe-owner.')
    ->middleware(['auth', 'cafe.owner'])
    ->group(function () {
        Route::get('/dashboard', [CafeOwnerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications', [CafeOwnerDashboardController::class, 'notifications'])->name('notifications');
        Route::get('/my-cafe', [CafeOwnerMyCafeController::class, 'index'])->name('my-cafe');
        Route::post('/my-cafe', [CafeOwnerMyCafeController::class, 'update'])->name('my-cafe.update');
        Route::get('/coupon-promos', [CafeOwnerCouponPromoController::class, 'index'])->name('coupon-promos');
        Route::post('/coupon-promos', [CafeOwnerCouponPromoController::class, 'store'])->name('coupon-promos.store');
        Route::patch('/coupon-promos/{id}', [CafeOwnerCouponPromoController::class, 'update'])->name('coupon-promos.update');
        Route::delete('/coupon-promos/{id}', [CafeOwnerCouponPromoController::class, 'destroy'])->name('coupon-promos.destroy');
        Route::get('/marketplace', [CafeOwnerMarketplaceController::class, 'index'])->name('marketplace');
        Route::post('/marketplace/products', [CafeOwnerMarketplaceController::class, 'store'])->name('marketplace.products.store');
        Route::patch('/marketplace/products/{product}', [CafeOwnerMarketplaceController::class, 'updateProduct'])->name('marketplace.products.update');
        Route::patch('/marketplace/products/{product}/visibility', [CafeOwnerMarketplaceController::class, 'updateProductVisibility'])->name('marketplace.products.visibility');
        Route::patch('/marketplace/orders/{order}', [CafeOwnerMarketplaceController::class, 'updateOrder'])->name('marketplace.orders.update');
        Route::get('/recommendations', [CafeOwnerRecommendationsController::class, 'index'])->name('recommendations');
        Route::patch('/recommendations/reviews/{rating}/owner-response', [CafeOwnerRecommendationsController::class, 'updateOwnerResponse'])->name('recommendations.reviews.owner-response');
        Route::get('/map', [CafeOwnerMapController::class, 'index'])->name('map');
        Route::get('/messages', [CafeOwnerMessagesController::class, 'index'])->name('messages');
        Route::get('/messages/{conversation}', [CafeOwnerMessagesController::class, 'index'])->name('messages.show');
        Route::post('/messages', [CafeOwnerMessagesController::class, 'messagesStore'])->name('messages.store');
        Route::post('/messages/{conversation}/send', [CafeOwnerMessagesController::class, 'sendConversationMessage'])->name('messages.send');
    });

// Reseller routes (reseller only)
Route::prefix('reseller')
    ->name('reseller.')
    ->middleware(['auth', 'role:reseller', 'reseller.verified'])
    ->group(function () {
        Route::get('/dashboard', [ResellerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifications', [ResellerDashboardController::class, 'notifications'])->name('notifications');
        Route::get('/profile', 'App\\Http\\Controllers\\Reseller\\ResellerProfileController@index')->name('profile');
        Route::post('/profile', 'App\\Http\\Controllers\\Reseller\\ResellerProfileController@update')->name('profile');
        Route::get('/marketplace', 'App\\Http\\Controllers\\Reseller\\ResellerMarketplaceController@index')->name('marketplace');
        Route::post('/marketplace/products', 'App\\Http\\Controllers\\Reseller\\ResellerMarketplaceController@store')->name('marketplace.products.store');
        Route::patch('/marketplace/products/{product}', 'App\\Http\\Controllers\\Reseller\\ResellerMarketplaceController@updateProduct')->name('marketplace.products.update');
        Route::patch('/marketplace/products/{product}/visibility', 'App\\Http\\Controllers\\Reseller\\ResellerMarketplaceController@updateProductVisibility')->name('marketplace.products.visibility');
        Route::patch('/marketplace/orders/{order}', 'App\\Http\\Controllers\\Reseller\\ResellerMarketplaceController@updateOrder')->name('marketplace.orders.update');
        Route::get('/map', 'App\\Http\\Controllers\\Reseller\\ResellerMapController@index')->name('map');
        Route::get('/messages', 'App\\Http\\Controllers\\Reseller\\ResellerMessagesController@index')->name('messages');
        Route::get('/messages/{conversation}', 'App\\Http\\Controllers\\Reseller\\ResellerMessagesController@index')->name('messages.show');
        Route::post('/messages', 'App\\Http\\Controllers\\Reseller\\ResellerMessagesController@messagesStore')->name('messages.store');
        Route::post('/messages/{conversation}/send', 'App\\Http\\Controllers\\Reseller\\ResellerMessagesController@sendConversationMessage')->name('messages.send');
    });

// Farm owner routes (farm_owner only)
Route::prefix('farm-owner')
    ->name('farm-owner.')
    ->middleware(['auth', 'role:farm_owner'])
    ->group(function () {
        Route::get('/', [FarmOwnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/my-farm', [FarmOwnerController::class, 'myFarm'])->name('my-farm');
        Route::match(['POST', 'PATCH'], '/my-farm', [FarmOwnerController::class, 'updateMyFarm'])->name('my-farm.update');
        Route::get('/marketplace', [FarmOwnerController::class, 'marketplace'])->name('marketplace');
        Route::get('/notifications', [FarmOwnerController::class, 'notifications'])->name('notifications');
        Route::post('/marketplace/products', [FarmOwnerController::class, 'storeMarketplaceProduct'])->name('marketplace.products.store');
        Route::patch('/marketplace/products/{product}', [FarmOwnerController::class, 'updateMarketplaceProduct'])->name('marketplace.products.update');
        Route::patch('/marketplace/products/{product}/visibility', [FarmOwnerController::class, 'updateMarketplaceProductVisibility'])->name('marketplace.products.visibility');
        Route::patch('/marketplace/orders/{order}', [FarmOwnerController::class, 'updateMarketplaceOrder'])->name('marketplace.orders.update');
        Route::get('/map', [FarmOwnerController::class, 'map'])->name('map');
        Route::get('/messages', [FarmOwnerController::class, 'messages'])->name('messages');
        Route::get('/messages/{conversation}', [FarmOwnerController::class, 'messages'])->name('messages.show');
        Route::post('/messages', [FarmOwnerController::class, 'messagesStore'])->name('messages.store');
        Route::post('/messages/{conversation}/send', [FarmOwnerController::class, 'sendConversationMessage'])->name('messages.send');
    });

// Shared placeholder home for cafe_owner, reseller, and consumer
Route::middleware(['auth', 'role:cafe_owner,reseller,consumer'])->group(function () {
    Route::get('/home', function () {
        // ...placeholder home logic...
    })->name('home');
});

// Consumer routes (placeholder)
Route::middleware(['auth', 'role:consumer'])->group(function () {
    // Add consumer routes here
});
