<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class SpecialtyRepository extends BaseRepository implements SpecialtyRepositoryInterface
{
    public function getModel(): string
    {
        return Specialty::class;
    }

    public function findBySlug(string $slug, string $locale): ?Specialty
    {
        return $this->model
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Specialty
    {
        return $this->model
            ->with(['translations', 'heroMedia', 'introImage', 'coverMedia', 'partners.translations'])
            ->where('is_active', true)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function allActive(): Collection
    {
        return $this->model
            ->with(['translations', 'heroMedia', 'coverMedia'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
