<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Repositories\Interfaces\ServiceRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    public function getModel(): string
    {
        return Service::class;
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Service
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'specialties.translations', 'destinations.translations', 'partner.translations'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'specialties.translations', 'destinations.translations'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function suggestRelated(?int $specialtyId, ?int $destinationId, int $limit = 5): Collection
    {
        $query = $this->model->newQuery()->published()->with('translations');

        if ($specialtyId !== null) {
            $query->whereHas('specialties', fn ($q) => $q->where('specialties.id', $specialtyId));
        } elseif ($destinationId !== null) {
            $query->whereHas('destinations', fn ($q) => $q->where('destinations.id', $destinationId));
        }

        return $query->latest('published_at')->limit($limit)->get();
    }
}
