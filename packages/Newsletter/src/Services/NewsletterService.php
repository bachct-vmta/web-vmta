<?php

namespace Packages\Newsletter\Src\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Packages\Newsletter\Src\Enums\SubscriberStatus;
use Packages\Newsletter\Src\Mail\ConfirmSubscriptionMail;
use Packages\Newsletter\Src\Mail\UnsubscribedMail;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;
use Packages\Newsletter\Src\Repositories\Interfaces\NewsletterRepositoryInterface;

class NewsletterService
{
    public function __construct(private readonly NewsletterRepositoryInterface $repository) {}

    /**
     * Create or revive a subscriber pending confirmation. Sends double opt-in email.
     * Returns [subscriber, alreadyConfirmed?]. Re-subscribing a confirmed email is a no-op
     * (we don't re-confirm — just refresh the dispatch).
     */
    public function subscribe(string $email, string $locale, ?string $ip = null): array
    {
        $existing = $this->repository->findByEmail($email);

        if ($existing && $existing->isConfirmed()) {
            return [$existing, true];
        }

        $token = Str::random(48);

        if ($existing) {
            $existing->update([
                'locale' => $locale,
                'status' => SubscriberStatus::Pending,
                'opt_in_token' => $token,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'ip_address' => $ip,
                'consent_at' => now(),
            ]);
            $subscriber = $existing;
        } else {
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'locale' => $locale,
                'status' => SubscriberStatus::Pending,
                'opt_in_token' => $token,
                'subscribed_at' => now(),
                'ip_address' => $ip,
                'consent_at' => now(),
            ]);
        }

        $this->dispatchConfirmation($subscriber);

        return [$subscriber, false];
    }

    public function confirm(string $token): ?NewsletterSubscriber
    {
        $subscriber = $this->repository->findByToken($token);
        if (! $subscriber) {
            return null;
        }

        if ($subscriber->isConfirmed()) {
            return $subscriber;
        }

        $subscriber->update([
            'status' => SubscriberStatus::Confirmed,
            'confirmed_at' => now(),
            'opt_in_token' => null,
        ]);

        return $subscriber;
    }

    public function unsubscribeByEmail(string $email): ?NewsletterSubscriber
    {
        $subscriber = $this->repository->findByEmail($email);
        if (! $subscriber) {
            return null;
        }

        $subscriber->update([
            'status' => SubscriberStatus::Unsubscribed,
            'unsubscribed_at' => now(),
            'opt_in_token' => null,
        ]);

        $this->dispatchUnsubscribed($subscriber);

        return $subscriber;
    }

    protected function dispatchConfirmation(NewsletterSubscriber $subscriber): void
    {
        try {
            Mail::to($subscriber->email)->queue(new ConfirmSubscriptionMail($subscriber));
        } catch (\Throwable $e) {
            Log::error('newsletter.mail.confirm_dispatch_failed', [
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function dispatchUnsubscribed(NewsletterSubscriber $subscriber): void
    {
        try {
            Mail::to($subscriber->email)->queue(new UnsubscribedMail($subscriber));
        } catch (\Throwable $e) {
            Log::error('newsletter.mail.unsubscribe_dispatch_failed', [
                'subscriber_id' => $subscriber->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
