<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;

class PageController extends Controller
{
    public function __construct(private readonly PageRepositoryInterface $repository) {}

    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $page = $this->repository->findPublishedBySlug($slug, $locale);

        abort_if($page === null, 404);

        $translation = $page->translate($locale) ?? $page->translations->first();

        SEOTools::setTitle($translation->seo_title ?: $translation->title);
        if (! empty($translation->seo_description)) {
            SEOTools::setDescription($translation->seo_description);
        } elseif (! empty($translation->excerpt)) {
            SEOTools::setDescription(Str::limit(strip_tags($translation->excerpt), 160));
        }
        SEOTools::opengraph()->setUrl(url()->current());
        if (! empty($translation->seo_og_image)) {
            SEOTools::opengraph()->addImage($translation->seo_og_image);
        }

        return view('content::public.pages.show', [
            'page' => $page,
            'translation' => $translation,
        ]);
    }
}
