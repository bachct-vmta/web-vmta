<?php

namespace Packages\Dental\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;
use Packages\Dental\Src\Models\Concerns\Publishable;
use Packages\Dental\Src\Models\Translations\DentalFacilityTranslation;

class DentalFacility extends BaseModel implements TranslatableContract
{
    use Publishable;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'dental_category_id',
        'status',
        'published_at',
        'is_operating',
        'cover_media_id',
        'certificates_media_ids',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_operating' => 'boolean',
        'certificates_media_ids' => 'array',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = [
        'name',
        'slug',
        'address',
    ];

    public $translationModel = DentalFacilityTranslation::class;

    protected array $searchable = ['translations.name', 'translations.slug'];

    protected array $filterable = ['dental_category_id', 'status', 'is_operating'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DentalCategory::class, 'dental_category_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(DentalService::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        $permalink = $this->coverMedia?->permalink;

        return $permalink ? media_permalink_url($permalink) : null;
    }

    // Trả ảnh chứng nhận theo đúng thứ tự admin đã sắp, không theo thứ tự id
    public function certificateMedia(): Collection
    {
        $ids = $this->certificates_media_ids ?? [];

        if ($ids === []) {
            return MediaFile::query()->whereRaw('1 = 0')->get();
        }

        return MediaFile::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (MediaFile $media) => array_search($media->id, $ids, true))
            ->values();
    }
}
