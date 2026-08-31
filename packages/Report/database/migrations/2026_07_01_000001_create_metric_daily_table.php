<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metric_daily', function (Blueprint $table) {
            $table->date('date');
            $table->string('metric_key', 100);
            $table->unsignedBigInteger('count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->primary(['date', 'metric_key']);
            $table->index('metric_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_daily');
    }
};
