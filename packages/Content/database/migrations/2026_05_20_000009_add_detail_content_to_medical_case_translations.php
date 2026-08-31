<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('medical_case_translations', 'detail_content')) {
            return;
        }

        Schema::table('medical_case_translations', function (Blueprint $table) {
            $table->json('detail_content')->nullable()->after('col3_body');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('medical_case_translations', 'detail_content')) {
            return;
        }

        Schema::table('medical_case_translations', function (Blueprint $table) {
            $table->dropColumn('detail_content');
        });
    }
};
