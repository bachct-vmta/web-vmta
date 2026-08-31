<?php

namespace Packages\Inquiry\Src\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Models\Inquiry;

class InquiryReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * Subject is rendered at construction time (synchronous, request locale) so the queue worker
     * cannot produce a mixed-locale subject when picking up the job later under a different locale.
     */
    public readonly string $renderedSubject;

    public function __construct(public Inquiry $inquiry)
    {
        $key = $inquiry->priority === InquiryPriority::Urgent
            ? 'inquiry::inquiry.admin_subject_urgent'
            : 'inquiry::inquiry.admin_subject_normal';

        $this->renderedSubject = __($key, ['name' => $inquiry->name], $inquiry->locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'inquiry::emails.admin-received',
        );
    }
}
