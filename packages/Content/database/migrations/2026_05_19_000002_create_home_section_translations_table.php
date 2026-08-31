<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title')->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
            $table->unique(['home_section_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_translations');
    }
};
