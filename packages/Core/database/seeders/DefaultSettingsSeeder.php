<?php

namespace Packages\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Core\Src\Models\Setting;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->defaults() as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'type' => $row['type'],
                    'group' => $row['group'],
                    'is_encrypted' => $row['is_encrypted'] ?? false,
                    'description' => $row['description'] ?? null,
                ],
            );
        }

        Setting::clearCache();
    }

    /**
     * @return array<int, array{key:string,value:?string,type:string,group:string,is_encrypted?:bool,description?:string}>
     */
    protected function defaults(): array
    {
        return [
            // Site
            [
                'key' => 'site.hotline_number',
                'value' => '1900-1234',
                'type' => 'string',
                'group' => 'site',
                'description' => 'Hotline hiển thị header/footer/chatbot CTA.',
            ],
            [
                'key' => 'site.handoff_form_url',
                'value' => '',
                'type' => 'string',
                'group' => 'site',
                'description' => 'URL form chuyển hồ sơ (Tally/Google Forms).',
            ],
            [
                'key' => 'site.app_cta_url_default',
                'value' => '',
                'type' => 'string',
                'group' => 'site',
                'description' => 'CTA URL mặc định cho catalog item (fallback khi item không set).',
            ],
            // Chatbot
            [
                'key' => 'chatbot.document_group',
                'value' => 'DULICH',
                'type' => 'string',
                'group' => 'chatbot',
                'description' => 'Document group truyền lên Tourism API khi tạo conversation.',
            ],
            [
                'key' => 'chatbot.ai_provider',
                'value' => null,
                'type' => 'string',
                'group' => 'chatbot',
                'description' => 'AI provider override (để trống = upstream default).',
            ],
            [
                'key' => 'chatbot.max_messages_per_session',
                'value' => '10',
                'type' => 'int',
                'group' => 'chatbot',
                'description' => 'Số lượng message tối đa cho mỗi visitor session.',
            ],
            [
                'key' => 'chatbot.session_ttl',
                'value' => '86400',
                'type' => 'int',
                'group' => 'chatbot',
                'description' => 'TTL session chatbot (giây).',
            ],
            // Branding (Site)
            [
                'key' => 'site.logo_media_id',
                'value' => null,
                'type' => 'int',
                'group' => 'site',
                'description' => 'Logo hiển thị header/footer (chọn từ Media).',
            ],
            [
                'key' => 'site.favicon_media_id',
                'value' => null,
                'type' => 'int',
                'group' => 'site',
                'description' => 'Favicon trình duyệt (chọn từ Media, khuyến nghị PNG/ICO vuông).',
            ],
            // Mail
            [
                'key' => 'mail.from_address',
                'value' => 'noreply@vmta.vn',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Địa chỉ From mặc định.',
            ],
            [
                'key' => 'mail.from_name',
                'value' => 'VMTA',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Tên hiển thị From mặc định.',
            ],
            [
                'key' => 'mail.smtp_host',
                'value' => '',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'SMTP host (vd: smtp.gmail.com).',
            ],
            [
                'key' => 'mail.smtp_port',
                'value' => '587',
                'type' => 'int',
                'group' => 'mail',
                'description' => 'SMTP port (587 cho TLS, 465 cho SSL).',
            ],
            [
                'key' => 'mail.smtp_username',
                'value' => '',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Tài khoản đăng nhập SMTP.',
            ],
            [
                'key' => 'mail.smtp_password',
                'value' => null,
                'type' => 'string',
                'group' => 'mail',
                'is_encrypted' => true,
                'description' => 'Mật khẩu / App password SMTP (lưu mã hoá).',
            ],
            [
                'key' => 'mail.smtp_encryption',
                'value' => 'tls',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Mã hoá kết nối: tls | ssl | (để trống = none).',
            ],
            [
                'key' => 'mail.notification_recipients',
                'value' => '',
                'type' => 'string',
                'group' => 'mail',
                'description' => 'Email nhận thông tin (inquiry/newsletter), ngăn cách dấu phẩy.',
            ],
        ];
    }
}
