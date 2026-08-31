<?php

namespace Packages\Content\Src\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Packages\Content\Src\Models\Translations\MenuItemTranslation;
use Packages\Core\Src\Models\BaseModel;

class MenuItem extends BaseModel implements TranslatableContract
{
    use Translatable;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'link_type',
        'target_type',
        'target_id',
        'icon',
        'css_class',
        'open_new_tab',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'open_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public array $translatedAttributes = ['label', 'url'];

    public $translationModel = MenuItemTranslation::class;

    protected array $filterable = ['menu_id', 'parent_id', 'is_active'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
