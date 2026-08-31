<?php

namespace Packages\Inquiry\Src\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Models\InquiryNote;

interface InquiryRepositoryInterface extends RepositoryInterface
{
    public function paginateForInbox(?int $coordinatorId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Assign inquiry to a Coordinator and write a transactional audit note authored by $byUserId.
     */
    public function assign(Inquiry $inquiry, int $userId, int $byUserId): bool;

    /**
     * Add a status-unchanged note (free-form comment by Coordinator/Admin).
     * Both status_from and status_to are stored NULL to distinguish from transition notes.
     */
    public function addNote(Inquiry $inquiry, int $userId, string $body): InquiryNote;

    /**
     * Transition inquiry to a new status with audit note in one transaction.
     * Returns false if: the target equals current status, the transition is not in `allowedNext()`,
     * or the inquiry is soft-deleted. Throws on DB error.
     */
    public function transitionStatus(Inquiry $inquiry, InquiryStatus $to, int $userId, ?string $noteBody = null): bool;
}
