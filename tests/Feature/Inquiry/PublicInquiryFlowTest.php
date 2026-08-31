<?php

namespace Tests\Feature\Inquiry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Mail\InquiryAutoReplyMail;
use Packages\Inquiry\Src\Mail\InquiryReceivedMail;
use Packages\Inquiry\Src\Models\Inquiry;
use Tests\TestCase;

class PublicInquiryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inquiry.admin_email' => 'ops@vmta.test']);
    }

    public function test_contact_form_creates_inquiry_and_queues_both_mails(): void
    {
        Mail::fake();

        $response = $this->post('/vi/contact', [
            'name' => 'Nguyễn Test',
            'email' => 'test@example.com',
            'phone' => '+84901234567',
            'message' => 'Tôi cần tư vấn chi tiết về dịch vụ nha khoa tại Đà Nẵng.',
            'consent_given' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $inquiry = Inquiry::first();
        $this->assertNotNull($inquiry);
        $this->assertSame(InquirySource::ContactForm, $inquiry->source);
        $this->assertSame(InquiryPriority::Normal, $inquiry->priority);
        $this->assertSame('vi', $inquiry->locale);

        Mail::assertQueued(InquiryReceivedMail::class, fn ($m) => $m->hasTo('ops@vmta.test'));
        Mail::assertQueued(InquiryAutoReplyMail::class, fn ($m) => $m->hasTo('test@example.com'));
    }

    public function test_honeypot_filled_silently_discards(): void
    {
        Mail::fake();

        $response = $this->post('/vi/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'phone' => '+84902345678',
            'message' => 'spam spam spam spam',
            'consent_given' => '1',
            // Default Spatie honeypot field name is dynamically computed from APP_NAME but
            // the package also blocks via missing valid_from timestamp. We rely on
            // ProtectAgainstSpam.handle() which redirects back without invoking controller.
            'my_name' => 'i-am-a-bot',
            'valid_from' => 'an-invalid-value',
        ]);

        // Spatie's ProtectAgainstSpam returns 200 (silent) so bots cannot distinguish success/blocked.
        // The contract is "no side effects": no row inserted, no mail queued.
        $this->assertSame(0, Inquiry::count());
        Mail::assertNotQueued(InquiryReceivedMail::class);
        Mail::assertNotQueued(InquiryAutoReplyMail::class);
    }

    public function test_emergency_form_creates_urgent_inquiry(): void
    {
        Mail::fake();

        $response = $this->post('/vi/khan-cap', [
            'name' => 'Khẩn cấp',
            'phone' => '0903456789',
            'message' => 'Cần hỗ trợ ngay',
            'consent_given' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $inquiry = Inquiry::first();
        $this->assertNotNull($inquiry);
        $this->assertSame(InquirySource::Emergency, $inquiry->source);
        $this->assertSame(InquiryPriority::Urgent, $inquiry->priority);
    }

    public function test_rate_limit_blocks_excess_submissions(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/vi/contact', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'phone' => '+84901111111',
                'message' => 'Yêu cầu tư vấn lần thứ '.$i,
                'consent_given' => '1',
            ]);
        }

        $response = $this->post('/vi/contact', [
            'name' => 'Sixth',
            'email' => 'sixth@example.com',
            'phone' => '+84901111111',
            'message' => 'Quá ngưỡng rate limit',
            'consent_given' => '1',
        ]);

        $response->assertStatus(429);
    }

    public function test_invalid_phone_rejected(): void
    {
        $response = $this->from('/vi/contact')->post('/vi/contact', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'phone' => '+0------0',
            'message' => 'Cần tư vấn',
            'consent_given' => '1',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertSame(0, Inquiry::count());
    }

    public function test_consent_required(): void
    {
        $response = $this->from('/vi/contact')->post('/vi/contact', [
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'phone' => '+84901234567',
            'message' => 'Cần tư vấn chi tiết',
            // consent_given missing
        ]);

        $response->assertSessionHasErrors(['consent_given']);
        $this->assertSame(0, Inquiry::count());
    }

    public function test_quick_inquiry_creates_lead_with_source_ref(): void
    {
        Mail::fake();

        $response = $this->post('/vi/inquiry/quick', [
            'name' => 'Khách quick',
            'phone' => '+84905555555',
            'email' => 'quick@example.com',
            'message' => 'Quan tâm gói khám tim mạch',
            'source_ref_type' => 'service',
            'source_ref_id' => 42,
            'consent_given' => '1',
        ]);

        $response->assertRedirect();

        $inquiry = Inquiry::first();
        $this->assertNotNull($inquiry);
        $this->assertSame(InquirySource::CatalogQuick, $inquiry->source);
        $this->assertSame('service', $inquiry->source_ref_type);
        $this->assertSame(42, $inquiry->source_ref_id);
    }

    public function test_quick_inquiry_rejects_disallowed_ref_type(): void
    {
        $response = $this->from('/vi/inquiry/quick')->post('/vi/inquiry/quick', [
            'name' => 'Attacker',
            'phone' => '+84906666666',
            'message' => 'leak attempt',
            'source_ref_type' => 'user',
            'source_ref_id' => 1,
            'consent_given' => '1',
        ]);

        $response->assertSessionHasErrors(['source_ref_type']);
        $this->assertSame(0, Inquiry::count());
    }

    public function test_quick_inquiry_requires_source_ref_fields(): void
    {
        $response = $this->from('/vi/inquiry/quick')->post('/vi/inquiry/quick', [
            'name' => 'NoRef',
            'phone' => '+84907777777',
            'message' => 'missing ref',
            'consent_given' => '1',
        ]);

        $response->assertSessionHasErrors(['source_ref_type', 'source_ref_id']);
        $this->assertSame(0, Inquiry::count());
    }
}
