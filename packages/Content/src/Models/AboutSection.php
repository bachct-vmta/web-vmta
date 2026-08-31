<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Models\Translations\AboutSectionTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

class AboutSection extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'about_sections';

    protected $fillable = ['position', 'is_active', 'sort_order', 'image_1_media_id', 'image_2_media_id'];

    protected $casts = [
        'position'  => AboutSectionPosition::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['title', 'subtitle', 'body', 'cta_label', 'items'];

    public $translationModel = AboutSectionTranslation::class;

    protected array $filterable = ['is_active', 'position'];

    public function image1(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_1_media_id');
    }

    public function image2(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_2_media_id');
    }
}
