<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Packages\Content\Src\Services\AboutPageService;

class AboutController extends Controller
{
    public function __construct(private readonly AboutPageService $aboutPage) {}

    public function __invoke(Request $request)
    {
        SEOTools::setTitle((string) __('content::public.about_meta_title'));
        SEOTools::opengraph()->setUrl(url()->current());

        $data = $this->aboutPage->getRenderData();

        $sections = $data['sections']->keyBy(fn ($s) => $s->position->value);

        return view('content::public.pages.gioi-thieu', [
            'heroSection'        => $sections->get('hero'),
            'whoAreSection'      => $sections->get('who_are'),
            'coreValuesSection'  => $sections->get('core_values'),
            'howWorksSection'    => $sections->get('how_works'),
            'differenceSection'  => $sections->get('difference'),
            'whyChooseSection'   => $sections->get('why_choose'),
            'startWithUsSection' => $sections->get('start_with_us'),
        ]);
    }
}
