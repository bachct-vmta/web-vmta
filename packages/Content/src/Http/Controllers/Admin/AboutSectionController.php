<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Http\Requests\Admin\UpdateAboutSectionRequest;
use Packages\Content\Src\Repositories\Interfaces\AboutSectionRepositoryInterface;
use Packages\Content\Src\Services\AboutPageService;
use Packages\Core\Src\Traits\LogsAdminActivity;

class AboutSectionController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly AboutSectionRepositoryInterface $repo,
        private readonly AboutPageService $aboutPage,
    ) {}

    public function index(): View
    {
        return view('content::admin.about.index', [
            'sections'  => $this->repo->getAllSectionsWithAllTranslations(),
            // core_values is managed on its own sidebar page (shared by Home & About),
            // so it is excluded from the About page's tab list.
            'positions' => array_values(array_filter(
                AboutSectionPosition::cases(),
                fn ($p) => $p !== AboutSectionPosition::CoreValues,
            )),
        ]);
    }

    public function update(string $position, UpdateAboutSectionRequest $request): RedirectResponse
    {
        $positionEnum = $request->position();
        $validated    = $request->validated();

        // When a position accepts items, a deleted-to-empty repeater submits no items[]
        // key at all. Force it to [] so the stored translation column is cleared rather
        // than keeping the previously saved rows.
        $acceptsItems = $positionEnum->expectedItemsRange() !== null;

        $translations = $validated['translations'] ?? [];
        foreach ($translations as $locale => &$payload) {
            if ($acceptsItems && ! array_key_exists('items', $payload)) {
                $payload['items'] = [];
            }
            $this->aboutPage->validateItems($positionEnum, $payload['items'] ?? null);
        }
        unset($payload);

        // Extract base attrs (non-translatable section columns) from validated payload.
        $base = [];
        foreach (['image_1_media_id', 'image_2_media_id'] as $col) {
            if (array_key_exists($col, $validated)) {
                $base[$col] = $validated[$col] ?: null;
            }
        }

        $section = $this->repo->upsertSection($positionEnum, $base, $translations);
        $this->aboutPage->invalidateCache();

        $this->logActivity('about_section.updated', $section, [
            'new' => ['position' => $positionEnum->value, 'translations' => array_keys($translations)],
        ]);

        return back()
            ->with('status', __('content::admin.about.updated'))
            ->withInput(['_active_tab' => $positionEnum->value]);
    }
}
