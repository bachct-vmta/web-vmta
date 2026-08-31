<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('google_drive_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->text('access_token_enc');
            $table->text('refresh_token_enc');
            $table->timestamp('expires_at');
            $table->string('folder_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_drive_credentials');
    }
};
