<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Services\FilterService;

/**
 * Public "Mạng lưới Liên Minh" (Partner network) page.
 *
 * URL: /{locale}/mang-luoi (list) · /{locale}/mang-luoi/{slug} (detail)
 * Partner is intentionally outside Scout — search lives on the unified /tim-kiem (Slice C2).
 * Network listing filters by type (hospital/hotel/travel) + destination + specialty.
 */
class PartnerController extends Controller
{
    public function __construct(private readonly FilterService $filter) {}

    public function index(): View
    {
        SEOTools::setTitle((string) __('catalog::public.partners.meta_title'));
        SEOTools::setDescription((string) __('catalog::public.partners.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.partners.index', [
            'partners' => $this->filter->filterPartners(24),
            'destinations' => Destination::with('translations')->where('is_active', true)->orderBy('sort_order')->get(),
            'specialties' => Specialty::with('translations')->where('is_active', true)->orderBy('sort_order')->get(),
            'types' => Partner::TYPES,
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $partner = Partner::with(['translations', 'destination.translations', 'specialties.translations'])
            ->where('is_active', true)
            ->whereHas('translations', fn ($q) => $q->where('locale', $locale)->where('slug', $slug))
            ->first();

        abort_if($partner === null, 404);

        $translation = $partner->translate($locale) ?? $partner->translations->first();

        SEOTools::setTitle($translation->name);
        if (! empty($translation->short_description)) {
            SEOTools::setDescription(Str::limit(strip_tags((string) $translation->short_description), 160));
        }
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.partners.show', [
            'partner' => $partner,
            'translation' => $translation,
        ]);
    }
}
