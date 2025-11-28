<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

// Plaid Debug Route
Route::get('/plaid-debug', function () {
    $config = [
        'PLAID_CLIENT_ID' => env('PLAID_CLIENT_ID') ? substr(env('PLAID_CLIENT_ID'), 0, 10) . '...' : 'NOT SET',
        'PLAID_CLIENT_SECRET' => env('PLAID_CLIENT_SECRET') ? '***SET***' : 'NOT SET',
        'PLAID_ENVIRONMENT' => env('PLAID_ENVIRONMENT', 'sandbox'),
        'PLAID_REDIRECT_URI' => env('PLAID_REDIRECT_URI', 'NOT SET'),
        'PLAID_API_KEY' => env('PLAID_API_KEY') ? '***SET***' : 'NOT SET',
    ];

    $validation = [
        'client_id_length' => strlen(env('PLAID_CLIENT_ID')) > 5 ? 'VALID' : 'INVALID',
        'client_secret_length' => strlen(env('PLAID_CLIENT_SECRET')) > 5 ? 'VALID' : 'INVALID',
        'environment_valid' => in_array(env('PLAID_ENVIRONMENT'), ['sandbox', 'development', 'production']) ? 'VALID' : 'INVALID',
        'redirect_uri_set' => !empty(env('PLAID_REDIRECT_URI')) ? 'VALID' : 'INVALID',
    ];

    $config_from_services = [
        'services.plaid.client_id' => config('services.plaid.client_id') ? substr(config('services.plaid.client_id'), 0, 10) . '...' : 'NOT SET',
        'services.plaid.client_secret' => config('services.plaid.client_secret') ? '***SET***' : 'NOT SET',
        'services.plaid.environment' => config('services.plaid.environment', 'sandbox'),
        'services.plaid.redirect_uri' => config('services.plaid.redirect_uri'),
    ];

    return response()->json([
        'env_variables' => $config,
        'validation' => $validation,
        'config_services' => $config_from_services,
        'timestamp' => now(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ], 200, ['Content-Type' => 'application/json']);
});

// Test Plaid API Directly
Route::get('/plaid-test', function () {
    try {
        $clientId = config('services.plaid.client_id');
        $clientSecret = config('services.plaid.client_secret');
        $environment = config('services.plaid.environment', 'sandbox');
        $redirectUri = config('services.plaid.redirect_uri');

        if (!$clientId || !$clientSecret) {
            return response()->json([
                'error' => 'Missing Plaid credentials',
                'client_id_set' => !empty($clientId),
                'client_secret_set' => !empty($clientSecret),
            ], 400);
        }

        // Test API call to Plaid
        $response = Http::post("https://{$environment}.plaid.com/link/token/create", [
            'client_id' => $clientId,
            'secret' => $clientSecret,
            'user' => [
                'client_user_id' => 'test_user_123',
            ],
            'client_name' => 'NeibrPay Test',
            'language' => 'en',
            'country_codes' => ['US'],
            'products' => ['transactions'],
            'redirect_uri' => $redirectUri,
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => 'FAILED',
                'http_status' => $response->status(),
                'error_body' => json_decode($response->body(), true),
                'credentials_used' => [
                    'client_id' => substr($clientId, 0, 10) . '...',
                    'environment' => $environment,
                    'redirect_uri' => $redirectUri,
                ],
            ], $response->status());
        }

        return response()->json([
            'status' => 'SUCCESS',
            'http_status' => $response->status(),
            'link_token' => $response->json()['link_token'] ? 'GENERATED' : 'MISSING',
            'credentials_used' => [
                'client_id' => substr($clientId, 0, 10) . '...',
                'environment' => $environment,
                'redirect_uri' => $redirectUri,
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});
