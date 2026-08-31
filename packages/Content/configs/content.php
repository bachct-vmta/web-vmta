<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pageview de-dup TTL (seconds)
    |--------------------------------------------------------------------------
    | IncrementPostViewCount job ignores repeat hits from the same (postId, IP)
    | pair within this window. Default 1h. Set via env `CONTENT_VIEW_COUNT_TTL`.
    */
    'view_count_dedup_ttl' => env('CONTENT_VIEW_COUNT_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Sitemap cache TTL (seconds)
    |--------------------------------------------------------------------------
    | /sitemap.xml is cached for this duration. Lower means fresher content
    | discoverable by crawlers; higher means less DB load.
    */
    'sitemap_ttl' => env('CONTENT_SITEMAP_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Medical Case Columns (Achievement Page)
    |--------------------------------------------------------------------------
    | 3 cố định columns trong mỗi case trên /thanh-tuu-y-khoa.
    | icon_path = relative to /images/medical-achievements/.
    */
    'medical_case_columns' => [
        ['icon_path' => 'icon-breakthrough.png',   'title' => 'GIẢI PHÁP ĐỘT PHÁ'],
        ['icon_path' => 'icon-effectiveness.png',  'title' => 'HIỆU QUẢ VƯỢT TRỘI'],
        ['icon_path' => 'icon-meaning.png',        'title' => 'Ý NGHĨA'],
    ],
];
