<?php

namespace Packages\Inquiry\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Core\Src\Traits\LogsAdminActivity;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;
use Packages\Inquiry\Src\Http\Requests\Admin\UpdateContactSectionRequest;
use Packages\Inquiry\Src\Repositories\Interfaces\ContactSectionRepositoryInterface;
use Packages\Inquiry\Src\Services\ContactPageService;

class ContactSectionController extends Controller
{
    use LogsAdminActivity;

    public function __construct(
        private readonly ContactSectionRepositoryInterface $repo,
        private readonly ContactPageService $contactPage,
    ) {}

    public function index(): View
    {
        return view('inquiry::admin.contact.index', [
            'sections'  => $this->repo->getAllSectionsWithAllTranslations(),
            'positions' => ContactSectionPosition::cases(),
        ]);
    }

    public function update(string $position, UpdateContactSectionRequest $request): RedirectResponse
    {
        $positionEnum = $request->position();
        $validated    = $request->validated();

        $translations = $validated['translations'] ?? [];
        foreach ($translations as $locale => $payload) {
            $this->contactPage->validateItems($positionEnum, $payload['items'] ?? null);
        }

        // Extract non-translatable section columns from the validated payload.
        $base = [];
        if (array_key_exists('image_1_media_id', $validated)) {
            $base['image_1_media_id'] = $validated['image_1_media_id'] ?: null;
        }
        if (array_key_exists('map_embed', $validated)) {
            $base['map_embed'] = $validated['map_embed'] ?: null;
        }

        $section = $this->repo->upsertSection($positionEnum, $base, $translations);
        $this->contactPage->invalidateCache();

        $this->logActivity('contact_section.updated', $section, [
            'new' => ['position' => $positionEnum->value, 'translations' => array_keys($translations)],
        ]);

        return back()
            ->with('status', __('inquiry::inquiry.admin.contact.updated'))
            ->withInput(['_active_tab' => $positionEnum->value]);
    }
}
