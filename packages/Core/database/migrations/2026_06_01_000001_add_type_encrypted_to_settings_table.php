<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'type')) {
                $table->string('type', 16)->default('string')->after('value');
            }
            if (! Schema::hasColumn('settings', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false)->after('type');
            }
            if (! Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('group');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['type', 'is_encrypted', 'description'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
