<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Page Management (Admin)
    Route::apiResource('pages', \App\Http\Controllers\PageController::class);
    
    // Booking Management (Admin)
    Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index']);
});

// Public Routes
Route::get('/p/{slug}', [\App\Http\Controllers\PageController::class, 'show']); // Public Page View
Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store']); // Create Booking
