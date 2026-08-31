<?php

namespace Packages\Dental\Src\Repositories\Eloquent;

use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Dental\Src\Models\DentalService;
use Packages\Dental\Src\Repositories\Interfaces\DentalServiceRepositoryInterface;

class DentalServiceRepository extends BaseRepository implements DentalServiceRepositoryInterface
{
    public function getModel(): string
    {
        return DentalService::class;
    }

    public function findPublishedBySlug(string $slug, int $facilityId, string $locale): ?DentalService
    {
        return $this->model
            ->newQuery()
            ->published()
            ->where('dental_facility_id', $facilityId)
            ->with(['translations', 'facility.translations', 'iconMedia', 'videoPosterMedia'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }
}
