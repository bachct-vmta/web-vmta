<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Repositories\Interfaces\ComboRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class ComboRepository extends BaseRepository implements ComboRepositoryInterface
{
    public function getModel(): string
    {
        return Combo::class;
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Combo
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'services.translations', 'tourPackages.translations'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'services.translations', 'tourPackages.translations'])
            ->latest('published_at')
            ->paginate($perPage);
    }
}
