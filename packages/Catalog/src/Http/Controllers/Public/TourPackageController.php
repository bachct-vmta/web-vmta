<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Models\Destination;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;
use Packages\Catalog\Src\Services\FilterService;

/**
 * Public TourPackage list + detail.
 *
 * URL: /{locale}/tour (list) · /{locale}/tour/{slug} (detail)
 * Filters: ?filter[destination]=...&filter[price_max]=...&sort=duration_days
 */
class TourPackageController extends Controller
{
    public function __construct(
        private readonly TourPackageRepositoryInterface $tours,
        private readonly FilterService $filter,
    ) {}

    public function index(): View
    {
        SEOTools::setTitle((string) __('catalog::public.tours.meta_title'));
        SEOTools::setDescription((string) __('catalog::public.tours.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.tours.index', [
            'tours' => $this->filter->filterTours(12),
            'destinations' => Destination::with('translations')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $tour = $this->tours->findPublishedBySlug($slug, $locale);

        abort_if($tour === null, 404);

        $translation = $tour->translate($locale) ?? $tour->translations->first();

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

        return view('catalog::public.tours.show', [
            'tour' => $tour->load(['translations', 'partner.translations', 'destinations.translations']),
            'translation' => $translation,
        ]);
    }
}
