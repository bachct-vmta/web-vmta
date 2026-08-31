<?php

namespace Packages\Catalog\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Repositories\Interfaces\PartnerRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class PartnerRepository extends BaseRepository implements PartnerRepositoryInterface
{
    public function getModel(): string
    {
        return Partner::class;
    }

    public function findBySlug(string $slug, string $locale): ?Partner
    {
        return $this->model
            ->with(['translations', 'destination.translations', 'specialties.translations'])
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();
    }

    public function paginateActive(?string $type, ?int $destinationId, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['translations', 'destination.translations'])
            ->where('is_active', true);

        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }

        if ($destinationId !== null) {
            $query->where('destination_id', $destinationId);
        }

        return $query->orderBy('sort_order')->paginate($perPage);
    }
}
