<?php

return [
    'api_key' => env('POSTHOG_API_KEY', ''),
    'host' => env('POSTHOG_HOST', 'https://us.i.posthog.com'),
    'enabled' => env('POSTHOG_ENABLED', true),
];

