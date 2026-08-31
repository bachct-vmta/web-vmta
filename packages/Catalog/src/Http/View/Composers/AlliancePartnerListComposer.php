<?php

namespace Packages\Catalog\Src\Http\View\Composers;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Packages\Catalog\Src\Models\Partner;

/**
 * Feeds the alliance page partner grid without Content depending on Catalog.
 * Only partners with a logo are shown — the grid renders logos, nothing else.
 */
class AlliancePartnerListComposer
{
    public function compose(View $view): void
    {
        $view->with('partnerGroups', $this->groupedPartners());
    }

    private function groupedPartners(): Collection
    {
        $byType = Partner::query()
            ->where('is_active', true)
            ->whereNotNull('logo_media_id')
            ->with(['logoMedia', 'translations'])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');

        return collect(Partner::TYPES)
            ->mapWithKeys(fn (string $type) => [$type => $byType->get($type, collect())])
            ->reject(fn (Collection $partners) => $partners->isEmpty());
    }
}
