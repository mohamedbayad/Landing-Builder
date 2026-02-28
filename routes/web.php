<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/debug-rec/{sessionId}', function ($sessionId) {
    $session = \App\Models\RecordingSession::where('session_id', $sessionId)->with('pages.events')->firstOrFail();
    $landingPage = $session->landingPage;
    return view('dashboard.recordings.show', compact('session', 'landingPage'));
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Who's Online
Route::get('/dashboard/online-users', [App\Http\Controllers\OnlineUsersController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('online-users.index');

Route::get('/api/online-users/stats', [App\Http\Controllers\OnlineUsersController::class, 'api'])
    ->middleware(['auth'])
    ->name('online-users.api');

Route::middleware('auth')->group(function () {
    Route::middleware('check.license')->group(function () {
        Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'index'])
        ->name('templates.index')
        ->middleware('license.realtime');
        Route::post('/templates/{id}/import', [App\Http\Controllers\TemplateController::class, 'import'])->name('templates.import');
    });

    Route::post('/templates/upload', [App\Http\Controllers\TemplateController::class, 'upload'])->name('templates.upload');

    Route::resource('landings', App\Http\Controllers\LandingController::class)->except(['create', 'store']);
    
    // Leads (Sales/Checkouts) - Admin
    Route::get('/leads/export', [App\Http\Controllers\LeadsController::class, 'export'])->name('leads.export');
    Route::post('/leads/bulk-update', [App\Http\Controllers\LeadsController::class, 'bulkUpdate'])->name('leads.bulk-update');
    Route::post('/leads/bulk-delete', [App\Http\Controllers\LeadsController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::get('/leads/{lead}/details', [App\Http\Controllers\LeadsController::class, 'show'])->name('leads.show');
    Route::resource('leads', App\Http\Controllers\LeadsController::class)->only(['index', 'update', 'destroy']);
    
    // Forms - Admin
    Route::get('/forms', [App\Http\Controllers\FormController::class, 'index'])->name('forms.index');
    Route::resource('form-endpoints', App\Http\Controllers\FormEndpointController::class)->only(['store', 'destroy']);
    
    // Session Recordings - Admin
    Route::prefix('dashboard')->group(function () {
        Route::get('/recordings/{landingPageId?}', [\App\Http\Controllers\RecordingDashboardController::class, 'index'])->name('recordings.index');
        Route::get('/recordings/{landingPageId}/{sessionId}', [\App\Http\Controllers\RecordingDashboardController::class, 'show'])->name('recordings.show');
        Route::delete('/recordings/{sessionId}', [\App\Http\Controllers\RecordingDashboardController::class, 'destroy'])->name('recordings.destroy');
    });

    // Custom Domains - Admin
    Route::prefix('dashboard/domains')->group(function () {
        Route::get('/', [\App\Http\Controllers\CustomDomainController::class, 'index'])->name('domains.index');
        Route::post('/', [\App\Http\Controllers\CustomDomainController::class, 'store'])->name('domains.store');
        Route::get('/{domain}', [\App\Http\Controllers\CustomDomainController::class, 'show'])->name('domains.show');
        Route::post('/{domain}/verify', [\App\Http\Controllers\CustomDomainController::class, 'verify'])->name('domains.verify');
        Route::post('/{domain}/assign', [\App\Http\Controllers\CustomDomainController::class, 'assign'])->name('domains.assign');
        Route::delete('/{domain}', [\App\Http\Controllers\CustomDomainController::class, 'destroy'])->name('domains.destroy');
    });

    // Analytics (New)
    Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [App\Http\Controllers\AnalyticsController::class, 'data'])->name('analytics.data');
    Route::get('/analytics/realtime', [App\Http\Controllers\AnalyticsController::class, 'realtime'])->name('analytics.realtime');
    Route::get('/analytics/clicks-breakdown', [App\Http\Controllers\AnalyticsController::class, 'clicksBreakdown'])->name('analytics.clicks-breakdown');
    
    // Tracking route moved to public section below
    // Legacy/Placeholder routes removal or update if needed
    // Route::post('/landings/{landing}/payment/stripe', ...)->name('payment.stripe.create'); // Old
    // Route::post('/landings/{landing}/payment/paypal', ...)->name('payment.paypal.create'); // Old

    // Global Settings
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');


    Route::get('/landings/{landing}/pages/{page}/edit', [App\Http\Controllers\LandingPageController::class, 'edit'])->name('landings.pages.edit');
    Route::put('/landings/{landing}/pages/{page}', [App\Http\Controllers\LandingPageController::class, 'update'])->name('landings.pages.update');
    Route::get('/landings/{landing}/editor', [App\Http\Controllers\LandingController::class, 'editor'])
        ->name('landings.editor')
        ->middleware('license.realtime'); 

    // Additional editor-related routes if any, e.g. saving
    Route::post('/landings/{landing}/save', [App\Http\Controllers\LandingController::class, 'save'])
        ->name('landings.save')
        ->middleware('license.realtime');
    Route::post('/landings/{landing}/main', [App\Http\Controllers\LandingController::class, 'setAsMain'])->name('landings.main');
    Route::post('/landings/{landing}/publish', [App\Http\Controllers\LandingController::class, 'publish'])
        ->name('landings.publish')
        ->middleware('license.realtime');
    
    // Funnel Management
    Route::get('/landings/{landing}/funnel', [App\Http\Controllers\FunnelController::class, 'show'])->name('landings.funnel');
    Route::post('/landings/{landing}/products', [App\Http\Controllers\FunnelController::class, 'storeProduct'])->name('funnel.products.store');
    Route::delete('/landings/{landing}/products/{product}', [App\Http\Controllers\FunnelController::class, 'deleteProduct'])->name('funnel.products.destroy');
    Route::post('/landings/{landing}/fields', [App\Http\Controllers\FunnelController::class, 'storeCheckoutFields'])->name('funnel.fields.store');

    Route::get('/preview/{landing}/{page}', [App\Http\Controllers\PublicLandingController::class, 'preview'])->name('landings.preview');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');




    // Landing Media Library
    Route::get('/landings/{landing}/media', [App\Http\Controllers\LandingMediaController::class, 'index'])->name('landings.media.index');
    Route::post('/landings/{landing}/media', [App\Http\Controllers\LandingMediaController::class, 'store'])
        ->name('landings.media.store')
        ->middleware('throttle:30,1'); // Limit uploads
    Route::delete('/landings/{landing}/media/{media}', [App\Http\Controllers\LandingMediaController::class, 'destroy'])->name('landings.media.destroy');

    // Dashboard Media Library (Unified)
    Route::get('/media', [App\Http\Controllers\MediaAssetController::class, 'index'])->name('media.index');
    Route::get('/api/media', [App\Http\Controllers\MediaAssetController::class, 'list'])->name('media.list');
    Route::post('/api/media', [App\Http\Controllers\MediaAssetController::class, 'store'])
        ->name('media.store')
        ->middleware('throttle:30,1'); // Limit uploads
    Route::delete('/api/media/{media}', [App\Http\Controllers\MediaAssetController::class, 'destroy'])->name('media.destroy');
});

require __DIR__.'/auth.php';

// Public Routes (Catch-all should be last if possible, but strict slug matching avoids issues)
    // Countdown Timer API
    Route::get('/l/{landing}/countdown', [App\Http\Controllers\CountdownController::class, 'show'])->name('landings.countdown');

    // Tracking (Public - Web middleware for Cookies)
    Route::post('/api/track/event', [App\Http\Controllers\AnalyticsTrackerController::class, 'trackEvent'])
        ->name('analytics.track')
        ->middleware('throttle:120,1'); // 120 per minute (high traffic)

    // CTA Click Tracking (Public - Dedicated endpoint)
    Route::post('/analytics/track-click', [App\Http\Controllers\AnalyticsController::class, 'trackClick'])
        ->name('analytics.track-click')
        ->middleware('throttle:120,1');

    // Route::post('/cart/sync', [App\Http\Controllers\CartController::class, 'sync'])->name('cart.sync');
Route::get('/landings/{landing}/checkout', [App\Http\Controllers\PublicLandingController::class, 'checkoutFlow'])->name('landings.checkout');
Route::get('/', [App\Http\Controllers\PublicLandingController::class, 'home'])->name('public.home');
Route::get('/{slug}', [App\Http\Controllers\PublicLandingController::class, 'page'])->where('slug', '^[a-zA-Z0-9-_]+$')->name('public.page');
Route::get('/{landingSlug}/{pageSlug}', [App\Http\Controllers\PublicLandingController::class, 'landingSubPage'])
    ->where('landingSlug', '^[a-zA-Z0-9-_]+$')
    ->where('pageSlug', '^[a-zA-Z0-9-_]+$')
    ->name('public.landing.page');


// Public Order & Form Processing
Route::post('/checkout/process', [App\Http\Controllers\LeadsController::class, 'processCheckout'])
    ->name('orders.store')
    ->middleware('throttle:20,1'); // 20 requests per minute

Route::post('/forms/process', [App\Http\Controllers\FormController::class, 'store'])
    ->name('forms.submit')
    ->middleware('throttle:20,1');

Route::post('/f/{uuid}', [App\Http\Controllers\FormEndpointController::class, 'submit'])
    ->name('forms.endpoint.submit')
    ->middleware('throttle:20,1');

// Payment Routes (Public API)
Route::post('/payment/paypal/create', [App\Http\Controllers\PaymentController::class, 'createPaypalOrder'])->name('payment.paypal.create');
Route::post('/payment/paypal/capture', [App\Http\Controllers\PaymentController::class, 'capturePaypalOrder'])->name('payment.paypal.capture');
Route::post('/payment/stripe/intent', [App\Http\Controllers\PaymentController::class, 'createStripePaymentIntent'])->name('payment.stripe.intent');
Route::get('/payment/stripe/return', [App\Http\Controllers\PaymentController::class, 'handleStripeReturn'])->name('payment.stripe.return');

// Protected Routes (ensure this is inside auth group in full file, but here just replacing the public block and adding resource if possible, or adding resource separately)
Route::get('/invoices/{lead}', [App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download')->middleware('signed');
