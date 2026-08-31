<?php

return [
    'heading' => 'Cài đặt hệ thống',
    'updated' => 'Đã cập nhật cài đặt.',
    'save' => 'Lưu thay đổi',
    'enabled' => 'Bật',
    'encrypted' => 'Đã mã hóa',
    'encrypted_placeholder' => 'Để trống nếu không đổi',
    'groups' => [
        'site' => 'Trang web',
        'chatbot' => 'Chatbot',
        'seo' => 'SEO',
        'mail' => 'Email',
        'general' => 'Chung',
        'social' => 'Mạng xã hội',
    ],
    // Nested by group (Laravel dot-notation parses keys hierarchically).
    // Access via __('core::settings.keys.mail.from_address') etc.
    'keys' => [
        'site' => [
            'logo_media_id'        => 'Logo trang',
            'favicon_media_id'     => 'Favicon',
            'hotline_number'       => 'Số hotline',
            'handoff_form_url'     => 'URL form chuyển tiếp',
            'app_cta_url_default'  => 'URL CTA mặc định',
        ],
        'mail' => [
            'from_address'             => 'Email gửi mặc định',
            'from_name'                => 'Tên người gửi',
            'smtp_host'                => 'SMTP host',
            'smtp_port'                => 'SMTP port',
            'smtp_username'            => 'SMTP username',
            'smtp_password'            => 'SMTP password',
            'smtp_encryption'          => 'Mã hóa SMTP (tls/ssl)',
            'notification_recipients'  => 'Danh sách email nhận thông báo (ngăn cách dấu phẩy)',
        ],
        'chatbot' => [
            'document_group'            => 'Nhóm tài liệu chatbot',
            'ai_provider'               => 'AI provider',
            'max_messages_per_session'  => 'Số tin nhắn tối đa / phiên',
            'session_ttl'               => 'Thời gian sống phiên (giây)',
        ],
        'social' => [
            'facebook_url'   => 'Link Facebook',
            'instagram_url'  => 'Link Instagram',
            'youtube_url'    => 'Link YouTube',
            'tiktok_url'     => 'Link TikTok',
        ],
    ],
    'test_mail' => [
        'button' => 'Gửi email thử',
        'placeholder' => 'Để trống = dùng email người nhận thông báo đầu tiên / email tài khoản hiện tại',
        'subject' => 'VMTA — Email thử SMTP',
        'body' => 'Đây là email thử để kiểm tra cấu hình SMTP. Nếu bạn nhận được, cấu hình đang hoạt động.',
        'sent' => 'Đã gửi email thử tới :email.',
        'failed' => 'Gửi email thử thất bại',
        'no_recipient' => 'Không có email người nhận. Vui lòng nhập hoặc thiết lập mail.notification_recipients.',
    ],
];
