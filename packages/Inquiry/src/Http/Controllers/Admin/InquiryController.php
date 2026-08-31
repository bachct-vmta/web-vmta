<?php

namespace Packages\Inquiry\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Packages\Core\Src\Models\User;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Http\Requests\AddNoteRequest;
use Packages\Inquiry\Src\Http\Requests\AssignInquiryRequest;
use Packages\Inquiry\Src\Http\Requests\TransitionInquiryRequest;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Notifications\InquiryAssignedNotification;
use Packages\Inquiry\Src\Repositories\Interfaces\InquiryRepositoryInterface;
use Packages\Inquiry\Src\Tables\InquiryTable;

class InquiryController extends Controller
{
    public function __construct(private readonly InquiryRepositoryInterface $repository) {}

    public function index(Request $request, InquiryTable $table): View
    {
        $user = $request->user();
        $isCoordinator = $user
            && $user->hasPermission('inquiry.index')
            && ! $user->hasPermission('inquiry.assign');

        // Mirror InquiryTable::query() so the header toggle highlights the right pill.
        $mine = $user && (
            $request->boolean('mine')
            || ($isCoordinator && ! $request->has('mine'))
        );

        return view('inquiry::admin.inquiries.index', [
            'table' => $table,
            'mine' => $mine,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $inquiry = Inquiry::with(['assignee', 'notes.user', 'sourceRef'])->findOrFail($id);

        return view('inquiry::admin.inquiries.show', [
            'inquiry' => $inquiry,
            'allowedNext' => $inquiry->status->allowedNext(),
            'coordinators' => $this->loadCoordinators($request->user()?->id),
        ]);
    }

    public function assign(AssignInquiryRequest $request, int $id): RedirectResponse
    {
        $inquiry = Inquiry::findOrFail($id);
        $assigneeId = (int) $request->validated('assigned_to');

        $ok = $this->repository->assign($inquiry, $assigneeId, $request->user()->id);
        if (! $ok) {
            return back()->withErrors(['assigned_to' => __('inquiry::inquiry.admin.assigned_failure')]);
        }

        $assignee = User::find($assigneeId);
        if ($assignee !== null) {
            Notification::send($assignee, new InquiryAssignedNotification($inquiry->fresh()));
        }

        return back()->with('status', __('inquiry::inquiry.admin.assigned_success'));
    }

    public function transition(TransitionInquiryRequest $request, int $id): RedirectResponse
    {
        $inquiry = Inquiry::findOrFail($id);
        $target = InquiryStatus::from($request->validated('status'));

        $ok = $this->repository->transitionStatus(
            $inquiry,
            $target,
            $request->user()->id,
            $request->validated('note'),
        );

        if (! $ok) {
            return back()->withErrors([
                'status' => __('inquiry::inquiry.admin.forbidden_transition', [
                    'from' => $inquiry->status->value,
                    'to' => $target->value,
                ]),
            ]);
        }

        return back()->with('status', __('inquiry::inquiry.admin.transition_success'));
    }

    public function note(AddNoteRequest $request, int $id): RedirectResponse
    {
        $inquiry = Inquiry::findOrFail($id);
        $this->repository->addNote($inquiry, $request->user()->id, (string) $request->validated('body'));

        return back()->with('status', __('inquiry::inquiry.admin.note_added'));
    }

    /**
     * Active users eligible to be assigned an Inquiry: role has `inquiry.index` AND is not a
     * super user AND is not the acting Admin. Role permissions are stored as a flat JSON list
     * of strings (see AdminSeeder); filter in PHP rather than via JSON path to stay portable
     * across SQLite/MySQL/PG.
     */
    private function loadCoordinators(?int $excludeUserId = null)
    {
        return User::with('role')
            ->where('is_active', true)
            ->where('is_super_user', false)
            ->when($excludeUserId !== null, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->get()
            ->filter(function ($u) {
                if ($u->role === null) {
                    return false;
                }
                $perms = $u->role->permissions ?? [];

                return in_array('inquiry.index', $perms, true);
            })
            ->sortBy('name')
            ->values();
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('inquiry.delete'), 403);

        $this->repository->delete($id);

        return redirect()->route(admin_route_name('inquiries.index'))
            ->with('status', __('inquiry::inquiry.admin.deleted_success'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('inquiry.delete'), 403);

        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        if (! empty($ids)) {
            Inquiry::whereIn('id', $ids)->delete();
        }

        return back()->with('status', __('inquiry::inquiry.admin.bulk_deleted_success', ['count' => count($ids)]));
    }
}
