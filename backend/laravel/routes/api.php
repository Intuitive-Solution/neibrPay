<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ResidentController;
use App\Http\Controllers\Api\UnitsController;
use App\Http\Controllers\Api\UnitDocumentController;
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

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    // Resident management routes
    Route::apiResource('residents', ResidentController::class);
    Route::get('residents/{resident}/units', [ResidentController::class, 'units']);
    Route::get('residents/{resident}/available-units', [ResidentController::class, 'availableUnits']);
    Route::delete('residents/{resident}/units/{unit}', [ResidentController::class, 'removeUnit']);
    Route::post('residents/{resident}/restore', [ResidentController::class, 'restore']);
    Route::delete('residents/{resident}/force', [ResidentController::class, 'forceDelete']);
    
    // Unit management routes
    Route::apiResource('units', UnitsController::class);
    Route::post('units/{unit}/restore', [UnitsController::class, 'restore']);
    Route::delete('units/{unit}/force', [UnitsController::class, 'forceDelete']);
    
    // Unit owner management routes
    Route::post('units/{unit}/owners', [UnitsController::class, 'addOwners']);
    Route::delete('units/{unit}/owners', [UnitsController::class, 'removeOwners']);
    Route::put('units/{unit}/owners', [UnitsController::class, 'syncOwners']);
    
    // Unit document management routes
    Route::get('units/{unit}/documents', [UnitDocumentController::class, 'index']);
    Route::post('units/{unit}/documents', [UnitDocumentController::class, 'store']);
    Route::get('units/{unit}/documents/{document}', [UnitDocumentController::class, 'show']);
    Route::get('units/{unit}/documents/{document}/download', [UnitDocumentController::class, 'download']);
    Route::delete('units/{unit}/documents/{document}', [UnitDocumentController::class, 'destroy']);
    Route::delete('units/{unit}/documents/{document}/force', [UnitDocumentController::class, 'forceDelete']);
});

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
