<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_category_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            // Nhãn "Đang hoạt động" trên card, tách khỏi trạng thái xuất bản
            $table->boolean('is_operating')->default(true);
            // Không ràng buộc FK sang media_files vì kiểu khoá chính ở DB hiện tại lệch signed/unsigned
            $table->unsignedBigInteger('cover_media_id')->nullable()->index();
            $table->json('certificates_media_ids')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_facilities');
    }
};
