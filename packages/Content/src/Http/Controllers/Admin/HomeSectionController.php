<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Http\Requests\Admin\UpdateHomeSectionRequest;
use Packages\Content\Src\Repositories\Interfaces\HomeSectionRepositoryInterface;
use Packages\Content\Src\Services\HomePageService;
use Packages\Core\Src\Traits\LogsAdminActivity;

class HomeSectionController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly HomeSectionRepositoryInterface $repo,
        private readonly HomePageService $homePage,
    ) {}

    public function index(): View
    {
        return view('content::admin.home.index', [
            'sections' => $this->repo->getAllSectionsWithAllTranslations(),
            // The Values (Giá trị cốt lõi) block now renders from the shared About
            // core_values source, so its Home tab is hidden to avoid confusion.
            'positions' => array_values(array_filter(
                HomeSectionPosition::cases(),
                fn ($p) => $p !== HomeSectionPosition::Values,
            )),
        ]);
    }

    public function update(string $position, UpdateHomeSectionRequest $request): RedirectResponse
    {
        $positionEnum = $request->position();
        $validated = $request->validated();

        $base = [];
        if (array_key_exists('image_media_id', $validated)) {
            $base['image_media_id'] = $validated['image_media_id'] ?: null;
        }
        if (array_key_exists('video_url', $validated)) {
            $base['video_url'] = $validated['video_url'] ?: null;
        }
        if (array_key_exists('marquee_active', $validated)) {
            $base['marquee_active'] = (bool) $validated['marquee_active'];
        }

        $translations = $validated['translations'] ?? [];
        foreach ($translations as $locale => &$payload) {
            $this->homePage->validateItems($positionEnum, $payload['items'] ?? null);
        }
        unset($payload);

        $section = $this->repo->upsertSection($positionEnum, $base, $translations);
        $this->homePage->invalidateCache();

        $this->logActivity('home_section.updated', $section, [
            'new' => ['position' => $positionEnum->value, 'translations' => array_keys($translations)],
        ]);

        return back()
            ->with('status', __('content::admin.home.updated'))
            ->withInput(['_active_tab' => $positionEnum->value]);
    }
}
