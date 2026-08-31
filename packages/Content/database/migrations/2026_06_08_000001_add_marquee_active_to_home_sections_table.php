<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            // Independent toggle for the hero marquee strip — lets admins hide the
            // whole marquee row without disabling the rest of the hero section.
            $table->boolean('marquee_active')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropColumn('marquee_active');
        });
    }
};
