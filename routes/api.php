<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SuperAdmin\SaasAdminController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Page Management (Admin)
    Route::apiResource('pages', \App\Http\Controllers\PageController::class);
    
    // Page Sub-resources
    Route::prefix('pages/{page}')->group(function () {
        Route::get('/availability', [\App\Http\Controllers\AvailabilityRuleController::class, 'index']);
        Route::post('/availability', [\App\Http\Controllers\AvailabilityRuleController::class, 'store']);
        Route::put('/availability/bulk', [\App\Http\Controllers\AvailabilityRuleController::class, 'bulkUpdate']); // New Bulk Route
        Route::put('/availability/{rule}', [\App\Http\Controllers\AvailabilityRuleController::class, 'update']);
        Route::delete('/availability/{rule}', [\App\Http\Controllers\AvailabilityRuleController::class, 'destroy']);

        Route::get('/appointment-types', [\App\Http\Controllers\AppointmentTypeController::class, 'index']);
        Route::post('/appointment-types', [\App\Http\Controllers\AppointmentTypeController::class, 'store']);
        Route::put('/appointment-types/bulk', [\App\Http\Controllers\AppointmentTypeController::class, 'bulkUpdate']); // New Bulk Route
        Route::put('/appointment-types/{type}', [\App\Http\Controllers\AppointmentTypeController::class, 'update']);
        Route::delete('/appointment-types/{type}', [\App\Http\Controllers\AppointmentTypeController::class, 'destroy']);
    });

    // Booking Management (Admin)
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index']);

    // Team Management
    Route::apiResource('teams', \App\Http\Controllers\TeamController::class);
    Route::post('teams/{team}/invite', [\App\Http\Controllers\TeamController::class, 'invite']);

    // Integration Management
    Route::get('/integrations', [\App\Http\Controllers\IntegrationController::class, 'index']);
    Route::get('/integrations/google/redirect', [\App\Http\Controllers\IntegrationController::class, 'googleRedirect']);
    Route::post('/integrations/google/callback', [\App\Http\Controllers\IntegrationController::class, 'googleCallback']);
    Route::delete('/integrations/{integration}', [\App\Http\Controllers\IntegrationController::class, 'destroy']);

    // Meeting Polls (Admin)
    Route::apiResource('polls', \App\Http\Controllers\PollController::class);

    // Onboarding
    Route::post('/onboarding/complete', [\App\Http\Controllers\OnboardingController::class, 'complete']);

    // Subscriptions & Billing
    Route::post('/subscription/checkout', [\App\Http\Controllers\SubscriptionController::class, 'checkout']);
    Route::post('/coupons/validate-code', [\App\Http\Controllers\CouponController::class, 'validateCoupon']);

    // Admin / Coupons
    Route::apiResource('coupons', \App\Http\Controllers\CouponController::class);

    // Account Area
    Route::get('/account/summary', [AccountController::class, 'summary']);
    Route::put('/account/profile', [AccountController::class, 'updateProfile']);
    Route::put('/account/preferences', [AccountController::class, 'updatePreferences']);
    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::patch('/account/mode', [AccountController::class, 'updateMode']);
    Route::get('/account/billing-history', [AccountController::class, 'billingHistory']);
});

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('super-admin')->group(function () {
    Route::get('/overview', [SaasAdminController::class, 'overview']);
    Route::get('/customers', [SaasAdminController::class, 'customers']);
    Route::get('/customers/{user}', [SaasAdminController::class, 'showCustomer']);
    Route::post('/customers/{user}/actions', [SaasAdminController::class, 'performAction']);
    Route::get('/activity', [SaasAdminController::class, 'activity']);
    Route::get('/payments', [SaasAdminController::class, 'payments']);
    Route::get('/coupons', [SaasAdminController::class, 'coupons']);
    Route::get('/mail/diagnostics', [SaasAdminController::class, 'mailDiagnostics']);
    Route::post('/mail/test', [SaasAdminController::class, 'sendTestEmail']);
});

// Public Routes
Route::get('/pricing/catalog', [\App\Http\Controllers\PricingCatalogController::class, 'index']);
Route::get('/p/{slug}', [\App\Http\Controllers\PageController::class, 'show']); // Public Page View
Route::post('/p/{slug}/click', [\App\Http\Controllers\PageController::class, 'recordClick']); // Track Click
Route::get('/p/{slug}/slots', [\App\Http\Controllers\BookingSlotController::class, 'index']); // Get Slots
Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store']); // Create Booking

// Meeting Polls (Public)
Route::get('/p/polls/{slug}', [\App\Http\Controllers\PollController::class, 'show']);
Route::post('/poll-options/{option}/vote', [\App\Http\Controllers\PollController::class, 'vote']);

// Webhooks
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle']);
