<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('upstream_conversation_id')->nullable()->index();
            $table->unsignedSmallInteger('message_count')->default(0);
            $table->unsignedSmallInteger('max_messages')->default(10);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('locale', 10)->default('vi');
            $table->string('handoff_reason', 30)->nullable(); // overflow | upstream_error
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};
