<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\Translations\HomeSectionTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

class HomeSection extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'home_sections';

    protected $fillable = [
        'position',
        'image_media_id',
        'video_url',
        'is_active',
        'marquee_active',
        'sort_order',
    ];

    protected $casts = [
        'position' => HomeSectionPosition::class,
        'is_active' => 'boolean',
        'marquee_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['title', 'subtitle', 'body', 'cta_label', 'cta_url', 'items'];

    public $translationModel = HomeSectionTranslation::class;

    protected array $filterable = ['is_active', 'position'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }
}
