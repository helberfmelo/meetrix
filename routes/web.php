<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('super-admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!(/?api|/?p/|/?storage|/?up|/?favicon)).*$');
