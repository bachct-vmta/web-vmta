<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds 2 generic image FKs to about_sections — primarily for the who_are section
 * which needs 2 illustration images. Other positions can leave them NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_sections', function (Blueprint $table) {
            $table->foreignId('image_1_media_id')->nullable()->after('sort_order')
                ->constrained('media_files')->nullOnDelete();
            $table->foreignId('image_2_media_id')->nullable()->after('image_1_media_id')
                ->constrained('media_files')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('about_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_1_media_id');
            $table->dropConstrainedForeignId('image_2_media_id');
        });
    }
};
