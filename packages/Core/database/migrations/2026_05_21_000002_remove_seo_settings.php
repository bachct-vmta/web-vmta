<?php

use Illuminate\Database\Migrations\Migration;
use Packages\Core\Src\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        Setting::where('group', 'seo')->delete();
        Setting::clearCache();
    }

    public function down(): void
    {
        // Restore minimal defaults if rolled back.
        foreach ([
            ['key' => 'seo.site_name', 'value' => 'VMTA — Vietnam Medical Tourism Alliance', 'description' => 'Tên site dùng cho meta + Open Graph.'],
            ['key' => 'seo.default_og_image', 'value' => '', 'description' => 'OG image mặc định khi page không có cover.'],
        ] as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'type' => 'string',
                    'group' => 'seo',
                    'is_encrypted' => false,
                    'description' => $row['description'],
                ],
            );
        }
        Setting::clearCache();
    }
};
