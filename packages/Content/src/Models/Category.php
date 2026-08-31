<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Content\Src\Models\Translations\CategoryTranslation;
use Packages\Core\Src\Models\BaseModel;

class Category extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['name', 'slug', 'description'];

    public $translationModel = CategoryTranslation::class;

    protected array $filterable = ['parent_id', 'is_active'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
