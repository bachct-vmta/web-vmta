<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Tags module removed. Drops the pivot first (FK constraints), then translations,
 * then the parent tags table. Irreversible — down() is a no-op since the source
 * migrations have also been deleted from the package.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tag_translations');
        Schema::dropIfExists('tags');
    }

    public function down(): void
    {
        // Tags module removed permanently. To reinstate, restore the original
        // create_tags_table / create_tag_translations_table / create_post_tag_table
        // migrations from git history and re-run.
    }
};
