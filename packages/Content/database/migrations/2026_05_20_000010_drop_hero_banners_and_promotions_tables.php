<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('promotion_translations');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('hero_banner_translations');
        Schema::dropIfExists('hero_banners');
    }

    public function down(): void
    {
        // Feature removed; no-op rollback.
    }
};
