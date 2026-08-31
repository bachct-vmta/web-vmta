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
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('permalink');
            $table->integer('user_id')->unsigned()->default(0)->index();
            $table->integer('parent_id')->default(0)->index();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alt');
            $table->string('permalink');
            $table->integer('size')->default(0);
            $table->string('mine_type')->default('');
            $table->integer('user_id')->unsigned()->default(0)->index();
            $table->integer('folder_id')->unsigned()->default(0)->index();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('media_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_folders');
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_settings');
    }
};
