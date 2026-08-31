<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Content\Src\Models\Concerns\HasCoverMedia;
use Packages\Content\Src\Models\Translations\PostTranslation;
use Packages\Core\Src\Models\BaseModel;
use Packages\Core\Src\Models\User;

class Post extends BaseModel implements TranslatableContract
{
    use HasCoverMedia;
    use Translatable;

    protected $fillable = [
        'category_id',
        'author_id',
        'cover_media_id',
        'status',
        'is_featured',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
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

    public $translationModel = PostTranslation::class;

    protected array $searchable = ['translations.title', 'translations.slug'];

    protected array $filterable = ['status', 'category_id', 'is_featured'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
