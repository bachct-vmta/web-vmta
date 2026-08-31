<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Http\Requests\Admin\UpdateAllianceSectionRequest;
use Packages\Content\Src\Repositories\Interfaces\AllianceSectionRepositoryInterface;
use Packages\Content\Src\Services\AlliancePageService;
use Packages\Core\Src\Traits\LogsAdminActivity;

class AllianceSectionController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly AllianceSectionRepositoryInterface $repo,
        private readonly AlliancePageService $alliancePage,
    ) {}

    public function index(): View
    {
        return view('content::admin.alliance.index', [
            'sections'  => $this->repo->getAllSectionsWithAllTranslations(),
            'positions' => AllianceSectionPosition::cases(),
        ]);
    }

    public function update(string $position, UpdateAllianceSectionRequest $request): RedirectResponse
    {
        $positionEnum = $request->position();
        $validated    = $request->validated();

        $translations = $validated['translations'] ?? [];
        foreach ($translations as $locale => $payload) {
            $this->alliancePage->validateItems($positionEnum, $payload['items'] ?? null);
        }

        $section = $this->repo->upsertSection($positionEnum, [], $translations);
        $this->alliancePage->invalidateCache();

        $this->logActivity('alliance_section.updated', $section, [
            'new' => ['position' => $positionEnum->value, 'translations' => array_keys($translations)],
        ]);

        return back()
            ->with('status', __('content::content.admin.alliance.updated'))
            ->withInput(['_active_tab' => $positionEnum->value]);
    }
}
