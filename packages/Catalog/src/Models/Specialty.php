<?php

namespace Packages\Catalog\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

class Specialty extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'icon',
        'cover_media_id',
        'hero_media_id',
        'intro_image_media_id',
        'show_lead_form',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_lead_form' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
        'description',
    ];

    public $translationModel = SpecialtyTranslation::class;

    protected array $searchable = ['translations.name', 'translations.slug'];

    protected array $filterable = ['is_active'];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_specialty');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_specialty');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'hero_media_id');
    }

    public function introImage(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'intro_image_media_id');
    }
}
