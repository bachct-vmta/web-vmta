<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_case_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_case_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->string('subtitle', 500)->nullable();
            $table->text('intro')->nullable();
            $table->json('col1_items')->nullable();
            $table->json('col2_items')->nullable();
            $table->text('col3_body')->nullable();
            $table->timestamps();
            $table->unique(['medical_case_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_case_translations');
    }
};
