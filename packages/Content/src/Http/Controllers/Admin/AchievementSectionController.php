<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use InvalidArgumentException;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Http\Requests\Admin\UpdateAchievementSectionRequest;
use Packages\Content\Src\Repositories\Interfaces\AchievementSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;
use Packages\Content\Src\Services\AchievementPageService;
use Packages\Core\Src\Traits\LogsAdminActivity;

class AchievementSectionController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly AchievementSectionRepositoryInterface $repo,
        private readonly MedicalCaseRepositoryInterface $cases,
        private readonly AchievementPageService $page,
    ) {}

    public function index(): View
    {
        return view('content::admin.achievement.index', [
            'sections'  => $this->repo->getAllSectionsWithAllTranslations(),
            'positions' => AchievementSectionPosition::cases(),
            'cases'     => $this->cases->getAllForAdmin(),
        ]);
    }

    public function update(string $position, UpdateAchievementSectionRequest $request): RedirectResponse
    {
        $positionEnum = $request->position();
        $validated = $request->validated();

        $base = [];
        if (array_key_exists('image_media_id', $validated)) {
            $base['image_media_id'] = $validated['image_media_id'] ?: null;
        }

        $translations = $validated['translations'] ?? [];

        try {
            foreach ($translations as $payload) {
                $this->page->validateItems($positionEnum, $payload['items'] ?? null);
            }
        } catch (InvalidArgumentException $e) {
            return back()
                ->withErrors(['items' => $e->getMessage()])
                ->withInput();
        }

        $section = $this->repo->upsertSection($positionEnum, $base, $translations);
        $this->page->invalidateCache();

        $this->logActivity('achievement_section.updated', $section, [
            'new' => ['position' => $positionEnum->value, 'translations' => array_keys($translations)],
        ]);

        return back()
            ->with('status', __('content::achievement.updated'))
            ->withInput(['_active_tab' => $positionEnum->value]);
    }
}
