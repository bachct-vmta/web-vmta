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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('password');
            $table->foreignId('role_id')->nullable()->after('balance')->constrained('roles')->nullOnDelete();
            $table->json('permissions')->nullable()->after('role_id');
            $table->boolean('is_super_user')->default(false)->after('permissions');
            $table->boolean('is_active')->default(true)->after('is_super_user');
            $table->string('phone')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'balance',
                'role_id',
                'permissions',
                'is_super_user',
                'is_active',
                'phone',
            ]);
        });
    }
};
