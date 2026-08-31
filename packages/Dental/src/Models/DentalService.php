<?php

namespace Packages\Dental\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;
use Packages\Dental\Src\Models\Concerns\Publishable;
use Packages\Dental\Src\Models\Translations\DentalServiceTranslation;

class DentalService extends BaseModel implements TranslatableContract
{
    use Publishable;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'dental_facility_id',
        'status',
        'published_at',
        'icon_media_id',
        'video_poster_media_id',
        'video_url',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = [
        'title',
        'slug',
        'hero_h1',
        'video_caption',
        'body',
        'comparison_html',
        'price_table_html',
    ];

    public $translationModel = DentalServiceTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['dental_facility_id', 'status'];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(DentalFacility::class, 'dental_facility_id');
    }

    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'icon_media_id');
    }

    public function videoPosterMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'video_poster_media_id');
    }

    public function getIconUrlAttribute(): ?string
    {
        $permalink = $this->iconMedia?->permalink;

        return $permalink ? media_permalink_url($permalink) : null;
    }

    public function getVideoPosterUrlAttribute(): ?string
    {
        $permalink = $this->videoPosterMedia?->permalink;

        return $permalink ? media_permalink_url($permalink) : null;
    }
}
