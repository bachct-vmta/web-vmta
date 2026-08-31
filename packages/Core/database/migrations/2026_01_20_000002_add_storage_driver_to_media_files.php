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
        Schema::table('media_files', function (Blueprint $table) {
            $table->string('storage_driver')->default('local')->after('folder_id');
            $table->string('external_id')->nullable()->after('storage_driver');
            $table->index('storage_driver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_files', function (Blueprint $table) {
            $table->dropIndex(['storage_driver']);
            $table->dropColumn(['storage_driver', 'external_id']);
        });
    }
};
