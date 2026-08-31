<?php

namespace Packages\Catalog\Src\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\TourPackage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * URL-driven catalog filtering via Spatie QueryBuilder.
 * Example: ?filter[specialty]=tim-mach,da-lieu&filter[destination]=da-nang&filter[price_max]=2000
 */
class FilterService
{
    public function filterServices(int $perPage = 15): LengthAwarePaginator
    {
        $locale = app()->getLocale();
        $perPage = min(max($perPage, 1), 100);

        return QueryBuilder::for(Service::query()->published())
            ->allowedFilters(...[
                AllowedFilter::callback('specialty', function (Builder $q, $value) use ($locale) {
                    $slugs = is_array($value) ? $value : explode(',', (string) $value);
                    $q->whereHas('specialties.translations', fn ($t) => $t->where('locale', $locale)->whereIn('slug', $slugs));
                }),
                AllowedFilter::callback('destination', function (Builder $q, $value) use ($locale) {
                    $slugs = is_array($value) ? $value : explode(',', (string) $value);
                    $q->whereHas('destinations.translations', fn ($t) => $t->where('locale', $locale)->whereIn('slug', $slugs));
                }),
                AllowedFilter::callback('partner', fn (Builder $q, $value) => $q->where('partner_id', $value)),
                AllowedFilter::callback('price_min', fn (Builder $q, $value) => $q->where('price_from', '>=', $value)),
                AllowedFilter::callback('price_max', fn (Builder $q, $value) => $q->where('price_from', '<=', $value)),
                AllowedFilter::exact('is_featured'),
            ])
            ->allowedSorts(...['published_at', 'price_from', 'sort_order'])
            ->defaultSort('-published_at')
            ->with(['translations', 'specialties.translations', 'destinations.translations'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function filterTours(int $perPage = 15): LengthAwarePaginator
    {
        $locale = app()->getLocale();
        $perPage = min(max($perPage, 1), 100);

        return QueryBuilder::for(TourPackage::query()->published())
            ->allowedFilters(...[
                AllowedFilter::callback('destination', function (Builder $q, $value) use ($locale) {
                    $slugs = is_array($value) ? $value : explode(',', (string) $value);
                    $q->whereHas('destinations.translations', fn ($t) => $t->where('locale', $locale)->whereIn('slug', $slugs));
                }),
                AllowedFilter::callback('partner', fn (Builder $q, $value) => $q->where('partner_id', $value)),
                AllowedFilter::callback('price_min', fn (Builder $q, $value) => $q->where('price_from', '>=', $value)),
                AllowedFilter::callback('price_max', fn (Builder $q, $value) => $q->where('price_from', '<=', $value)),
                AllowedFilter::exact('is_featured'),
            ])
            ->allowedSorts(...['published_at', 'price_from', 'duration_days', 'sort_order'])
            ->defaultSort('-published_at')
            ->with(['translations', 'destinations.translations'])
            ->paginate($perPage)
            ->withQueryString();
    }

    public function filterCombos(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return QueryBuilder::for(Combo::query()->published())
            ->allowedFilters(...[
                AllowedFilter::callback('price_min', fn (Builder $q, $value) => $q->where('price_from', '>=', $value)),
                AllowedFilter::callback('price_max', fn (Builder $q, $value) => $q->where('price_from', '<=', $value)),
                AllowedFilter::callback('discount_min', fn (Builder $q, $value) => $q->where('discount_percent', '>=', $value)),
                AllowedFilter::exact('is_featured'),
            ])
            ->allowedSorts(...['published_at', 'price_from', 'discount_percent', 'sort_order'])
            ->defaultSort('-published_at')
            ->with(['translations', 'services.translations', 'tourPackages.translations'])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Partner network page — no Scout, simple SQL filter. type ∈ hospital|hotel|travel,
     * destination passed as locale-aware slug (matches the URL convention from Slice B).
     */
    public function filterPartners(int $perPage = 24): LengthAwarePaginator
    {
        $locale = app()->getLocale();
        $perPage = min(max($perPage, 1), 100);

        return QueryBuilder::for(Partner::query()->where('is_active', true))
            ->allowedFilters(...[
                AllowedFilter::exact('type'),
                AllowedFilter::callback('destination', function (Builder $q, $value) use ($locale) {
                    $slugs = is_array($value) ? $value : explode(',', (string) $value);
                    $q->whereHas('destination.translations', fn ($t) => $t->where('locale', $locale)->whereIn('slug', $slugs));
                }),
                AllowedFilter::callback('specialty', function (Builder $q, $value) use ($locale) {
                    $slugs = is_array($value) ? $value : explode(',', (string) $value);
                    $q->whereHas('specialties.translations', fn ($t) => $t->where('locale', $locale)->whereIn('slug', $slugs));
                }),
            ])
            ->allowedSorts(...['sort_order', 'created_at'])
            ->defaultSort('sort_order')
            ->with(['translations', 'destination.translations', 'specialties.translations'])
            ->paginate($perPage)
            ->withQueryString();
    }
}
