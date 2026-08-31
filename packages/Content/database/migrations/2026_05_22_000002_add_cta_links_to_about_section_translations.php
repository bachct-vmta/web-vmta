<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds per-locale CTA URLs alongside the existing translatable cta_label / subtitle
 * pair, so admin can point each About-section button to a custom URL without
 * editing Blade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('about_section_translations', function (Blueprint $table) {
            $table->string('cta_link', 500)->nullable()->after('cta_label');
            $table->string('cta2_link', 500)->nullable()->after('cta_link');
        });
    }

    public function down(): void
    {
        Schema::table('about_section_translations', function (Blueprint $table) {
            $table->dropColumn(['cta_link', 'cta2_link']);
        });
    }
};
