<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Packages\Content\Src\Services\AlliancePageService;

class AllianceController extends Controller
{
    public function __construct(private readonly AlliancePageService $alliancePage) {}

    public function __invoke(Request $request)
    {
        SEOTools::setTitle((string) __('content::public.alliance.meta_title'));
        SEOTools::setDescription((string) __('content::public.alliance.meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        $data = $this->alliancePage->getRenderData();

        $sections = $data['sections']->keyBy(fn ($s) => $s->position->value);

        return view('content::public.pages.mang-luoi-lien-minh', [
            'heroSection'      => $sections->get('hero'),
            'overviewSection'  => $sections->get('overview'),
            'standardsSection' => $sections->get('standards'),
            'mapSection'       => $sections->get('map'),
            'joinFormSection'  => $sections->get('join_form'),
        ]);
    }
}
