<?php

namespace Packages\Inquiry\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\MediaFile;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;
use Packages\Inquiry\Src\Models\Translations\ContactSectionTranslation;

class ContactSection extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $table = 'contact_sections';

    protected $fillable = ['position', 'is_active', 'sort_order', 'image_1_media_id', 'map_embed'];

    protected $casts = [
        'position'   => ContactSectionPosition::class,
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Translatable text columns (per-locale). */
    public array $translatedAttributes = ['title', 'subtitle', 'body', 'extra', 'items'];

    public $translationModel = ContactSectionTranslation::class;

    protected array $filterable = ['is_active', 'position'];

    public function image1(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_1_media_id');
    }
}
