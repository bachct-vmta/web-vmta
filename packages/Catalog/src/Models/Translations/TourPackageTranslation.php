<?php

namespace Packages\Catalog\Src\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Packages\Catalog\Src\Models\TourPackage;

class TourPackageTranslation extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'itinerary',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    protected static function booted(): void
    {
        $reindex = function (self $translation): void {
            $parent = TourPackage::find($translation->tour_package_id);
            if ($parent !== null) {
                $parent->searchable();
            }
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
