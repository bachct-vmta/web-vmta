<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class TourPackageRepository extends BaseRepository implements TourPackageRepositoryInterface
{
    public function getModel(): string
    {
        return TourPackage::class;
    }

    public function findPublishedBySlug(string $slug, string $locale): ?TourPackage
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'destinations.translations', 'partner.translations'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function paginatePublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->published()
            ->with(['translations', 'destinations.translations'])
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function suggestRelated(?int $destinationId, int $limit = 5): Collection
    {
        $query = $this->model->newQuery()->published()->with('translations');

        if ($destinationId !== null) {
            $query->whereHas('destinations', fn ($q) => $q->where('destinations.id', $destinationId));
        }

        return $query->latest('published_at')->limit($limit)->get();
    }
}
