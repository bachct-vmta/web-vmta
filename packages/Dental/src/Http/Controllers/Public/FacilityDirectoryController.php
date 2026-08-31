<?php

namespace Packages\Dental\Src\Http\Controllers\Public;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Dental\Src\Repositories\Interfaces\DentalCategoryRepositoryInterface;

class FacilityDirectoryController extends Controller
{
    public function __construct(private readonly DentalCategoryRepositoryInterface $categories) {}

    public function index(): View
    {
        $locale = app()->getLocale();

        return view('dental::public.facilities', [
            'categories' => $this->categories->publishedWithFacilities($locale),
            'breadcrumbs' => [
                ['label' => __('dental::public.breadcrumb.products')],
                ['label' => __('dental::public.breadcrumb.dental')],
            ],
        ]);
    }
}
