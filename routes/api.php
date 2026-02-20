<?php
Route::get('/diagnostic/user-check', function() {
    $user = \App\Models\User::where('email', 'admin@meetrix.pro')->first();
    return [
        'found' => !!$user,
        'email' => $user->email ?? null,
        'is_master' => $user->is_master_admin ?? false,
        'has_password' => $user ? !!$user->password : false,
        'time' => now()->toDateTimeString()
    ];
});

Route::get('/diagnostic/nuclear-reset', function() {
    $email = 'helberfrancis@gmail.com';
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        $user->delete();
        return ['status' => 'NUCLEAR_CLEANUP_COMPLETE', 'email' => $email];
    }
    return ['status' => 'NOT_FOUND', 'email' => $email];
});

Route::get('/diagnostic/user-fix', function() {
    $user = \App\Models\User::updateOrCreate(
        ['email' => 'admin@meetrix.pro'],
        [
            'name' => 'Master Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('MeetrixMaster2026Sovereign!#'),
            'is_master_admin' => true,
            'email_verified_at' => now(),
        ]
    );
    return [
        'success' => true,
        'email' => $user->email,
        'is_master' => $user->is_master_admin,
        'message' => 'Sovereign Node provisioned.'
    ];
});

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    Route::post('/coupons/validate', [\App\Http\Controllers\CouponController::class, 'validateCoupon']);

    // Admin / Coupons
    Route::apiResource('coupons', \App\Http\Controllers\CouponController::class);
});

// Public Routes
Route::get('/p/{slug}', [\App\Http\Controllers\PageController::class, 'show']); // Public Page View
Route::post('/p/{slug}/click', [\App\Http\Controllers\PageController::class, 'recordClick']); // Track Click
Route::get('/p/{slug}/slots', [\App\Http\Controllers\BookingSlotController::class, 'index']); // Get Slots
Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store']); // Create Booking

// Meeting Polls (Public)
Route::get('/p/polls/{slug}', [\App\Http\Controllers\PollController::class, 'show']);
Route::post('/poll-options/{option}/vote', [\App\Http\Controllers\PollController::class, 'vote']);

// Webhooks
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle']);
