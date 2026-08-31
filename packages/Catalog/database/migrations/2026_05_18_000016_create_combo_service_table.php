<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_service', function (Blueprint $table) {
            $table->foreignId('combo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['combo_id', 'service_id']);
            $table->index(['service_id', 'combo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_service');
    }
};
