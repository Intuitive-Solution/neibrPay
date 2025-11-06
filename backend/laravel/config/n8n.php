<?php

return [
    /*
    |--------------------------------------------------------------------------
    | n8n Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for n8n webhook integration for sending invoice emails
    | and other notifications asynchronously.
    |
    */

    'webhook_url' => env('N8N_WEBHOOK_URL', null),

    'webhook_secret' => env('N8N_WEBHOOK_SECRET', null),

    'verification_code_webhook_url' => env('N8N_VERIFICATION_CODE_WEBHOOK_URL', null),
];

