<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI') ? trim(env('GOOGLE_REDIRECT_URI'), '"\'/') : (env('APP_URL') . '/api/auth/google/callback'),
    ],

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'client_id' => env('STRIPE_CLIENT_ID'),
        'connect_webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
    ],

    'plaid' => [
        'client_id' => env('PLAID_CLIENT_ID'),
        'client_secret' => env('PLAID_CLIENT_SECRET'),
        'environment' => env('PLAID_ENVIRONMENT', 'sandbox'),
        'redirect_uri' => env('PLAID_REDIRECT_URI'),
        'api_key' => env('PLAID_API_KEY'),
    ],

];

