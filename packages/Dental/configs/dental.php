<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URL slugs
    |--------------------------------------------------------------------------
    |
    | Segment mở đầu của ba trang public, khai báo theo từng locale. Đổi ở đây
    | là đổi URL, không ảnh hưởng slug của bản ghi trong CSDL.
    |
    */

    'url_slug' => [
        'vi' => 'kham-nha',
        'en' => 'dental-care',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ảnh hero
    |--------------------------------------------------------------------------
    |
    | Thiết kế dùng chung một tấm ảnh cho dải hero của cả ba trang, không đổi theo
    | cơ sở. `cover_media_id` của cơ sở chỉ dùng làm ảnh trên card ở trang danh sách.
    |
    */

    'hero_image' => env('DENTAL_HERO_IMAGE', '/uploads/branches/kham-nha/hero-kham-nha.jpg'),

    'news_sidebar_limit' => 5,

];
