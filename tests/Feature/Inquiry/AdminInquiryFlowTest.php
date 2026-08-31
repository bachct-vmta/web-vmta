<?php

namespace Tests\Feature\Inquiry;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\Role;
use Packages\Core\Src\Models\User;
use Packages\Inquiry\Src\Enums\InquiryPriority;
use Packages\Inquiry\Src\Enums\InquirySource;
use Packages\Inquiry\Src\Enums\InquiryStatus;
use Packages\Inquiry\Src\Models\Inquiry;
use Packages\Inquiry\Src\Notifications\InquiryAssignedNotification;
use Tests\TestCase;

class AdminInquiryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function makeInquiry(array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'name' => 'Khách',
            'email' => 'khach@example.com',
            'phone' => '+84901234567',
            'message' => 'Tôi quan tâm dịch vụ',
            'locale' => 'vi',
            'source' => InquirySource::ContactForm->value,
            'priority' => InquiryPriority::Normal->value,
            'status' => InquiryStatus::New->value,
            'consent_given' => true,
        ], $overrides));
    }

    public function test_index_renders_for_super_admin(): void
    {
        $this->makeInquiry(['name' => 'A']);
        $this->makeInquiry(['name' => 'B', 'priority' => InquiryPriority::Urgent->value]);

        $response = $this->actingAs($this->admin())->get('/admin/inquiries');

        $response->assertOk();
        $response->assertSee('A');
        $response->assertSee('B');
    }

    public function test_index_filters_by_status(): void
    {
        $this->makeInquiry(['name' => 'NewOne']);
        $this->makeInquiry(['name' => 'ClosedOne', 'status' => InquiryStatus::Closed->value]);

        $response = $this->actingAs($this->admin())->get('/admin/inquiries?status=closed');

        $response->assertOk();
        $response->assertSee('ClosedOne');
        $response->assertDontSee('NewOne');
    }

    public function test_show_renders_inquiry_detail(): void
    {
        $inq = $this->makeInquiry(['name' => 'Detail user']);

        $response = $this->actingAs($this->admin())->get("/admin/inquiries/{$inq->id}");

        $response->assertOk();
        $response->assertSee('Detail user');
        $response->assertSee($inq->phone);
    }

    public function test_assign_creates_audit_note_and_sends_notification(): void
    {
        Notification::fake();
        $inq = $this->makeInquiry();
        $admin = $this->admin();

        // Create a candidate coordinator (role with inquiry.index permission)
        $coordRole = Role::where('slug', 'coordinator')->first();
        $coord = User::create([
            'name' => 'Coord Test',
            'email' => 'coord-test@x.com',
            'password' => bcrypt('x'),
            'role_id' => $coordRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post("/admin/inquiries/{$inq->id}/assign", [
            'assigned_to' => $coord->id,
        ]);

        $response->assertRedirect();
        $this->assertSame($coord->id, $inq->fresh()->assigned_to);
        $this->assertSame(1, $inq->fresh()->notes()->count(), 'Audit note must be created on first assign');

        Notification::assertSentTo($coord, InquiryAssignedNotification::class);
    }

    public function test_transition_valid_status_creates_note_and_updates_timestamp(): void
    {
        $inq = $this->makeInquiry();
        $admin = $this->admin();

        $this->assertNull($inq->contacted_at);

        $response = $this->actingAs($admin)->post("/admin/inquiries/{$inq->id}/transition", [
            'status' => 'contacted',
            'note' => 'Đã gọi điện',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $fresh = $inq->fresh();
        $this->assertSame(InquiryStatus::Contacted, $fresh->status);
        $this->assertNotNull($fresh->contacted_at);

        $note = $fresh->notes()->first();
        $this->assertSame('Đã gọi điện', $note->body);
        $this->assertSame('new', $note->status_from);
        $this->assertSame('contacted', $note->status_to);
    }

    public function test_transition_forbidden_path_returns_session_error(): void
    {
        $inq = $this->makeInquiry(['status' => InquiryStatus::Closed->value]);
        $admin = $this->admin();

        $response = $this->actingAs($admin)
            ->from("/admin/inquiries/{$inq->id}")
            ->post("/admin/inquiries/{$inq->id}/transition", ['status' => 'new']);

        $response->assertSessionHasErrors(['status']);
        $this->assertSame(InquiryStatus::Closed, $inq->fresh()->status);
    }

    public function test_add_note_creates_standalone_note(): void
    {
        $inq = $this->makeInquiry();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post("/admin/inquiries/{$inq->id}/note", [
            'body' => 'Khách yêu cầu gọi lại sau 17h',
        ]);

        $response->assertRedirect();
        $note = $inq->fresh()->notes()->first();
        $this->assertSame('Khách yêu cầu gọi lại sau 17h', $note->body);
        $this->assertNull($note->status_from);
        $this->assertNull($note->status_to);
    }

    public function test_destroy_soft_deletes_inquiry(): void
    {
        $inq = $this->makeInquiry();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->delete("/admin/inquiries/{$inq->id}");

        $response->assertRedirect();
        $this->assertNull(Inquiry::find($inq->id));
        $this->assertNotNull(Inquiry::withTrashed()->find($inq->id));
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/admin/inquiries');
        $response->assertRedirect();
    }

    public function test_throttle_bucket_is_shared_across_inquiry_post_routes(): void
    {
        // Contract lock (Slice B M2 deferral): Laravel's default ThrottleRequests middleware
        // keys the bucket on `domain|ip` for unauthenticated requests — so all 3 inquiry POST
        // routes share a single 5/min bucket per IP. This is stricter than per-route and matches
        // the plan's "5/phút/IP" intent (single rate-limit budget for any inquiry submission).

        for ($i = 0; $i < 5; $i++) {
            $this->post('/vi/contact', [
                'name' => "User {$i}",
                'email' => "u{$i}@x.com",
                'phone' => '+84901111111',
                'message' => 'Yêu cầu '.$i,
                'consent_given' => '1',
            ]);
        }

        // Hitting a DIFFERENT inquiry route from the same IP also gets throttled.
        $emergencyBlocked = $this->post('/vi/khan-cap', [
            'name' => 'Khẩn',
            'phone' => '0903456789',
            'consent_given' => '1',
        ]);
        $emergencyBlocked->assertStatus(429);
    }
}
