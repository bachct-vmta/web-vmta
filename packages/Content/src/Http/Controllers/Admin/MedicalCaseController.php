<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StoreMedicalCaseRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateMedicalCaseDetailRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateMedicalCaseRequest;
use Packages\Content\Src\Models\MedicalCase;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;
use Packages\Content\Src\Services\AchievementPageService;
use Packages\Core\Src\Traits\LogsAdminActivity;

class MedicalCaseController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly MedicalCaseRepositoryInterface $repo,
        private readonly AchievementPageService $page,
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route(admin_route_name('achievement.index'), ['#cases']);
    }

    public function create(): View
    {
        return view('content::admin.achievement.cases.form', [
            'case'    => new MedicalCase(),
            'isEdit'  => false,
        ]);
    }

    public function store(StoreMedicalCaseRequest $request): RedirectResponse
    {
        $case = $this->repo->createWithTranslations(
            $request->baseAttributes(),
            $request->translationsPayload(),
        );

        $this->page->invalidateCache();
        $this->logActivity('medical_case.created', $case);

        return redirect()
            ->route(admin_route_name('achievement.index'))
            ->with('status', __('content::achievement.cases.created'));
    }

    public function edit(MedicalCase $case): View
    {
        $case->load(['translations', 'image']);

        return view('content::admin.achievement.cases.form', [
            'case'   => $case,
            'isEdit' => true,
        ]);
    }

    public function update(MedicalCase $case, UpdateMedicalCaseRequest $request): RedirectResponse
    {
        $this->repo->updateWithTranslations(
            $case,
            $request->baseAttributes(),
            $request->translationsPayload(),
        );

        $this->page->invalidateCache();
        $this->logActivity('medical_case.updated', $case);

        return redirect()
            ->route(admin_route_name('achievement.index'))
            ->with('status', __('content::achievement.cases.updated'));
    }

    public function editDetail(MedicalCase $case): View
    {
        $case->load(['translations', 'image']);

        return view('content::admin.achievement.cases.detail', [
            'case' => $case,
        ]);
    }

    public function updateDetail(MedicalCase $case, UpdateMedicalCaseDetailRequest $request): RedirectResponse
    {
        $this->repo->updateDetailContent($case, $request->translationsPayload());

        $this->page->invalidateCache();
        $this->logActivity('medical_case.detail_updated', $case);

        return redirect()
            ->route(admin_route_name('achievement.cases.detail.edit'), $case)
            ->with('status', __('content::achievement.cases.form.detail_saved'));
    }

    public function destroy(MedicalCase $case): RedirectResponse
    {
        $this->logActivity('medical_case.deleted', $case);
        $this->repo->deleteCase($case);
        $this->page->invalidateCache();

        return redirect()
            ->route(admin_route_name('achievement.index'))
            ->with('status', __('content::achievement.cases.deleted'));
    }
}
