<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Packages\Catalog\Src\Models\Service;

class ServiceTranslation extends Model
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

    // Translation changes do not bubble up to the parent's Scout observer.
    // Trigger re-index explicitly so admins see fresh search results after a save.
    protected static function booted(): void
    {
        $reindex = function (self $translation): void {
            $parent = Service::find($translation->service_id);
            if ($parent !== null) {
                $parent->searchable();
            }
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
