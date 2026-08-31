<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index(); // hospital | hotel | travel
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('logo_media_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('website')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
