<?php

namespace Packages\Catalog\Src\Services;

use Illuminate\Support\Collection;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Catalog\Src\Repositories\Interfaces\ServiceRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;

/**
 * Cross-entity Scout search merging Service + TourPackage + Combo results.
 * Partner is intentionally excluded from this index (see Partner model docblock).
 * Slice A wires the merge primitive; ranking + relevance tuning land in Slice C.
 */
class SearchService
{
    public function __construct(
        private readonly ServiceRepositoryInterface $services,
        private readonly TourPackageRepositoryInterface $tours,
    ) {}

    /**
     * @return Collection<int, array{type: string, model: object}>
     */
    public function searchAll(string $query, int $perType = 10): Collection
    {
        $query = trim($query);
        if ($query === '') {
            return collect();
        }

        $services = Service::search($query)->take($perType)->get()
            ->map(fn ($m) => ['type' => 'service', 'model' => $m]);
        $tours = TourPackage::search($query)->take($perType)->get()
            ->map(fn ($m) => ['type' => 'tour_package', 'model' => $m]);
        $combos = Combo::search($query)->take($perType)->get()
            ->map(fn ($m) => ['type' => 'combo', 'model' => $m]);

        return collect([...$services, ...$tours, ...$combos]);
    }

    /**
     * Empty-state suggestion: top 5 related published items if main query returns 0.
     */
    public function suggest(?int $specialtyId, ?int $destinationId, int $limit = 5): Collection
    {
        $serviceSuggest = $this->services->suggestRelated($specialtyId, $destinationId, $limit)
            ->map(fn ($m) => ['type' => 'service', 'model' => $m]);

        if ($serviceSuggest->count() >= $limit) {
            return $serviceSuggest->take($limit);
        }

        $tourSuggest = $this->tours->suggestRelated($destinationId, $limit - $serviceSuggest->count())
            ->map(fn ($m) => ['type' => 'tour_package', 'model' => $m]);

        return collect([...$serviceSuggest, ...$tourSuggest])->take($limit);
    }
}
