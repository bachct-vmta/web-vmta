<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog defaults
    |--------------------------------------------------------------------------
    */

    'cta_app_url_default' => env('CATALOG_CTA_APP_URL_DEFAULT'),

    'pagination' => [
        'list_per_page' => env('CATALOG_LIST_PER_PAGE', 12),
        'partner_per_page' => env('CATALOG_PARTNER_PER_PAGE', 15),
        'search_per_type' => env('CATALOG_SEARCH_PER_TYPE', 10),
        'suggest_limit' => env('CATALOG_SUGGEST_LIMIT', 5),
    ],

    'gallery' => [
        'max_size_kb' => 5120,
        'sizes' => [
            'thumb' => [400, 400],
            'medium' => [800, 600],
            'large' => [1600, 1200],
        ],
    ],

    'cache' => [
        'facets_ttl' => 600, // 10 minutes
    ],

    'lead_recipient' => env('CATALOG_LEAD_RECIPIENT', 'vmta@vmta.vn'),
];
