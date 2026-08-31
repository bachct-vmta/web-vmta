<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slug dịch vụ chỉ cần duy nhất trong phạm vi một cơ sở, nhưng dental_facility_id nằm ở
 * bảng cha nên ràng buộc đó không đặt được ở tầng DB — StoreDentalServiceRequest lo việc này.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_service_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->string('slug');
            $table->string('hero_h1')->nullable();
            $table->string('video_caption')->nullable();
            $table->longText('body')->nullable();
            $table->longText('comparison_html')->nullable();
            $table->longText('price_table_html')->nullable();
            $table->timestamps();
            $table->unique(['dental_service_id', 'locale']);
            $table->index(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_service_translations');
    }
};
