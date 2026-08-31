<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialty_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['specialty_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_translations');
    }
};
