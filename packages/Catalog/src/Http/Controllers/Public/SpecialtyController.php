<?php

namespace Packages\Catalog\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;
use Packages\Core\Src\Models\MediaFile;

/**
 * Public Specialty (Chuyên khoa) hub + detail.
 *
 * URL: /{locale}/chuyen-khoa (hub) · /{locale}/chuyen-khoa/{slug} (detail)
 *
 * Hub renders all active specialties as cards (sorted by sort_order).
 * Detail renders structured landing template (hero/intro/strengths/hospitals/lead).
 */
class SpecialtyController extends Controller
{
    public function __construct(
        private readonly SpecialtyRepositoryInterface $specialties,
    ) {}

    public function index(): View
    {
        SEOTools::setTitle((string) __('catalog::public.specialties.meta_title'));
        SEOTools::setDescription((string) __('catalog::public.specialties.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('catalog::public.specialties.index', [
            'specialties' => $this->specialties->allActive(),
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $specialty = $this->specialties->findPublishedBySlug($slug, $locale);

        abort_if($specialty === null, 404);

        $translation = $specialty->translate($locale) ?? $specialty->translations->first();

        $heroH1 = $translation->hero_h1 ?: mb_strtoupper((string) $translation->name);
        $breadcrumb = $translation->breadcrumb_label ?: $translation->name;

        $seoTitle = $translation->seo_title ?: ($heroH1.' | '.config('app.name'));
        SEOTools::setTitle($seoTitle);
        $seoDescription = $translation->seo_description
            ?: ($translation->intro_lead ?: Str::limit(strip_tags((string) ($translation->intro_body_html ?? $translation->description)), 160));
        if ($seoDescription) {
            SEOTools::setDescription((string) $seoDescription);
        }
        SEOTools::opengraph()->setUrl(url()->current());
        $ogImage = $translation->seo_og_image
            ?: ($specialty->heroMedia?->permalink ?? $specialty->coverMedia?->permalink);
        if ($ogImage) {
            SEOTools::opengraph()->addImage(asset(ltrim($ogImage, '/')));
        }

        $mediaMap = $this->resolveJsonMediaIds([
            $translation->strengths_json ?? [],
            $translation->hospitals_json ?? [],
        ]);

        return view('catalog::public.specialties.show', [
            'specialty' => $specialty,
            'translation' => $translation,
            'heroH1' => $heroH1,
            'breadcrumb' => $breadcrumb,
            'mediaMap' => $mediaMap,
        ]);
    }

    /**
     * Single-query lookup for media_files referenced inside JSON columns.
     * Prevents N+1 when blade iterates strengths/hospitals cards.
     */
    private function resolveJsonMediaIds(array $jsonArrays): Collection
    {
        $ids = collect($jsonArrays)
            ->flatten(1)
            ->pluck('image_media_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return MediaFile::whereIn('id', $ids)->get()->keyBy('id');
    }
}
