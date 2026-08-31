<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Packages\Catalog\Src\Mail\SpecialtyLeadReceivedMail;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\SpecialtyLead;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;
use Tests\TestCase;

class SpecialtyLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        RateLimiter::clear('throttle:6,1');
    }

    private function specialty(string $slug = 'nha-khoa'): Specialty
    {
        $sp = Specialty::create(['is_active' => true, 'show_lead_form' => true, 'sort_order' => 1]);
        SpecialtyTranslation::create([
            'specialty_id' => $sp->id,
            'locale' => 'vi',
            'name' => 'Nha khoa',
            'slug' => $slug,
        ]);
        return $sp;
    }

    public function test_lead_submit_persists_record_and_queues_mail(): void
    {
        $sp = $this->specialty();

        $r = $this->post('/vi/chuyen-khoa/nha-khoa/lead', [
            'name' => 'Nguyễn Khoa',
            'phone' => '0900000000',
            'email' => 'a@b.test',
            'demand' => 'Implant',
            'message' => 'Cần tư vấn',
            'consent' => '1',
        ]);

        $r->assertRedirect();
        $this->assertDatabaseHas('specialty_leads', [
            'specialty_id' => $sp->id,
            'name' => 'Nguyễn Khoa',
            'phone' => '0900000000',
            'email' => 'a@b.test',
            'status' => 'new',
        ]);
        Mail::assertQueued(SpecialtyLeadReceivedMail::class);
    }

    public function test_lead_validation_rejects_missing_consent(): void
    {
        $this->specialty();

        $r = $this->from('/vi/chuyen-khoa/nha-khoa')
            ->post('/vi/chuyen-khoa/nha-khoa/lead', [
                'name' => 'X',
                'phone' => '0900',
            ]);

        $r->assertSessionHasErrors(['consent']);
        $this->assertDatabaseCount('specialty_leads', 0);
        Mail::assertNothingQueued();
    }

    public function test_lead_honeypot_silently_rejects(): void
    {
        $this->specialty();

        $r = $this->post('/vi/chuyen-khoa/nha-khoa/lead', [
            'name' => 'Bot',
            'phone' => '0900',
            'hp_field' => 'spam',
            'consent' => '1',
        ]);

        $r->assertRedirect();
        $this->assertDatabaseCount('specialty_leads', 0);
        Mail::assertNothingQueued();
    }

    public function test_lead_returns_404_for_missing_specialty(): void
    {
        $this->post('/vi/chuyen-khoa/khong-co/lead', [
            'name' => 'X',
            'phone' => '0900000000',
            'consent' => '1',
        ])->assertNotFound();
    }
}
