<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/health', [HealthController::class, 'check']);

Route::get('/status', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'Laravel API'
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello from Laravel API',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => app()->environment()
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    // Signup routes (no authentication required)
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/google-signup', [AuthController::class, 'googleSignup']);
    
    // Protected routes (require Firebase authentication)
    Route::middleware('firebase.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
