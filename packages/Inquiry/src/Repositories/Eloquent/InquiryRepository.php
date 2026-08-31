<?php

namespace Packages\Inquiry\Src\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Models\InquiryNote;
use Packages\Inquiry\Src\Repositories\Interfaces\InquiryRepositoryInterface;

class InquiryRepository extends BaseRepository implements InquiryRepositoryInterface
{
    public function getModel(): string
    {
        return Inquiry::class;
    }

    public function paginateForInbox(?int $coordinatorId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['assignee', 'sourceRef']);

        if ($coordinatorId !== null) {
            $query->forCoordinator($coordinatorId);
        }

        foreach (['status', 'source', 'priority'] as $key) {
            if (! empty($filters[$key])) {
                $query->where($key, $filters[$key]);
            }
        }

        return $query->urgentFirst()->paginate($perPage)->withQueryString();
    }

    public function assign(Inquiry $inquiry, int $userId, int $byUserId): bool
    {
        return DB::transaction(function () use ($inquiry, $userId, $byUserId) {
            $previousAssigneeId = $inquiry->assigned_to;
            $updated = $inquiry->update(['assigned_to' => $userId]);

            if ($updated) {
                $body = $previousAssigneeId === null
                    ? __('inquiry::inquiry.assigned_initial', ['id' => $userId])
                    : __('inquiry::inquiry.assigned_reassigned', ['from' => $previousAssigneeId, 'to' => $userId]);

                InquiryNote::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id' => $byUserId,
                    'body' => $body,
                    'status_from' => null,
                    'status_to' => null,
                ]);
            }

            return $updated;
        });
    }

    public function addNote(Inquiry $inquiry, int $userId, string $body): InquiryNote
    {
        return InquiryNote::create([
            'inquiry_id' => $inquiry->id,
            'user_id' => $userId,
            'body' => $body,
            'status_from' => null,
            'status_to' => null,
        ]);
    }

    public function transitionStatus(Inquiry $inquiry, InquiryStatus $to, int $userId, ?string $noteBody = null): bool
    {
        if ($inquiry->trashed()) {
            return false;
        }

        $current = $inquiry->status;

        // Same-state and forbidden transitions both rejected. Use addNote() for status-unchanged comments.
        if ($current === $to || ! in_array($to, $current->allowedNext(), true)) {
            return false;
        }

        return DB::transaction(function () use ($inquiry, $current, $to, $userId, $noteBody) {
            $updates = ['status' => $to->value];
            if ($to === InquiryStatus::Contacted && $inquiry->contacted_at === null) {
                $updates['contacted_at'] = now();
            }
            if (in_array($to, [InquiryStatus::Closed, InquiryStatus::Cancelled], true) && $inquiry->closed_at === null) {
                $updates['closed_at'] = now();
            }
            $inquiry->update($updates);

            InquiryNote::create([
                'inquiry_id' => $inquiry->id,
                'user_id' => $userId,
                // `?:` (not `??`) so an empty string also falls back to the default audit body.
                'body' => $noteBody ?: __('inquiry::inquiry.status_changed_default'),
                'status_from' => $current->value,
                'status_to' => $to->value,
            ]);

            return true;
        });
    }
}
