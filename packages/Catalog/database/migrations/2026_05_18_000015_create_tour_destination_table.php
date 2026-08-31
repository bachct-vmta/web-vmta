<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_destination', function (Blueprint $table) {
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['tour_package_id', 'destination_id'], 'tour_destination_primary');
            $table->index(['destination_id', 'tour_package_id'], 'tour_destination_reverse_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_destination');
    }
};
