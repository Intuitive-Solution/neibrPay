<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceAttachmentController;
use App\Http\Controllers\Api\InvoiceController;
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
    Route::post('residents/{resident}/units', [ResidentController::class, 'addUnits']);
    Route::delete('residents/{resident}/units/{unit}', [ResidentController::class, 'removeUnit']);
    Route::post('residents/{resident}/restore', [ResidentController::class, 'restore']);
    Route::delete('residents/{resident}/force', [ResidentController::class, 'forceDelete']);
    
    // Unit management routes
    Route::get('units/for-invoices', [UnitsController::class, 'forInvoices']);
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
    
    // Invoice management routes
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{id}/restore', [InvoiceController::class, 'restore']);
    Route::delete('invoices/{id}/force', [InvoiceController::class, 'forceDelete']);
    Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markAsSent']);
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid']);
    Route::post('invoices/{invoice}/clone', [InvoiceController::class, 'clone']);
    Route::post('invoices/{invoice}/email', [InvoiceController::class, 'email']);
    Route::get('units/{unit}/invoices', [InvoiceController::class, 'forUnit']);
    
    // Invoice attachment management routes
    Route::get('invoices/{invoice}/attachments', [InvoiceAttachmentController::class, 'index']);
    Route::post('invoices/{invoice}/attachments', [InvoiceAttachmentController::class, 'store']);
    Route::get('invoices/{invoice}/attachments/{attachment}', [InvoiceAttachmentController::class, 'show']);
    Route::get('invoices/{invoice}/attachments/{attachment}/download', [InvoiceAttachmentController::class, 'download']);
    Route::delete('invoices/{invoice}/attachments/{attachment}', [InvoiceAttachmentController::class, 'destroy']);
});

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
