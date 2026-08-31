<?php

namespace Packages\Dental\Src\Repositories\Eloquent;

use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Repositories\Interfaces\DentalFacilityRepositoryInterface;

class DentalFacilityRepository extends BaseRepository implements DentalFacilityRepositoryInterface
{
    public function getModel(): string
    {
        return DentalFacility::class;
    }

    public function findPublishedBySlug(string $slug, string $locale): ?DentalFacility
    {
        return $this->model
            ->newQuery()
            ->published()
            ->with([
                'translations',
                'category.translations',
                'coverMedia',
                'services' => fn ($q) => $q->published()->sorted()->with(['translations', 'iconMedia']),
            ])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }
}
