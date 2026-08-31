<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specialties', function (Blueprint $table) {
            $table->foreignId('hero_media_id')
                ->nullable()
                ->after('cover_media_id')
                ->constrained('media_files')
                ->nullOnDelete();

            $table->foreignId('intro_image_media_id')
                ->nullable()
                ->after('hero_media_id')
                ->constrained('media_files')
                ->nullOnDelete();

            $table->boolean('show_lead_form')
                ->default(true)
                ->after('intro_image_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('specialties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hero_media_id');
            $table->dropConstrainedForeignId('intro_image_media_id');
            $table->dropColumn('show_lead_form');
        });
    }
};
