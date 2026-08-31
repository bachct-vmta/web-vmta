<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Repositories\Interfaces\DestinationRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class DestinationRepository extends BaseRepository implements DestinationRepositoryInterface
{
    public function getModel(): string
    {
        return Destination::class;
    }

    public function findBySlug(string $slug, string $locale): ?Destination
    {
        return $this->model
            ->with('translations')
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function allActive(): Collection
    {
        return $this->model
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
