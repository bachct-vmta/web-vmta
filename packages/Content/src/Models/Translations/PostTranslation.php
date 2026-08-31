<?php

namespace Packages\Content\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];
}
