<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class AchievementSectionTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = ['title', 'subtitle', 'body', 'cta_label', 'items'];

    protected $casts = [
        'items' => 'array',
    ];
}
