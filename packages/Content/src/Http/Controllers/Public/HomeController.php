<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Content\Src\Services\AboutPageService;
use Packages\Content\Src\Services\HomePageService;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomePageService $homePage,
        private readonly AboutPageService $aboutPage,
        private readonly PostRepositoryInterface $posts,
    ) {}

    public function __invoke(): View
    {
        $locale = app()->getLocale();

        SEOTools::setTitle((string) __('content::public.home_meta_title'));
        SEOTools::setDescription((string) __('content::public.home_meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        $renderData = $this->homePage->getRenderData($locale);

        // Shared "Giá trị cốt lõi" block — single source is the About page's
        // core_values section, reused here so both pages stay in sync.
        $coreValuesSection = $this->aboutPage->getRenderData($locale)['sections']
            ->firstWhere(fn ($s) => $s->position->value === 'core_values');

        return view('content::public.pages.home', [
            'sections' => $renderData['sections'],
            'locale' => $locale,
            'latestPosts' => $this->posts->latest(3),
            'coreValuesSection' => $coreValuesSection,
        ]);
    }
}
