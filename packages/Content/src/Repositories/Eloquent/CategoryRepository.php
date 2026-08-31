<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function getModel(): string
    {
        return Category::class;
    }

    public function findBySlug(string $slug, string $locale): ?Category
    {
        return $this->model
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function tree(): Collection
    {
        return $this->model
            ->with(['translations', 'children.translations'])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
