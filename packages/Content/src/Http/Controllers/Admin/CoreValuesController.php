<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Repositories\Interfaces\AboutSectionRepositoryInterface;

/**
 * Dedicated admin page for the shared "Giá trị cốt lõi" (Core Values) block.
 *
 * Core values live on the About page's `core_values` section (about_sections),
 * but are surfaced here as a standalone sidebar item because the same data is
 * rendered on both the Home page and the About page. Editing/saving reuses the
 * existing `about.update` endpoint via the `_fieldset-core_values` partial.
 */
class CoreValuesController extends Controller
{
    public function __construct(
        private readonly AboutSectionRepositoryInterface $repo,
    ) {}

    public function index(): View
    {
        $sections = $this->repo->getAllSectionsWithAllTranslations();

        return view('content::admin.core-values.index', [
            'position' => AboutSectionPosition::CoreValues,
            'section'  => $sections->firstWhere('position', AboutSectionPosition::CoreValues),
        ]);
    }
}
