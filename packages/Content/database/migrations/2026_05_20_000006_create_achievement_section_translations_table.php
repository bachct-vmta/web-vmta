<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('achievement_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title')->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
            $table->unique(['achievement_section_id', 'locale'], 'ach_sec_trans_section_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_section_translations');
    }
};
