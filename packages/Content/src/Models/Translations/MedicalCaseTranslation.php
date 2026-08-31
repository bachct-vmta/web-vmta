<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class MedicalCaseTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'intro',
        'col1_items',
        'col2_items',
        'col3_body',
        'detail_content',
    ];

    protected $casts = [
        'col1_items'     => 'array',
        'col2_items'     => 'array',
        'detail_content' => 'array',
    ];
}
