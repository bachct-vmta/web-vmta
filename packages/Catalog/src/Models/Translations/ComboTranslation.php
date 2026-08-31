<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Packages\Catalog\Src\Models\Combo;

class ComboTranslation extends Model
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

    protected static function booted(): void
    {
        $reindex = function (self $translation): void {
            $parent = Combo::find($translation->combo_id);
            if ($parent !== null) {
                $parent->searchable();
            }
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
