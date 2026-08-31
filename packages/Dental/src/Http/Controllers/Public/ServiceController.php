<?php

namespace Packages\Dental\Src\Http\Controllers\Public;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Dental\Src\Repositories\Interfaces\DentalFacilityRepositoryInterface;
use Packages\Dental\Src\Repositories\Interfaces\DentalServiceRepositoryInterface;
use Packages\Dental\Src\Services\LatestNewsProvider;

class ServiceController extends Controller
{
    public function __construct(
        private readonly DentalFacilityRepositoryInterface $facilities,
        private readonly DentalServiceRepositoryInterface $services,
        private readonly LatestNewsProvider $news,
    ) {}

    public function show(string $facility, string $service): View
    {
        $locale = app()->getLocale();
        $facilityModel = $this->facilities->findPublishedBySlug($facility, $locale);

        abort_if($facilityModel === null, 404);

        // Chặn URL ghép sai: dịch vụ phải thuộc đúng cơ sở trên đường dẫn
        $model = $this->services->findPublishedBySlug($service, $facilityModel->id, $locale);

        abort_if($model === null, 404);

        $translation = $model->translate($locale) ?? $model->translations->first();
        $facilityTranslation = $facilityModel->translate($locale) ?? $facilityModel->translations->first();

        return view('dental::public.service', [
            'service' => $model,
            'translation' => $translation,
            'facility' => $facilityModel,
            'facilityTranslation' => $facilityTranslation,
            'heroTitle' => $translation->hero_h1 ?: $translation->title,
            'posts' => $this->news->forSidebar($locale),
            'breadcrumbs' => [
                ['label' => __('dental::public.breadcrumb.products')],
                ['label' => __('dental::public.breadcrumb.dental'), 'url' => route("site.{$locale}.dental.index")],
                ['label' => $facilityTranslation->name, 'url' => route("site.{$locale}.dental.facility", ['facility' => $facilityTranslation->slug])],
                ['label' => $translation->title],
            ],
        ]);
    }
}
