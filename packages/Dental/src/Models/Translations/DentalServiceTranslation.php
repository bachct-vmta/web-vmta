<?php

namespace Packages\Dental\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class DentalServiceTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'hero_h1',
        'video_caption',
        'body',
        'comparison_html',
        'price_table_html',
    ];
}
