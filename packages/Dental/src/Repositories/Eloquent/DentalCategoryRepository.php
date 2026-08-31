<?php

namespace Packages\Dental\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Repositories\Interfaces\DentalCategoryRepositoryInterface;

class DentalCategoryRepository extends BaseRepository implements DentalCategoryRepositoryInterface
{
    public function getModel(): string
    {
        return DentalCategory::class;
    }

    public function publishedWithFacilities(string $locale): Collection
    {
        return $this->model
            ->newQuery()
            ->published()
            ->sorted()
            ->with([
                'translations',
                'facilities' => fn ($q) => $q->published()->sorted()->with(['translations', 'coverMedia']),
            ])
            ->get();
    }
}
