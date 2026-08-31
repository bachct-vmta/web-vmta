<?php

namespace Packages\Dental\Src\Http\Controllers\Public;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Dental\Src\Repositories\Interfaces\DentalFacilityRepositoryInterface;

class FacilityController extends Controller
{
    public function __construct(private readonly DentalFacilityRepositoryInterface $facilities) {}

    public function show(string $facility): View
    {
        $locale = app()->getLocale();
        $model = $this->facilities->findPublishedBySlug($facility, $locale);

        abort_if($model === null, 404);

        $translation = $model->translate($locale) ?? $model->translations->first();

        return view('dental::public.facility', [
            'facility' => $model,
            'translation' => $translation,
            'services' => $model->services,
            'certificates' => $model->certificateMedia(),
            'breadcrumbs' => [
                ['label' => __('dental::public.breadcrumb.products')],
                ['label' => __('dental::public.breadcrumb.dental'), 'url' => route("site.{$locale}.dental.index")],
                ['label' => $translation->name],
            ],
        ]);
    }
}
