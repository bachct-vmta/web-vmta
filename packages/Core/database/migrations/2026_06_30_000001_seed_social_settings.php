<?php

use Illuminate\Database\Migrations\Migration;
use Packages\Core\Src\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->rows() as $row) {
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
            // Social links (group=social)
            [
                'key' => 'social.facebook_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Link Facebook (hiển thị ở footer).',
            ],
            [
                'key' => 'social.instagram_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Link Instagram (hiển thị ở footer).',
            ],
            [
                'key' => 'social.youtube_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Link YouTube (hiển thị ở footer).',
            ],
            [
                'key' => 'social.tiktok_url',
                'value' => '',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Link TikTok (hiển thị ở footer).',
            ],
        ];
    }
};
