<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Models\Translations\AchievementSectionTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

class AchievementSection extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'achievement_sections';

    protected $fillable = ['position', 'image_media_id', 'is_active', 'sort_order'];

    protected $casts = [
        'position'   => AchievementSectionPosition::class,
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['title', 'subtitle', 'body', 'cta_label', 'items'];

    public $translationModel = AchievementSectionTranslation::class;

    protected array $filterable = ['is_active', 'position'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }
}
