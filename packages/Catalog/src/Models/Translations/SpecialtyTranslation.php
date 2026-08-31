<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class SpecialtyTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'specialty_id',
        'locale',
        'name',
        'slug',
        'description',
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
    ];

    protected $casts = [
        'strengths_json' => 'array',
        'hospitals_json' => 'array',
    ];
}
