<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\Translations\AllianceSectionTranslation;
use Packages\Core\Src\Models\BaseModel;

class AllianceSection extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'alliance_sections';

    protected $fillable = ['position', 'is_active', 'sort_order'];

    protected $casts = [
        'position'   => AllianceSectionPosition::class,
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['title', 'subtitle', 'body', 'cta_label', 'items'];

    public $translationModel = AllianceSectionTranslation::class;

    protected array $filterable = ['is_active', 'position'];
}
