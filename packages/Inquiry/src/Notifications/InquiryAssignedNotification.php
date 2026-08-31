<?php

namespace Packages\Inquiry\Src\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Packages\Inquiry\Src\Models\Inquiry;

/**
 * Fires when an Admin assigns an Inquiry to a Coordinator.
 * Delivered via in-app bell (database) + queued email.
 *
 * Locale strategy: rendered in staff UI language (config('app.locale')), NOT the customer's
 * submission locale — the recipient here is internal staff.
 *
 * URL strategy: queue workers have no HTTP request, so route() falls back to APP_URL. We force
 * the root URL from config at construction time so the link in the email/in-app card is correct.
 */
class InquiryAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public readonly string $renderedTitle;

    public readonly string $staffLocale;

    public readonly string $detailUrl;

    public function __construct(public Inquiry $inquiry)
    {
        $this->staffLocale = (string) config('app.locale', 'vi');
        $this->renderedTitle = __('inquiry::inquiry.admin.assigned_notification_title', [], $this->staffLocale);

        $appUrl = (string) config('app.url', '');
        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);
        }
        $this->detailUrl = route(admin_route_name('inquiries.show'), $inquiry->id);
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->renderedTitle)
            ->greeting(__('inquiry::inquiry.admin.assigned_notification_greeting', [], $this->staffLocale))
            ->line($this->buildBodyLine())
            ->action(__('inquiry::inquiry.admin.detail_heading', [], $this->staffLocale), $this->detailUrl);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'inquiry_id' => $this->inquiry->id,
            'inquiry_name' => $this->inquiry->name,
            'inquiry_phone' => $this->inquiry->phone,
            'priority' => $this->inquiry->priority->value,
            'source' => $this->inquiry->source->value,
            'title' => $this->renderedTitle,
            'url' => $this->detailUrl,
        ];
    }

    private function buildBodyLine(): string
    {
        return sprintf(
            '#%d %s — %s · %s',
            $this->inquiry->id,
            $this->inquiry->name,
            $this->inquiry->phone,
            __('inquiry::inquiry.priority.'.$this->inquiry->priority->value, [], $this->staffLocale),
        );
    }
}
