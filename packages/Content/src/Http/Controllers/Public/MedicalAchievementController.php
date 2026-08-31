<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;
use Packages\Content\Src\Services\AchievementPageService;

class MedicalAchievementController extends Controller
{
    public function __construct(
        private readonly AchievementPageService $page,
        private readonly MedicalCaseRepositoryInterface $casesRepo,
    ) {}

    public function __invoke(): View
    {
        $locale = app()->getLocale();
        $data = $this->page->getRenderData($locale);
        $cases = $this->page->getCasesForRender($locale);

        $sections = $data['sections']->keyBy(fn ($s) => $s->position->value);
        $hero = $sections->get(AchievementSectionPosition::Hero->value);

        SEOTools::setTitle((string) __('content::public.achievement_meta_title'));
        SEOTools::setDescription(
            'Những thành tựu y khoa tiêu biểu tại Việt Nam, từ ghép tạng phức tạp đến y học cá thể hóa.'
        );
        SEOTools::opengraph()->setUrl(url()->current());
        SEOTools::opengraph()->addImage(
            $hero?->image?->permalink
                ?? asset('images/medical-achievements/hero-operating-room.jpg')
        );

        return view('content::public.pages.thanh-tuu-y-khoa', [
            'sections' => $sections,
            'cases'    => $cases,
        ]);
    }

    public function showHeartLung(): View
    {
        $locale = app()->getLocale();
        $case = $this->casesRepo->findActiveBySlug('ghep-dong-thoi-tim-phoi', $locale);

        abort_if($case === null, 404);

        $translation = $case->translate($locale) ?? $case->translations->first();
        abort_if($translation === null, 404);

        SEOTools::setTitle($translation->title);
        if (! empty($translation->subtitle)) {
            SEOTools::setDescription($translation->subtitle);
        }
        SEOTools::opengraph()->setUrl(url()->current());
        SEOTools::opengraph()->addImage(asset('images/heart-lung-transplant/hero-bg.jpg'));

        return view('content::public.pages.medical-case-detail', [
            'case'        => $case,
            'translation' => $translation,
        ]);
    }
}
