<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Panel Configuration
    |--------------------------------------------------------------------------
    |
    | These settings configure the admin panel routes and naming conventions.
    | All packages should use these values via helper functions.
    |
    */

    // Admin route prefix (e.g., 'admin', 'cms', 'dashboard')
    'admin_prefix' => env('CORE_ADMIN_PREFIX', 'admin'),

    // Admin route name prefix (e.g., 'admin', 'cms', 'dashboard')
    'admin_route_name' => env('CORE_ADMIN_ROUTE_NAME', 'admin'),
];
