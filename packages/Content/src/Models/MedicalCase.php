<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Models\Translations\MedicalCaseTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;

class MedicalCase extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'medical_cases';

    protected $fillable = ['slug', 'image_media_id', 'reverse', 'sort_order', 'is_active'];

    protected $casts = [
        'reverse'    => 'boolean',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /*
     * NOTE: per-locale `slug` lives in the translation table (medical_case_translations.slug)
     * and is `$fillable` on MedicalCaseTranslation, but it is intentionally NOT listed in
     * $translatedAttributes here — that would make $case->slug resolve to the translation
     * accessor and break backward-compat lookups (seeders, hardcoded findActiveBySlug calls
     * that hit the parent column). Access per-locale slug via $case->translate($locale)->slug.
     */
    public array $translatedAttributes = [
        'title',
        'subtitle',
        'intro',
        'col1_items',
        'col2_items',
        'col3_body',
        'detail_content',
    ];

    public $translationModel = MedicalCaseTranslation::class;

    protected array $filterable = ['is_active'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }
}
