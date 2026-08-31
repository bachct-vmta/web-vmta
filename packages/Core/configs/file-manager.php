<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Driver Configuration
    |--------------------------------------------------------------------------
    |
    | Supported drivers: 'local', 'google'
    | Default: 'local'
    |
    */
    'default_driver' => env('MEDIA_STORAGE_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Google Drive Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Drive storage.
    | Required when default_driver = 'google'
    |
    */
    'google_drive' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    */
    'mime_types' => [
        'image/png',
        'image/jpeg',
        'image/webp',
        'text/csv',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/pdf',
        'video/mp4',
        'video/mpeg',
        'video/ogg',
        'image/vnd.microsoft.icon',
        'image/x-icon',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Extensions
    |--------------------------------------------------------------------------
    */
    'extensions' => [
        'png',
        'jpg',
        'webp',
        'csv',
        'xlsx',
        'xls',
        'pdf',
        'mp4',
        'mpeg',
        'ogv',
        'ico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 2097152), // 2MB default
    'limit_upload_files' => 20,
    'path_folder' => 'uploads',
    'permission' => 0755,
    'base_path' => '/uploads',
    'media_url' => env('MEDIA_URL'),

    /*
    |--------------------------------------------------------------------------
    | Legacy Config (deprecated)
    |--------------------------------------------------------------------------
    */
    'diskList' => 'local', // @deprecated Use default_driver instead
];
