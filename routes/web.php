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
    Route::post('/dashboard/assistant/chat', [App\Http\Controllers\DashboardAssistantController::class, 'chat'])
        ->name('dashboard.assistant.chat')
        ->middleware('throttle:60,1');

    Route::prefix('landing-builder/templates')->name('templates.')->group(function () {
        Route::get('/', [App\Http\Controllers\TemplateController::class, 'index'])
            ->name('index')
            ->middleware(['permission:templates.view']);
        Route::get('/my', [App\Http\Controllers\TemplateController::class, 'myTemplates'])
            ->name('my')
            ->middleware(['permission:templates.view', 'permission:tech.manage']);
        Route::get('/create', [App\Http\Controllers\TemplateController::class, 'create'])
            ->name('create')
            ->middleware(['permission:templates.create', 'permission:tech.manage']);
        Route::post('/', [App\Http\Controllers\TemplateController::class, 'store'])
            ->name('store')
            ->middleware(['permission:templates.upload', 'permission:tech.manage']);
        Route::post('/upload', [App\Http\Controllers\TemplateController::class, 'upload'])
            ->name('upload')
            ->middleware(['permission:templates.upload', 'permission:tech.manage']);
        Route::get('/{template}/edit', [App\Http\Controllers\TemplateController::class, 'edit'])
            ->name('edit')
            ->middleware(['permission:templates.edit', 'permission:tech.manage']);
        Route::put('/{template}', [App\Http\Controllers\TemplateController::class, 'update'])
            ->name('update')
            ->middleware(['permission:templates.edit', 'permission:tech.manage']);
        Route::patch('/{template}/toggle-status', [App\Http\Controllers\TemplateController::class, 'toggleStatus'])
            ->name('toggle-status')
            ->middleware(['permission:templates.publish', 'permission:tech.manage']);
        Route::post('/{id}/import', [App\Http\Controllers\TemplateController::class, 'import'])
            ->name('import')
            ->middleware(['subscription.active', 'permission:builder.import']);
    });

    Route::get('/templates', fn () => redirect()->route('templates.index'))->name('templates.legacy.index');

    Route::prefix('users')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:users.view');
        Route::get('/create', [App\Http\Controllers\Admin\UserManagementController::class, 'create'])
            ->name('users.create')
            ->middleware('permission:users.create');
        Route::post('/', [App\Http\Controllers\Admin\UserManagementController::class, 'store'])
            ->name('users.store')
            ->middleware('permission:users.create');
        Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserManagementController::class, 'edit'])
            ->name('users.edit')
            ->middleware('permission:users.edit');
        Route::put('/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'update'])
            ->name('users.update')
            ->middleware('permission:users.edit');
    });

    Route::prefix('roles-permissions')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RolePermissionController::class, 'index'])
            ->name('roles-permissions.index')
            ->middleware('permission:roles.view');
        Route::put('/{role}', [App\Http\Controllers\Admin\RolePermissionController::class, 'update'])
            ->name('roles-permissions.update')
            ->middleware('permission:roles.manage');
    });

    Route::prefix('plans')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PlanController::class, 'index'])
            ->name('plans.index')
            ->middleware('permission:plans.view');
        Route::post('/', [App\Http\Controllers\Admin\PlanController::class, 'store'])
            ->name('plans.store')
            ->middleware('permission:plans.create');
        Route::put('/{plan}', [App\Http\Controllers\Admin\PlanController::class, 'update'])
            ->name('plans.update')
            ->middleware('permission:plans.edit');
    });

    Route::prefix('subscriptions')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SubscriptionController::class, 'index'])
            ->name('subscriptions.index')
            ->middleware('permission:subscriptions.view');
        Route::get('/invoices', [App\Http\Controllers\Admin\SubscriptionController::class, 'invoices'])
            ->name('subscriptions.invoices.index')
            ->middleware('permission:subscriptions.view');
        Route::get('/{subscription}/invoice', [App\Http\Controllers\Admin\SubscriptionController::class, 'downloadInvoice'])
            ->name('subscriptions.invoices.download')
            ->middleware('permission:subscriptions.view');
        Route::post('/', [App\Http\Controllers\Admin\SubscriptionController::class, 'store'])
            ->name('subscriptions.store')
            ->middleware('permission:subscriptions.create');
        Route::put('/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'update'])
            ->name('subscriptions.update')
            ->middleware('permission:subscriptions.edit');
    });

    Route::resource('landings', App\Http\Controllers\LandingController::class)
        ->except(['create', 'store'])
        ->middleware(['subscription.active', 'permission:landing_pages.view']);
    Route::post('/landings/{landing}/sync-template', [App\Http\Controllers\TemplateController::class, 'syncLandingTemplate'])
        ->name('landings.templates.sync')
        ->middleware(['subscription.active', 'permission:templates.edit']);
    
    // Leads (Sales/Checkouts) - Admin
    Route::get('/leads/export', [App\Http\Controllers\LeadsController::class, 'export'])->name('leads.export');
    Route::post('/leads/bulk-update', [App\Http\Controllers\LeadsController::class, 'bulkUpdate'])->name('leads.bulk-update');
    Route::post('/leads/bulk-delete', [App\Http\Controllers\LeadsController::class, 'bulkDelete'])->name('leads.bulk-delete');
    Route::get('/leads/{lead}/details', [App\Http\Controllers\LeadsController::class, 'show'])->name('leads.show');
    Route::resource('leads', App\Http\Controllers\LeadsController::class)->only(['index', 'update', 'destroy']);
    
    // Forms - Admin
    Route::get('/forms', [App\Http\Controllers\FormController::class, 'index'])->name('forms.index');
    Route::resource('form-endpoints', App\Http\Controllers\FormEndpointController::class)->only(['store', 'update', 'destroy']);

    // Email Automation Module
    Route::prefix('email-automation')->name('email-automation.')->group(function () {
        Route::get('/', fn () => redirect()->route('email-automation.automations.index'))->name('index');

        Route::get('/automations', [App\Http\Controllers\EmailAutomationController::class, 'index'])->name('automations.index');
        Route::get('/automations/create', [App\Http\Controllers\EmailAutomationController::class, 'create'])->name('automations.create');
        Route::post('/automations', [App\Http\Controllers\EmailAutomationController::class, 'store'])->name('automations.store');
        Route::get('/automations/{automation}/edit', [App\Http\Controllers\EmailAutomationController::class, 'edit'])->name('automations.edit');
        Route::put('/automations/{automation}', [App\Http\Controllers\EmailAutomationController::class, 'update'])->name('automations.update');
        Route::delete('/automations/{automation}', [App\Http\Controllers\EmailAutomationController::class, 'destroy'])->name('automations.destroy');
        Route::post('/automations/{automation}/duplicate', [App\Http\Controllers\EmailAutomationController::class, 'duplicate'])->name('automations.duplicate');
        Route::patch('/automations/{automation}/status', [App\Http\Controllers\EmailAutomationController::class, 'updateStatus'])->name('automations.status');

        Route::get('/templates', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [App\Http\Controllers\EmailTemplateController::class, 'create'])->name('templates.create');
        Route::post('/templates', [App\Http\Controllers\EmailTemplateController::class, 'store'])->name('templates.store');
        Route::post('/templates/generate-body', [App\Http\Controllers\EmailTemplateController::class, 'generateBody'])->name('templates.generate-body');
        Route::get('/templates/{template}/edit', [App\Http\Controllers\EmailTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('/templates/{template}', [App\Http\Controllers\EmailTemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [App\Http\Controllers\EmailTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('/templates/{template}/duplicate', [App\Http\Controllers\EmailTemplateController::class, 'duplicate'])->name('templates.duplicate');
        Route::post('/templates/{template}/send-test', [App\Http\Controllers\EmailTemplateController::class, 'sendTest'])->name('templates.send-test');

        Route::get('/contacts', [App\Http\Controllers\EmailContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contact}', [App\Http\Controllers\EmailContactController::class, 'show'])->name('contacts.show');
        Route::patch('/contacts/{contact}/status', [App\Http\Controllers\EmailContactController::class, 'updateStatus'])->name('contacts.status');

        Route::get('/activity', [App\Http\Controllers\EmailActivityController::class, 'index'])->name('activity.index');
        Route::get('/analytics', [App\Http\Controllers\EmailAnalyticsController::class, 'index'])->name('analytics.index');

        Route::get('/settings', [App\Http\Controllers\EmailSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [App\Http\Controllers\EmailSettingsController::class, 'update'])->name('settings.update');
    });
    
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
    Route::post('/settings/ai/test-connection', [App\Http\Controllers\API\AISettingsController::class, 'testConnection'])->name('settings.ai.test-connection')->middleware('permission:tech.manage');
    Route::get('/settings/plugins', [App\Http\Controllers\PluginController::class, 'dashboard'])->name('settings.plugins.index')->middleware('permission:tech.manage');
    Route::post('/settings/plugins/install', [App\Http\Controllers\PluginController::class, 'installFromDashboard'])->name('settings.plugins.install')->middleware('permission:tech.manage');
    Route::post('/settings/plugins/{plugin:slug}/activate', [App\Http\Controllers\PluginController::class, 'activateFromDashboard'])->name('settings.plugins.activate')->middleware('permission:tech.manage');
    Route::post('/settings/plugins/{plugin:slug}/deactivate', [App\Http\Controllers\PluginController::class, 'deactivateFromDashboard'])->name('settings.plugins.deactivate')->middleware('permission:tech.manage');
    Route::put('/settings/plugins/{plugin:slug}/settings', [App\Http\Controllers\PluginController::class, 'updateSettingsFromDashboard'])->name('settings.plugins.settings.update')->middleware('permission:tech.manage');

    // Plugin System (PRD Phase 1 foundation)
    Route::prefix('plugins')->name('plugins.')->group(function () {
        Route::get('/', [App\Http\Controllers\PluginController::class, 'index'])->name('index')->middleware('permission:tech.manage');
        Route::post('/install', [App\Http\Controllers\PluginController::class, 'install'])->name('install')->middleware('permission:tech.manage');
        Route::post('/{plugin:slug}/activate', [App\Http\Controllers\PluginController::class, 'activate'])->name('activate')->middleware('permission:tech.manage');
        Route::post('/{plugin:slug}/deactivate', [App\Http\Controllers\PluginController::class, 'deactivate'])->name('deactivate')->middleware('permission:tech.manage');
        Route::put('/{plugin:slug}/settings', [App\Http\Controllers\PluginController::class, 'updateSettings'])->name('settings.update')->middleware('permission:tech.manage');
        Route::delete('/{plugin:slug}', [App\Http\Controllers\PluginController::class, 'destroy'])->name('destroy')->middleware('permission:tech.manage');
        Route::post('/hooks/dispatch', [App\Http\Controllers\PluginController::class, 'dispatchHook'])->name('hooks.dispatch')->middleware('permission:tech.manage');
    });

    // Advanced Role-Based AI Configuration
    Route::prefix('settings/ai')->name('settings.ai.')->group(function () {
        Route::post('/providers', [App\Http\Controllers\Settings\AiConfigurationController::class, 'storeProvider'])->name('providers.store')->middleware('permission:tech.manage');
        Route::delete('/providers/{provider}', [App\Http\Controllers\Settings\AiConfigurationController::class, 'destroyProvider'])->name('providers.destroy')->middleware('permission:tech.manage');
        Route::post('/providers/{provider}/load-models', [App\Http\Controllers\Settings\AiConfigurationController::class, 'loadModels'])->name('providers.load-models')->middleware('permission:tech.manage');
        Route::post('/models/roles', [App\Http\Controllers\Settings\AiConfigurationController::class, 'updateModelRoles'])->name('models.roles.update')->middleware('permission:tech.manage');
    });


    Route::get('/landings/{landing}/pages/{page}/edit', [App\Http\Controllers\LandingPageController::class, 'edit'])
        ->name('landings.pages.edit')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::put('/landings/{landing}/pages/{page}', [App\Http\Controllers\LandingPageController::class, 'update'])
        ->name('landings.pages.update')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::post('/landings/{landing}/pages/{page}/duplicate', [App\Http\Controllers\LandingPageController::class, 'duplicate'])
        ->name('landings.pages.duplicate')
        ->middleware(['subscription.active', 'permission:landing_pages.create']);
    Route::get('/landings/{landing}/editor', [App\Http\Controllers\LandingController::class, 'editor'])
        ->name('landings.editor')
        ->middleware(['subscription.active', 'permission:builder.access']);

    // Editor AI Actions (used by GrapesJS ai-assistant plugin)
    Route::prefix('editor/ai')->name('editor.ai.')->group(function () {
        Route::post('/improve-copy', [App\Http\Controllers\EditorAIController::class, 'improveCopy'])->name('improve-copy')->middleware('permission:tech.manage');
        Route::post('/generate-image', [App\Http\Controllers\EditorAIController::class, 'generateImage'])->name('generate-image')->middleware('permission:tech.manage');
        Route::post('/suggest-section', [App\Http\Controllers\EditorAIController::class, 'suggestSection'])->name('suggest-section')->middleware('permission:tech.manage');
    });

    // Additional editor-related routes if any, e.g. saving
    Route::post('/landings/{landing}/save', [App\Http\Controllers\LandingController::class, 'save'])
        ->name('landings.save')
        ->middleware(['subscription.active', 'permission:builder.access']);
    Route::post('/landings/{landing}/main', [App\Http\Controllers\LandingController::class, 'setAsMain'])->name('landings.main');
    Route::post('/landings/{landing}/publish', [App\Http\Controllers\LandingController::class, 'publish'])
        ->name('landings.publish')
        ->middleware(['subscription.active', 'permission:landing_pages.publish']);
    Route::post('/landings/{landing}/unpublish', [App\Http\Controllers\LandingController::class, 'unpublish'])
        ->name('landings.unpublish');
    
    // Funnel Management
    Route::get('/landings/{landing}/funnel', [App\Http\Controllers\FunnelController::class, 'show'])
        ->name('landings.funnel')
        ->middleware(['subscription.active', 'permission:landing_pages.view']);
    Route::post('/landings/{landing}/steps', [App\Http\Controllers\FunnelController::class, 'storeStep'])
        ->name('funnel.steps.store')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::put('/landings/{landing}/steps', [App\Http\Controllers\FunnelController::class, 'updateSteps'])
        ->name('funnel.steps.update')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::post('/landings/{landing}/products', [App\Http\Controllers\FunnelController::class, 'storeProduct'])
        ->name('funnel.products.store')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::delete('/landings/{landing}/products/{product}', [App\Http\Controllers\FunnelController::class, 'deleteProduct'])
        ->name('funnel.products.destroy')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);
    Route::post('/landings/{landing}/fields', [App\Http\Controllers\FunnelController::class, 'storeCheckoutFields'])
        ->name('funnel.fields.store')
        ->middleware(['subscription.active', 'permission:landing_pages.edit']);

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

    // AI Landing Page Generator
    Route::get('/ai-generator', [\App\Http\Controllers\AILandingPageController::class, 'index'])->name('ai-generator.index')->middleware('permission:tech.manage');
    Route::post('/ai-generator/generate', [\App\Http\Controllers\AILandingPageController::class, 'generate'])->name('ai-generator.generate')->middleware('permission:tech.manage');
    Route::post('/ai-generator/publish', [\App\Http\Controllers\AILandingPageController::class, 'publish'])->name('ai-generator.publish')->middleware('permission:tech.manage');
    Route::post('/ai-generator/regenerate', [\App\Http\Controllers\AILandingPageController::class, 'regenerate'])->name('ai-generator.regenerate')->middleware('permission:tech.manage');
    Route::post('/ai-generator/regenerate-element', [\App\Http\Controllers\AILandingPageController::class, 'regenerateElement'])->name('ai-generator.regenerate-element')->middleware('permission:tech.manage');

    // Explicit action: a landing can be converted into a template only via this endpoint
    Route::post('/landings/{landing}/save-as-template', [App\Http\Controllers\LandingController::class, 'saveAsTemplate'])->name('landings.save-as-template')->middleware(['permission:templates.create', 'permission:tech.manage']);
});

require __DIR__.'/auth.php';

// Public Routes (Catch-all should be last if possible, but strict slug matching avoids issues)
    Route::get('/template-asset-proxy', [App\Http\Controllers\TemplateController::class, 'proxyTemplateAsset'])
        ->name('templates.asset-proxy');

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

// Public AI Chatbot API
Route::post('/api/public/ai-chat', [App\Http\Controllers\PublicAIChatController::class, 'ask'])
    ->name('public.ai.chat')
    ->middleware('throttle:30,1');

// Public Order & Form Processing
Route::post('/checkout/process', [App\Http\Controllers\LeadsController::class, 'processCheckout'])
    ->name('orders.store')
    ->middleware('throttle:20,1'); // 20 requests per minute

Route::post('/forms/process', [App\Http\Controllers\FormController::class, 'store'])
    ->name('forms.submit')
    ->middleware('throttle:20,1');

Route::match(['GET', 'POST'], '/f/{uuid}', [App\Http\Controllers\FormEndpointController::class, 'submit'])
    ->name('forms.endpoint.submit')
    ->middleware('throttle:20,1');

    // Route::post('/cart/sync', [App\Http\Controllers\CartController::class, 'sync'])->name('cart.sync');
Route::get('/landings/{landing}/checkout', [App\Http\Controllers\PublicLandingController::class, 'checkoutFlow'])->name('landings.checkout');
// Payment Routes (Public API)
Route::post('/payment/paypal/create', [App\Http\Controllers\PaymentController::class, 'createPaypalOrder'])->name('payment.paypal.create');
Route::post('/payment/paypal/capture', [App\Http\Controllers\PaymentController::class, 'capturePaypalOrder'])->name('payment.paypal.capture');
Route::post('/payment/stripe/intent', [App\Http\Controllers\PaymentController::class, 'createStripePaymentIntent'])->name('payment.stripe.intent');
Route::get('/payment/stripe/return', [App\Http\Controllers\PaymentController::class, 'handleStripeReturn'])->name('payment.stripe.return');

// Email Tracking / Unsubscribe (Public)
Route::get('/email/track/open/{message}/{hash}', [App\Http\Controllers\EmailTrackingController::class, 'open'])->name('email.track.open');
Route::get('/email/click/{code}', [App\Http\Controllers\EmailTrackingController::class, 'click'])->name('email.click');
Route::get('/email/unsubscribe/{contact}', [App\Http\Controllers\EmailTrackingController::class, 'unsubscribe'])
    ->name('email.unsubscribe')
    ->middleware('signed');

// Protected Routes (ensure this is inside auth group in full file, but here just replacing the public block and adding resource if possible, or adding resource separately)
Route::get('/invoices/{lead}', [App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download')->middleware('signed');

// Catch-all public landing routes (must stay last)
Route::get('/', [App\Http\Controllers\PublicLandingController::class, 'home'])->name('public.home');
Route::get('/{slug}', [App\Http\Controllers\PublicLandingController::class, 'page'])->where('slug', '^[a-zA-Z0-9-_]+$')->name('public.page');
Route::get('/{landingSlug}/{pageSlug}', [App\Http\Controllers\PublicLandingController::class, 'landingSubPage'])
    ->where('landingSlug', '^[a-zA-Z0-9-_]+$')
    ->where('pageSlug', '^[a-zA-Z0-9-_]+$')
    ->name('public.landing.page');
