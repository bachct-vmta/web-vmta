<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Repositories\Interfaces\ComboRepositoryInterface;
use Packages\Catalog\Src\Services\FilterService;

/**
 * Public Combo list + detail.
 *
 * URL: /{locale}/combo (list) · /{locale}/combo/{slug} (detail)
 * Combos bundle Services + TourPackages — detail eager-loads both pivots for itinerary display.
 */
class ComboController extends Controller
{
    public function __construct(
        private readonly ComboRepositoryInterface $combos,
        private readonly FilterService $filter,
    ) {}

    public function index(): View
    {
        SEOTools::setTitle((string) __('catalog::public.combos.meta_title'));
        SEOTools::setDescription((string) __('catalog::public.combos.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.combos.index', [
            'combos' => $this->filter->filterCombos(12),
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $combo = $this->combos->findPublishedBySlug($slug, $locale);

        abort_if($combo === null, 404);

        $translation = $combo->translate($locale) ?? $combo->translations->first();

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

        // Only load PUBLISHED bundled items — never expose draft children to the public
        // detail page (their slugs would 404 and titles would leak from drafts).
        $combo->load([
            'translations',
            'services' => fn ($q) => $q->published(),
            'services.translations',
            'tourPackages' => fn ($q) => $q->published(),
            'tourPackages.translations',
        ]);

        return view('catalog::public.combos.show', [
            'combo' => $combo,
            'translation' => $translation,
        ]);
    }
}
