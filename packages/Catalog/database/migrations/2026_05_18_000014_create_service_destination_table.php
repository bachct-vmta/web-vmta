<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_destination', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['service_id', 'destination_id']);
            $table->index(['destination_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_destination');
    }
};
