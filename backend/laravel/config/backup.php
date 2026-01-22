<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for document backup service that creates ZIP archives
    | of application documents and uploads them to Amazon S3.
    |
    */

    'api_key' => env('BACKUP_API_KEY'),
    
    'documents' => [
        'source_path' => storage_path('app/documents'),
        's3_bucket' => env('AWS_BUCKET', 'neibrpay-files-local'),
        's3_prefix' => 'backups/documents/',
    ],
];
