<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dental_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
            $table->unique(['dental_category_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dental_category_translations');
    }
};
