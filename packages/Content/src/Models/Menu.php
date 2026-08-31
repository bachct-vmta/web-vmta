<?php

namespace Packages\Content\Src\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Core\Src\Models\BaseModel;

class Menu extends BaseModel
{
    /**
     * Fixed set of menu slots the public site renders.
     * Slug → translation key under `content::content.menu.locations.*`.
     * Adding a new slot needs (a) a new entry here, (b) lang key in vi/en,
     * (c) corresponding `@include('content::public.partials.menu', ['location' => ...])`.
     */
    public const LOCATIONS = [
        'main_navigation' => 'main_navigation',
        'page_sidebar' => 'page_sidebar',
        'footer_1_navigation' => 'footer_1_navigation',
        'footer_2_navigation' => 'footer_2_navigation',
    ];

    protected $fillable = ['name', 'location', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected array $searchable = ['name', 'location'];

    protected array $filterable = ['location', 'is_active'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }
}
