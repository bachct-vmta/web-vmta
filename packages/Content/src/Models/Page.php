<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Models\Concerns\HasCoverMedia;
use Packages\Content\Src\Models\Translations\PageTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\User;

class Page extends BaseModel implements TranslatableContract
{
    use HasCoverMedia;
    use Translatable;

    protected $fillable = [
        'status',
        'template',
        'cover_media_id',
        'author_id',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public array $translatedAttributes = [
        'title',
        'slug',
        'excerpt',
        'body',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    public $translationModel = PageTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['status', 'author_id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
