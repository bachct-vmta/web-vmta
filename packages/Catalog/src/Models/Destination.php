<?php

namespace Packages\Catalog\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Catalog\Src\Models\Translations\DestinationTranslation;
use Packages\Core\Src\Models\BaseModel;

class Destination extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'country_code',
        'region',
        'cover_media_id',
        'latitude',
        'longitude',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
        'description',
    ];

    public $translationModel = DestinationTranslation::class;

    protected array $searchable = ['translations.name', 'translations.slug'];

    protected array $filterable = ['country_code', 'region', 'is_active'];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_destination');
    }

    public function tourPackages(): BelongsToMany
    {
        return $this->belongsToMany(TourPackage::class, 'tour_destination');
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }
}
