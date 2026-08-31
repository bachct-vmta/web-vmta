<?php

namespace Packages\Inquiry\Src\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Mail\InquiryAutoReplyMail;
use Packages\Inquiry\Src\Mail\InquiryReceivedMail;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Repositories\Interfaces\InquiryRepositoryInterface;
use Packages\Report\Src\Facades\Report;

/**
 * Inquiry write paths used by the 3 public Controllers (Slice B) and admin (Slice C).
 *
 * Notification + email dispatch land in Slice B (we expose hooks here so the dispatcher
 * job can read created inquiries without re-querying enums or duplicating defaults).
 */
class InquiryService
{
    public function __construct(
        private readonly InquiryRepositoryInterface $repository,
    ) {}

    /**
     * @param array{
     *   name: string,
     *   email?: ?string,
     *   phone: string,
     *   industry?: ?string,
     *   company_name?: ?string,
     *   message?: ?string,
     *   locale?: ?string,
     *   source_ref_type?: ?string,
     *   source_ref_id?: ?int,
     *   consent_given: bool,
     *   ip_address?: ?string,
     *   user_agent?: ?string,
     * } $payload
     */
    public function capture(InquirySource $source, array $payload, InquiryPriority $priority = InquiryPriority::Normal): Inquiry
    {
        // Trust boundary: service-owned fields (source/status/priority) MUST NOT be overridable
        // by the request payload. Caller-controlled fields are merged first; service overrides last.
        $callerControlled = [
            'name',
            'email',
            'phone',
            'industry',
            'company_name',
            'message',
            'source_ref_type',
            'source_ref_id',
            'consent_given',
            'ip_address',
            'user_agent',
        ];

        $data = array_merge(
            ['locale' => app()->getLocale()],
            Arr::only($payload, $callerControlled),
            [
                'source' => $source->value,
                'priority' => $priority->value,
                'status' => InquiryStatus::New->value,
            ],
        );

        /** @var Inquiry $inquiry */
        $inquiry = $this->repository->create($data);

        Report::increment('lead.total');
        Report::increment('lead.'.$source->value);

        return $inquiry;
    }

    /**
     * Queue admin notification + customer auto-reply for a captured inquiry.
     * Each dispatch is isolated: a queue-connection failure on one mail must not block the other
     * or roll back the persisted inquiry. Errors are logged with the inquiry id so ops can replay.
     */
    public function dispatchEmails(Inquiry $inquiry): void
    {
        $adminEmail = config('inquiry.admin_email');
        if (! empty($adminEmail)) {
            try {
                Mail::to($adminEmail)->queue(new InquiryReceivedMail($inquiry));
            } catch (\Throwable $e) {
                Log::error('inquiry.mail.admin_dispatch_failed', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! empty($inquiry->email)) {
            try {
                Mail::to($inquiry->email)->queue(new InquiryAutoReplyMail($inquiry));
            } catch (\Throwable $e) {
                Log::error('inquiry.mail.auto_reply_dispatch_failed', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
