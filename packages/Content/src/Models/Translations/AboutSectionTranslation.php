<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class AboutSectionTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['title', 'subtitle', 'body', 'cta_label', 'cta_link', 'cta2_link', 'items'];

    protected $casts = [
        'items' => 'array',
    ];
}
