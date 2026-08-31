<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_facility_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            // Không ràng buộc FK sang media_files vì kiểu khoá chính ở DB hiện tại lệch signed/unsigned
            $table->unsignedBigInteger('icon_media_id')->nullable()->index();
            $table->unsignedBigInteger('video_poster_media_id')->nullable()->index();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_services');
    }
};
