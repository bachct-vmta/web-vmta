<?php

namespace Tests\Feature\Newsletter;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Packages\Newsletter\Src\Enums\SubscriberStatus;
use Packages\Newsletter\Src\Mail\ConfirmSubscriptionMail;
use Packages\Newsletter\Src\Mail\UnsubscribedMail;
use Packages\Newsletter\Src\Models\NewsletterSubscriber;
use Tests\TestCase;

class SubscribeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_pending_subscriber_and_sends_confirmation_email(): void
    {
        Mail::fake();

        $response = $this->post(route('newsletter.vi.subscribe'), [
            'email' => 'sub@example.com',
            'consent_given' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $sub = NewsletterSubscriber::first();
        $this->assertNotNull($sub);
        $this->assertSame('sub@example.com', $sub->email);
        $this->assertSame(SubscriberStatus::Pending, $sub->status);
        $this->assertNotNull($sub->opt_in_token);
        $this->assertNotNull($sub->consent_at);

        Mail::assertQueued(ConfirmSubscriptionMail::class, fn ($m) => $m->hasTo('sub@example.com'));
    }

    public function test_subscribe_invalid_email_rejected(): void
    {
        $response = $this->post(route('newsletter.vi.subscribe'), [
            'email' => 'not-an-email',
            'consent_given' => '1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_subscribe_consent_required(): void
    {
        $response = $this->post(route('newsletter.vi.subscribe'), [
            'email' => 'sub@example.com',
        ]);

        $response->assertSessionHasErrors('consent_given');
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    public function test_resubscribe_pending_email_refreshes_token_and_resends(): void
    {
        Mail::fake();

        $this->post(route('newsletter.vi.subscribe'), ['email' => 'me@example.com', 'consent_given' => '1']);
        $oldToken = NewsletterSubscriber::first()->opt_in_token;

        $this->post(route('newsletter.vi.subscribe'), ['email' => 'me@example.com', 'consent_given' => '1']);
        $newToken = NewsletterSubscriber::first()->opt_in_token;

        $this->assertSame(1, NewsletterSubscriber::count());
        $this->assertNotSame($oldToken, $newToken);
        Mail::assertQueued(ConfirmSubscriptionMail::class, 2);
    }

    public function test_confirm_token_marks_subscriber_confirmed(): void
    {
        Mail::fake();
        $this->post(route('newsletter.vi.subscribe'), ['email' => 'confirm@example.com', 'consent_given' => '1']);

        $sub = NewsletterSubscriber::first();
        $response = $this->get(route('newsletter.confirm', ['token' => $sub->opt_in_token]));

        $response->assertStatus(200);
        $sub->refresh();
        $this->assertSame(SubscriberStatus::Confirmed, $sub->status);
        $this->assertNull($sub->opt_in_token);
    }

    public function test_confirm_invalid_token_renders_failure(): void
    {
        $response = $this->get(route('newsletter.confirm', ['token' => 'invalidtoken123']));
        $response->assertStatus(200);
        $response->assertSee(__('newsletter::newsletter.confirm_failed'));
    }

    public function test_unsubscribe_by_email_sets_status_and_sends_mail(): void
    {
        Mail::fake();
        $this->post(route('newsletter.vi.subscribe'), ['email' => 'bye@example.com', 'consent_given' => '1']);

        $response = $this->get(route('newsletter.unsubscribe', ['email' => 'bye@example.com']));

        $response->assertStatus(200);
        $sub = NewsletterSubscriber::first();
        $this->assertSame(SubscriberStatus::Unsubscribed, $sub->status);
        Mail::assertQueued(UnsubscribedMail::class, fn ($m) => $m->hasTo('bye@example.com'));
    }
}
