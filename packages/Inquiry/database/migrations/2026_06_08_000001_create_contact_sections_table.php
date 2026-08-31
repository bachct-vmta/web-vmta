<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_sections', function (Blueprint $table) {
            $table->id();
            $table->string('position', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Section illustration (offices) — locale-agnostic.
            // media_files.id is a SIGNED bigint (legacy schema), so match the type
            // explicitly; foreignId() would create an incompatible UNSIGNED column.
            $table->bigInteger('image_1_media_id')->nullable();
            $table->foreign('image_1_media_id')->references('id')->on('media_files')->nullOnDelete();
            // Raw Google Maps embed / iframe — single value, not translated.
            $table->text('map_embed')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_sections');
    }
};
