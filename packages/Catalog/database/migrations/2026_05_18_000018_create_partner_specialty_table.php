<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_specialty', function (Blueprint $table) {
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();
            $table->primary(['partner_id', 'specialty_id']);
            $table->index(['specialty_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_specialty');
    }
};
