<?php

namespace Packages\Dental\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Core\Src\Models\BaseModel;
use Packages\Dental\Src\Models\Concerns\Publishable;
use Packages\Dental\Src\Models\Translations\DentalCategoryTranslation;

class DentalCategory extends BaseModel implements TranslatableContract
{
    use Publishable;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'status',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
    ];

    public $translationModel = DentalCategoryTranslation::class;

    protected array $searchable = ['translations.name', 'translations.slug'];

    protected array $filterable = ['status'];

    public function facilities(): HasMany
    {
        return $this->hasMany(DentalFacility::class);
    }
}
