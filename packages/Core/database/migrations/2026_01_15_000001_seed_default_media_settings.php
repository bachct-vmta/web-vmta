<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default media settings to seed
     */
    protected array $defaults = [
        'media_max_file_size' => '10485760', // 10MB
        'media_allowed_mime_types' => 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,zip',
        'media_chunk_enabled' => '0',
        'media_chunk_size' => '1048576', // 1MB
        'media_document_preview_enabled' => '1',
        'media_document_preview_provider' => 'microsoft',
        'media_default_upload_folder' => 'uploads',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure media_settings table exists
        if (! Schema::hasTable('media_settings')) {
            Schema::create('media_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed default settings (only if not exists)
        foreach ($this->defaults as $key => $value) {
            $exists = DB::table('media_settings')->where('key', $key)->exists();

            if (! $exists) {
                DB::table('media_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove seeded settings
        DB::table('media_settings')
            ->whereIn('key', array_keys($this->defaults))
            ->delete();
    }
};
