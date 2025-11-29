<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChargeController;
use App\Http\Controllers\Api\ExpenseAttachmentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InvoiceAttachmentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoicePaymentController;
use App\Http\Controllers\Api\InvoicePdfController;
use App\Http\Controllers\Api\PlaidController;
use App\Http\Controllers\Api\StripePaymentController;
use App\Http\Controllers\Api\StripeConnectController;
use App\Http\Controllers\Api\ResidentController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UnitsController;
use App\Http\Controllers\Api\UnitDocumentController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\VendorController;
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

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    // Email-based authentication
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/send-code', [AuthController::class, 'sendCode']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/magic-link', [AuthController::class, 'magicLinkAuth']);
    
    // Google OAuth (requires sessions for OAuth state)
    Route::middleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ])->group(function () {
        Route::get('/google/redirect', [AuthController::class, 'redirectToGoogle']);
        Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    });
    Route::post('/google/signup', [AuthController::class, 'googleSignup']);
    
    // Protected routes (require Sanctum authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Protected routes (require Sanctum authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Resident management routes
    Route::apiResource('residents', ResidentController::class);
    Route::get('residents/{resident}/units', [ResidentController::class, 'units']);
    Route::get('residents/{resident}/available-units', [ResidentController::class, 'availableUnits']);
    Route::post('residents/{resident}/units', [ResidentController::class, 'addUnits']);
    Route::put('residents/{resident}/units/{unit}/type', [ResidentController::class, 'updateUnitType']);
    Route::delete('residents/{resident}/units/{unit}', [ResidentController::class, 'removeUnit']);
    Route::post('residents/{resident}/restore', [ResidentController::class, 'restore']);
    Route::delete('residents/{resident}/force', [ResidentController::class, 'forceDelete']);
    Route::post('residents/{resident}/send-invite', [ResidentController::class, 'sendInvite']);
    
    // Unit management routes
    Route::get('units/for-invoices', [UnitsController::class, 'forInvoices']);
    Route::apiResource('units', UnitsController::class);
    Route::post('units/{unit}/restore', [UnitsController::class, 'restore']);
    Route::delete('units/{unit}/force', [UnitsController::class, 'forceDelete']);
    
    // Unit owner management routes
    Route::post('units/{unit}/owners', [UnitsController::class, 'addOwners']);
    Route::put('units/{unit}/owners/{owner}/type', [UnitsController::class, 'updateOwnerType']);
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
    Route::get('invoices', [InvoiceController::class, 'index']);
    Route::post('invoices', [InvoiceController::class, 'store']);
    Route::get('invoices/{id}', [InvoiceController::class, 'show']); // Custom route to handle deleted invoices
    Route::put('invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy']);
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
    
    // Invoice PDF management routes
    Route::post('invoices/{invoice}/pdf/generate', [InvoicePdfController::class, 'generate']);
    Route::get('invoices/{invoice}/pdf', [InvoicePdfController::class, 'view']);
    Route::get('invoices/{invoice}/pdf/info', [InvoicePdfController::class, 'latest']);
    Route::get('invoices/{invoice}/pdf/download', [InvoicePdfController::class, 'download']);
    Route::get('invoices/{invoice}/pdf/versions', [InvoicePdfController::class, 'versions']);
    Route::get('invoices/{invoice}/pdf/versions/{version}/download', [InvoicePdfController::class, 'downloadVersion']);
    
    // Invoice payment management routes
    Route::get('payments', [InvoicePaymentController::class, 'index']);
    Route::post('invoices/{id}/payments', [InvoicePaymentController::class, 'store']);
    Route::get('payments/{id}', [InvoicePaymentController::class, 'show']);
    Route::put('payments/{id}', [InvoicePaymentController::class, 'update']);
    Route::delete('payments/{id}', [InvoicePaymentController::class, 'destroy']);
    
    // Invoice payment review routes (admin approval workflow)
    Route::post('payments/{id}/approve', [InvoicePaymentController::class, 'approve']);
    Route::post('payments/{id}/reject', [InvoicePaymentController::class, 'reject']);
    Route::post('payments/{id}/resubmit', [InvoicePaymentController::class, 'resubmit']);
    
    // Stripe payment routes
    Route::post('invoices/{id}/stripe/checkout', [StripePaymentController::class, 'createCheckoutSession']);
    Route::get('invoices/{id}/stripe/status', [StripePaymentController::class, 'getPaymentStatus']);
    
    // Stripe Connect routes (admin only)
    Route::post('stripe/connect', [StripeConnectController::class, 'connect']);
    Route::post('stripe/dashboard', [StripeConnectController::class, 'dashboard']);
    Route::post('stripe/verify', [StripeConnectController::class, 'verify']);
    Route::post('stripe/disconnect', [StripeConnectController::class, 'disconnect']);
    
    // Charge management routes
    Route::apiResource('charges', ChargeController::class);
    Route::post('charges/{charge}/restore', [ChargeController::class, 'restore']);
    
    // Vendor management routes
    Route::apiResource('vendors', VendorController::class);
    Route::post('vendors/{vendor}/restore', [VendorController::class, 'restore']);
    
    // Expense management routes
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('expenses/{expense}/restore', [ExpenseController::class, 'restore']);
    
    // Expense attachment routes
    Route::get('expenses/{expense}/attachments', [ExpenseAttachmentController::class, 'index']);
    Route::post('expenses/{expense}/attachments', [ExpenseAttachmentController::class, 'store']);
    Route::get('expenses/{expense}/attachments/{attachment}/download', [ExpenseAttachmentController::class, 'download']);
    Route::delete('expenses/{expense}/attachments/{attachment}', [ExpenseAttachmentController::class, 'destroy']);
    
    // HOA Document management routes
    Route::get('documents', [DocumentController::class, 'index']);
    Route::post('documents', [DocumentController::class, 'store']);
    Route::get('documents/{document}', [DocumentController::class, 'show']);
    Route::put('documents/{document}', [DocumentController::class, 'update']);
    Route::get('documents/{document}/download', [DocumentController::class, 'download']);
    Route::delete('documents/{document}', [DocumentController::class, 'destroy']);
    Route::delete('documents/{document}/force', [DocumentController::class, 'forceDelete']);
    
    // Settings routes
    Route::get('settings', [SettingsController::class, 'index']);
    
    // Tenant routes
    Route::put('tenant', [TenantController::class, 'update']);
    Route::put('tenant/localization', [TenantController::class, 'updateLocalization']);
    
    // Announcement routes
    Route::get('announcements/for-user', [AnnouncementController::class, 'forUser']);
    Route::apiResource('announcements', AnnouncementController::class);

    // Plaid bank integration routes
    Route::prefix('plaid')->group(function () {
        Route::get('/debug', [PlaidController::class, 'debug']);
        Route::post('/link-token', [PlaidController::class, 'createLinkToken']);
        Route::post('/exchange-token', [PlaidController::class, 'exchangeToken']);
        Route::get('/bank-accounts', [PlaidController::class, 'getBankAccounts']);
        Route::delete('/bank-accounts/{id}', [PlaidController::class, 'disconnectBankAccount']);
        Route::get('/transactions', [PlaidController::class, 'getTransactions']);
        Route::post('/sync', [PlaidController::class, 'syncAccount']);
    });
});

// Plaid sync-all route (public, secured with API key for n8n)
Route::post('/plaid/sync-all', [PlaidController::class, 'syncAll']);

// Stripe webhook route (public, no auth - uses signature verification)
Route::post('stripe/webhook', [StripePaymentController::class, 'handleWebhook']);

// Legacy route for backward compatibility
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
