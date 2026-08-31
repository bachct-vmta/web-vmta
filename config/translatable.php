<?php

/*
 * astrotomic/laravel-translatable — VMTA config (Phase 1).
 * Locales: VI (default) + EN.
 */

return [
    'locales' => [
        'vi',
        'en',
    ],

    'locale_separator' => '-',

    'fallback_locale' => 'vi',

    'use_fallback' => true,

    'use_property_fallback' => true,

    'translation_model_namespace' => null,

    'translation_suffix' => 'Translation',

    'translation_model_location' => null,

    'locale_key' => 'locale',

    'to_array_always_loads_translations' => true,

    'rule_factory' => [
        'format' => \Astrotomic\Translatable\Validation\RuleFactory::FORMAT_ARRAY,
        'prefix' => '%',
        'suffix' => '%',
    ],
];
