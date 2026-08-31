<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin notification recipient
    |--------------------------------------------------------------------------
    | Email that receives InquiryReceivedMail. In Phase 6 the Settings module
    | can override this at runtime via setting('inquiry.admin_email').
    */
    'admin_email' => env('INQUIRY_ADMIN_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Rate limiting
    |--------------------------------------------------------------------------
    | Public inquiry POST routes share this throttle bucket. Default: 5/minute/IP.
    */
    'throttle' => env('INQUIRY_THROTTLE', '5,1'),

    /*
    |--------------------------------------------------------------------------
    | Queue connection
    |--------------------------------------------------------------------------
    | Override the queue used for inquiry mail jobs. Null = default queue.
    */
    'queue' => env('INQUIRY_QUEUE'),

    /*
    |--------------------------------------------------------------------------
    | Office contact information
    |--------------------------------------------------------------------------
    | Displayed in the contact page info section. Override via .env per environment.
    */
    'offices' => [
        'hq' => [
            'address' => env('INQUIRY_HQ_ADDRESS', '18 Trần Hưng Đạo, Quận 1, TP. Hồ Chí Minh'),
            'email'   => env('INQUIRY_HQ_EMAIL', 'vmta@vmta.test'),
        ],
        'branch' => [
            'address' => env('INQUIRY_BRANCH_ADDRESS', 'Chi nhánh VMTA'),
            'email'   => env('INQUIRY_BRANCH_EMAIL', 'vmta@vmta.test'),
        ],
        'tech_support' => [
            'phone' => env('INQUIRY_TECH_PHONE', ''),
            'email' => env('INQUIRY_TECH_EMAIL', 'vmta@vmta.test'),
        ],
    ],
];
