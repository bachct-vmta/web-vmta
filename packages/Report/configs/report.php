<?php

return [
    // Use Redis bucket for non-blocking increments. When false (or Redis unavailable),
    // increments write straight to metric_daily via repository upsert.
    'use_redis' => env('REPORT_USE_REDIS', true),

    // Dashboard cache TTL (seconds). Admin can force refresh via ?refresh=1.
    'dashboard_cache_ttl' => (int) env('REPORT_DASHBOARD_TTL', 300),
];
