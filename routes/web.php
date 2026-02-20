<?php

use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/sys/migrate', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'MIGRATIONS_COMPLETE',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::match(['get', 'post'], '/sys/reset', function() {
    $email = 'helberfrancis@gmail.com';
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        $user->delete();
        return response()->json(['status' => 'CLEANUP_COMPLETE', 'email' => $email]);
    }
    return response()->json(['status' => 'NOT_FOUND', 'email' => $email]);
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!(/?api|/?storage|/?up|/?favicon|/?sys)).*$');
