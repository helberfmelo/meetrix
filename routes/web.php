<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'superadmin'])->prefix('super-admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

Route::get('/deploy/maintenance', function () {
    $key = request('key');
    if (!$key || $key !== config('app.deploy_key', env('DEPLOY_KEY'))) {
        abort(403, 'Unauthorized');
    }

    $command = request('command', 'migrate');
    $output = [];

    try {
        if ($command === 'migrate') {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output[] = 'Migrated: ' . \Illuminate\Support\Facades\Artisan::output();
        } elseif ($command === 'seed') {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            $output[] = 'Seeded: ' . \Illuminate\Support\Facades\Artisan::output();
        } elseif ($command === 'optimize') {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $output[] = 'Optimized: ' . \Illuminate\Support\Facades\Artisan::output();
        } else {
            return response()->json(['error' => 'Invalid command'], 400);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }

    return response()->json(['status' => 'success', 'output' => $output]);
});
