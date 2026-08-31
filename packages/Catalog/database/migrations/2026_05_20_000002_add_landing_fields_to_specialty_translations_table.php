<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specialty_translations', function (Blueprint $table) {
            $table->string('hero_h1', 255)->nullable()->after('description');
            $table->string('breadcrumb_label', 120)->nullable()->after('hero_h1');

            $table->string('intro_h2', 500)->nullable()->after('breadcrumb_label');
            $table->string('intro_lead', 500)->nullable()->after('intro_h2');
            $table->longText('intro_body_html')->nullable()->after('intro_lead');

            $table->string('strengths_h2_line1', 255)->nullable()->after('intro_body_html');
            $table->string('strengths_h2_line2', 255)->nullable()->after('strengths_h2_line1');
            $table->json('strengths_json')->nullable()->after('strengths_h2_line2');

            $table->string('hospitals_h2_line1', 255)->nullable()->after('strengths_json');
            $table->string('hospitals_h2_line2', 255)->nullable()->after('hospitals_h2_line1');
            $table->string('hospitals_subtitle', 500)->nullable()->after('hospitals_h2_line2');
            $table->json('hospitals_json')->nullable()->after('hospitals_subtitle');

            $table->string('lead_h2_line1', 255)->nullable()->after('hospitals_json');
            $table->string('lead_h2_line2', 255)->nullable()->after('lead_h2_line1');
            $table->string('lead_subtitle', 500)->nullable()->after('lead_h2_line2');
            $table->text('lead_body_html')->nullable()->after('lead_subtitle');
            $table->string('lead_demand_placeholder', 255)->nullable()->after('lead_body_html');
            $table->string('lead_submit_label', 120)->nullable()->after('lead_demand_placeholder');

            $table->string('seo_title', 255)->nullable()->after('lead_submit_label');
            $table->string('seo_description', 500)->nullable()->after('seo_title');
            $table->string('seo_og_image', 500)->nullable()->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('specialty_translations', function (Blueprint $table) {
            $table->dropColumn([
                'hero_h1',
                'breadcrumb_label',
                'intro_h2',
                'intro_lead',
                'intro_body_html',
                'strengths_h2_line1',
                'strengths_h2_line2',
                'strengths_json',
                'hospitals_h2_line1',
                'hospitals_h2_line2',
                'hospitals_subtitle',
                'hospitals_json',
                'lead_h2_line1',
                'lead_h2_line2',
                'lead_subtitle',
                'lead_body_html',
                'lead_demand_placeholder',
                'lead_submit_label',
                'seo_title',
                'seo_description',
                'seo_og_image',
            ]);
        });
    }
};
