<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Packages\Core\Src\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Các cột type/is_encrypted/description do migration tháng 6 thêm sau, migrate từ đầu chưa có ở bước này
        $hasMetadataColumns = Schema::hasColumn('settings', 'type');

        foreach ($this->rows() as $row) {
            $attributes = [
                'value' => $row['value'],
                'group' => $row['group'],
            ];

            if ($hasMetadataColumns) {
                $attributes += [
                    'type' => $row['type'],
                    'is_encrypted' => $row['is_encrypted'] ?? false,
                    'description' => $row['description'] ?? null,
                ];
            }

            Setting::firstOrCreate(['key' => $row['key']], $attributes);
        }

        Setting::clearCache();
    }

    public function down(): void
    {
        Setting::whereIn('key', array_column($this->rows(), 'key'))->delete();
        Setting::clearCache();
    }

    /**
     * @return array<int, array{key:string,value:?string,type:string,group:string,is_encrypted?:bool,description?:string}>
     */
    protected function rows(): array
    {
        return [
            // Branding (group=site)
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

            // Mail SMTP (group=mail)
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
};
