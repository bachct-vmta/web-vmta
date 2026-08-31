<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add per-locale slug column (nullable initially for backfill)
        Schema::table('medical_case_translations', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->after('locale');
        });

        // Backfill: copy parent slug to every existing translation row
        DB::statement(
            'UPDATE medical_case_translations SET slug = (
                SELECT slug FROM medical_cases WHERE medical_cases.id = medical_case_translations.medical_case_id
            ) WHERE slug IS NULL'
        );

        // Add unique index (locale, slug) — allows NULLs in SQLite/PG but
        // pairs of same slug across different locales remain valid because
        // composite uniqueness is enforced.
        Schema::table('medical_case_translations', function (Blueprint $table) {
            $table->unique(['locale', 'slug'], 'medical_case_translations_locale_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('medical_case_translations', function (Blueprint $table) {
            $table->dropUnique('medical_case_translations_locale_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
