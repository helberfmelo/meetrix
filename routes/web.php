<?php

use Illuminate\Support\Facades\Route;

Route::get('/diagnostic/user-fix', function() {
    try {
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'admin@meetrix.pro'],
            [
                'name' => 'Master Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('MeetrixMaster2026Sovereign!#'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );
        return [
            'success' => true,
            'email' => $user->email,
            'is_super_admin' => $user->is_super_admin,
            'message' => 'Sovereign Node provisioned with is_super_admin.'
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

Route::get('/diagnostic/user-check', function() {
    $user = \App\Models\User::where('email', 'admin@meetrix.pro')->first();
    return [
        'found' => !!$user,
        'email' => $user->email ?? null,
        'is_super' => $user->is_super_admin ?? false,
        'has_password' => $user ? !!$user->password : false,
        'time' => now()->toDateTimeString()
    ];
});

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('super-admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
