<?php

namespace Packages\Catalog\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Catalog\Src\Models\Translations\PartnerTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

/**
 * Partner intentionally NOT indexed by Scout (plan: keep network page dedicated,
 * avoid noise in cross-entity search). Search via Partner page filter instead.
 */
class Partner extends BaseModel implements TranslatableContract
{
    use SoftDeletes;
    use Translatable;

    public const TYPE_HOSPITAL = 'hospital';

    public const TYPE_HOTEL = 'hotel';

    public const TYPE_TRAVEL = 'travel';

    public const TYPE_INSURANCE = 'insurance';

    public const TYPES = [
        self::TYPE_HOSPITAL,
        self::TYPE_HOTEL,
        self::TYPE_TRAVEL,
        self::TYPE_INSURANCE,
    ];

    protected $fillable = [
        'type',
        'destination_id',
        'logo_media_id',
        'website',
        'phone',
        'email',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
        'short_description',
        'description',
        'address',
    ];

    public $translationModel = PartnerTranslation::class;

    protected array $searchable = ['translations.name', 'translations.slug'];

    protected array $filterable = ['type', 'destination_id', 'is_active'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'logo_media_id');
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'partner_specialty');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function tourPackages(): HasMany
    {
        return $this->hasMany(TourPackage::class);
    }
}
