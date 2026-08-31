<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_specialty', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'specialty_id']);
            $table->index(['specialty_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_specialty');
    }
};
