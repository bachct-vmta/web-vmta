<?php

return [
    'heading' => 'System Settings',
    'updated' => 'Settings updated successfully.',
    'save' => 'Save changes',
    'enabled' => 'Enabled',
    'encrypted' => 'Encrypted',
    'encrypted_placeholder' => 'Leave empty to keep current',
    'groups' => [
        'site' => 'Site',
        'chatbot' => 'Chatbot',
        'seo' => 'SEO',
        'mail' => 'Mail',
        'general' => 'General',
        'social' => 'Social',
    ],
    // Nested by group (Laravel dot-notation parses keys hierarchically).
    'keys' => [
        'site' => [
            'logo_media_id'        => 'Site logo',
            'favicon_media_id'     => 'Favicon',
            'hotline_number'       => 'Hotline number',
            'handoff_form_url'     => 'Handoff form URL',
            'app_cta_url_default'  => 'Default CTA URL',
        ],
        'mail' => [
            'from_address'             => 'Default from address',
            'from_name'                => 'From name',
            'smtp_host'                => 'SMTP host',
            'smtp_port'                => 'SMTP port',
            'smtp_username'            => 'SMTP username',
            'smtp_password'            => 'SMTP password',
            'smtp_encryption'          => 'SMTP encryption (tls/ssl)',
            'notification_recipients'  => 'Notification recipients (comma-separated emails)',
        ],
        'chatbot' => [
            'document_group'            => 'Chatbot document group',
            'ai_provider'               => 'AI provider',
            'max_messages_per_session'  => 'Max messages per session',
            'session_ttl'               => 'Session TTL (seconds)',
        ],
        'social' => [
            'facebook_url'   => 'Facebook URL',
            'instagram_url'  => 'Instagram URL',
            'youtube_url'    => 'YouTube URL',
            'tiktok_url'     => 'TikTok URL',
        ],
    ],
    'test_mail' => [
        'button' => 'Send test email',
        'placeholder' => 'Leave empty to use first notification recipient / current user email',
        'subject' => 'VMTA — SMTP test email',
        'body' => 'This is a test email to verify SMTP configuration. If you got this, the configuration works.',
        'sent' => 'Test email sent to :email.',
        'failed' => 'Failed to send test email',
        'no_recipient' => 'No recipient available. Enter an email or configure mail.notification_recipients.',
    ],
];
