<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class HomeSectionTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['title', 'subtitle', 'body', 'cta_label', 'cta_url', 'items'];

    protected $casts = [
        'items' => 'array',
    ];
}
