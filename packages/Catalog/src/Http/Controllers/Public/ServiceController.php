<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Repositories\Interfaces\ServiceRepositoryInterface;
use Packages\Catalog\Src\Services\FilterService;

/**
 * Public Service (gói khám) list + detail.
 *
 * URL: /{locale}/dich-vu (list) · /{locale}/dich-vu/{slug} (detail)
 * Filters via Spatie QueryBuilder (?filter[specialty]=...&filter[destination]=...&filter[price_max]=...).
 */
class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceRepositoryInterface $services,
        private readonly FilterService $filter,
    ) {}

    public function index(): View
    {
        SEOTools::setTitle((string) __('catalog::public.services.meta_title'));
        SEOTools::setDescription((string) __('catalog::public.services.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.services.index', [
            'services' => $this->filter->filterServices(12),
            'specialties' => Specialty::with('translations')->where('is_active', true)->orderBy('sort_order')->get(),
            'destinations' => Destination::with('translations')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $service = $this->services->findPublishedBySlug($slug, $locale);

        abort_if($service === null, 404);

        $translation = $service->translate($locale) ?? $service->translations->first();

        SEOTools::setTitle($translation->seo_title ?: $translation->title);
        if (! empty($translation->seo_description)) {
            SEOTools::setDescription($translation->seo_description);
        } elseif (! empty($translation->excerpt)) {
            SEOTools::setDescription(Str::limit(strip_tags((string) $translation->excerpt), 160));
        }
        SEOTools::opengraph()->setUrl(url()->current());
        if (! empty($translation->seo_og_image)) {
            SEOTools::opengraph()->addImage($translation->seo_og_image);
        }

        return view('catalog::public.services.show', [
            'service' => $service->load(['translations', 'partner.translations', 'specialties.translations', 'destinations.translations']),
            'translation' => $translation,
        ]);
    }
}
