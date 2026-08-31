<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialty_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('email', 190)->nullable();
            $table->string('phone', 64);
            $table->string('demand', 500)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new');
            $table->string('locale', 8)->default('vi');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['specialty_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialty_leads');
    }
};
