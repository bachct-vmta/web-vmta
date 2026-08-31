<?php

/*
 * mcamara/laravel-localization — VMTA config (Phase 1).
 * URL prefix /vi, /en. Root / redirect to default locale.
 */

return [
    'supportedLocales' => [
        'vi' => [
            'name' => 'Vietnamese',
            'script' => 'Latn',
            'native' => 'Tiếng Việt',
            'regional' => 'vi_VN',
        ],
        'en' => [
            'name' => 'English',
            'script' => 'Latn',
            'native' => 'English',
            'regional' => 'en_US',
        ],
    ],

    'localesOrder' => ['vi', 'en'],

    'localesMapping' => [],

    'useAcceptLanguageHeader' => true,

    'hideDefaultLocaleInURL' => false,

    'localesVariantsMapping' => [],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', PHP_OS === 'Linux' ? '.UTF-8' : ''),

    'urlsIgnored' => ['/admin', '/api', '/login', '/logout', '/register'],
];
