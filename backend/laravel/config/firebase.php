<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase Admin SDK integration
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),
    
    'credentials_path' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
    
    'database_url' => env('FIREBASE_DATABASE_URL'),
    
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    
    'app_id' => env('FIREBASE_APP_ID'),
    
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
];
