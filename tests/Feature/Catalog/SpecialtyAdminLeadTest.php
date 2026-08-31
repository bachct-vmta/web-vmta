<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\SpecialtyLead;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;
use Tests\TestCase;

class SpecialtyAdminLeadTest extends TestCase
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

    private static int $slugCounter = 0;

    private function lead(string $name = 'Nguyễn A'): SpecialtyLead
    {
        self::$slugCounter++;
        $sp = Specialty::create(['is_active' => true, 'show_lead_form' => true, 'sort_order' => 1]);
        SpecialtyTranslation::create([
            'specialty_id' => $sp->id,
            'locale' => 'vi',
            'name' => 'Chuyên khoa '.self::$slugCounter,
            'slug' => 'specialty-'.self::$slugCounter,
        ]);
        return SpecialtyLead::create([
            'specialty_id' => $sp->id,
            'name' => $name,
            'phone' => '0900000000',
            'status' => 'new',
            'locale' => 'vi',
        ]);
    }

    public function test_admin_can_list_leads(): void
    {
        $this->lead('Lead A');
        $this->lead('Lead B');

        $r = $this->actingAs($this->admin())->get('/admin/specialty-leads');
        $r->assertOk();
        $r->assertSee('Lead A');
        $r->assertSee('Lead B');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $a = $this->lead('Open Lead');
        $b = $this->lead('Closed Lead');
        $b->update(['status' => 'closed']);

        $r = $this->actingAs($this->admin())->get('/admin/specialty-leads?status=closed');
        $r->assertOk();
        $r->assertSee('Closed Lead');
        $r->assertDontSee('Open Lead');
    }

    public function test_admin_can_update_lead_status(): void
    {
        $lead = $this->lead('Status Lead');

        $r = $this->actingAs($this->admin())
            ->patch('/admin/specialty-leads/'.$lead->id.'/status', ['status' => 'contacted']);

        $r->assertRedirect();
        $this->assertDatabaseHas('specialty_leads', [
            'id' => $lead->id,
            'status' => 'contacted',
        ]);
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->lead();

        $this->get('/admin/specialty-leads')->assertRedirect();
    }
}
