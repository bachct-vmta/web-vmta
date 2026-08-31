<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_tour', function (Blueprint $table) {
            $table->foreignId('combo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->primary(['combo_id', 'tour_package_id'], 'combo_tour_primary');
            $table->index(['tour_package_id', 'combo_id'], 'combo_tour_reverse_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_tour');
    }
};
