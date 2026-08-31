<?php

namespace Tests\Feature\Dental;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\MediaFile;
use Packages\Core\Src\Models\User;
use Packages\Dental\Database\Seeders\DentalCategorySeeder;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Models\DentalService;
use Tests\TestCase;

class DentalAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminSeeder::class);
        $this->seed(DentalCategorySeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@nguyenkhoi.dev')->first();
    }

    private function facility(string $slug = 'bv-a', string $status = 'published'): DentalFacility
    {
        $facility = DentalFacility::create([
            'dental_category_id' => DentalCategory::first()->id,
            'status' => $status,
            'published_at' => now(),
        ]);
        $facility->translateOrNew('vi')->fill(['name' => 'Cơ sở '.$slug, 'slug' => $slug]);
        $facility->save();

        return $facility;
    }

    public function test_seeder_creates_two_categories_and_is_idempotent(): void
    {
        $this->assertSame(2, DentalCategory::count());

        $this->seed(DentalCategorySeeder::class);

        $this->assertSame(2, DentalCategory::count());
        $this->assertSame('Bệnh viện', DentalCategory::first()->translate('vi')->name);
        $this->assertSame('Hospitals', DentalCategory::first()->translate('en')->name);
    }

    public function test_admin_can_list_all_three_screens(): void
    {
        foreach (['dental-categories', 'dental-facilities', 'dental-services'] as $segment) {
            $this->actingAs($this->admin())->get('/admin/'.$segment)->assertOk();
        }
    }

    public function test_create_and_edit_forms_render(): void
    {
        $facility = $this->facility();
        $service = DentalService::create(['dental_facility_id' => $facility->id, 'status' => 'draft']);
        $service->translateOrNew('vi')->fill(['title' => 'Trám răng', 'slug' => 'tram-rang']);
        $service->save();

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/dental-categories/create')->assertOk();
        $this->actingAs($admin)->get('/admin/dental-facilities/create')->assertOk();
        $this->actingAs($admin)->get('/admin/dental-services/create')->assertOk();

        $this->actingAs($admin)->get('/admin/dental-categories/'.DentalCategory::first()->id.'/edit')->assertOk();
        $this->actingAs($admin)->get('/admin/dental-facilities/'.$facility->id.'/edit')->assertOk()->assertSee('bv-a');
        $this->actingAs($admin)->get('/admin/dental-services/'.$service->id.'/edit')->assertOk()->assertSee('tram-rang');
    }

    public function test_guest_is_redirected_from_admin_screens(): void
    {
        $this->get('/admin/dental-facilities')->assertRedirect();
    }

    public function test_admin_can_create_a_bilingual_facility(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/dental-facilities', [
            'dental_category_id' => DentalCategory::first()->id,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'certificates_media_ids' => '',
            'is_operating' => '1',
            'sort_order' => 3,
            'translations' => [
                ['locale' => 'vi', 'name' => 'Bệnh viện Răng Hàm Mặt', 'slug' => 'bv-rang-ham-mat'],
                ['locale' => 'en', 'name' => 'Dental Hospital', 'slug' => 'dental-hospital'],
            ],
        ]);

        $response->assertRedirect();

        $facility = DentalFacility::first();
        $this->assertSame('published', $facility->status);
        $this->assertTrue($facility->is_operating);
        $this->assertSame('Bệnh viện Răng Hàm Mặt', $facility->translate('vi')->name);
        $this->assertSame('Dental Hospital', $facility->translate('en')->name);
    }

    public function test_facility_gallery_keeps_the_order_the_admin_entered(): void
    {
        $facility = $this->facility();
        $media = collect(range(1, 3))->map(fn (int $i) => MediaFile::create([
            'name' => 'cert-'.$i,
            'alt' => 'cert-'.$i,
            'permalink' => '/uploads/cert-'.$i.'.jpg',
        ]));

        $reordered = [$media[2]->id, $media[0]->id, $media[1]->id];

        $this->actingAs($this->admin())->put('/admin/dental-facilities/'.$facility->id, [
            'dental_category_id' => $facility->dental_category_id,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'certificates_media_ids' => implode(',', $reordered),
            'translations' => [
                ['locale' => 'vi', 'name' => 'Cơ sở A', 'slug' => 'bv-a'],
            ],
        ])->assertRedirect();

        $fresh = $facility->fresh();
        $this->assertSame($reordered, $fresh->certificates_media_ids);
        $this->assertSame($reordered, $fresh->certificateMedia()->pluck('id')->all());
    }

    public function test_service_slug_may_repeat_across_facilities_but_not_within_one(): void
    {
        $first = $this->facility('bv-a');
        $second = $this->facility('bv-b');

        $payload = fn (int $facilityId) => [
            'dental_facility_id' => $facilityId,
            'status' => 'draft',
            'translations' => [
                ['locale' => 'vi', 'title' => 'Niềng răng', 'slug' => 'nieng-rang'],
            ],
        ];

        $this->actingAs($this->admin())->post('/admin/dental-services', $payload($first->id))->assertRedirect();

        // Cơ sở khác dùng lại slug là hợp lệ
        $this->actingAs($this->admin())->post('/admin/dental-services', $payload($second->id))->assertRedirect();
        $this->assertSame(2, DentalService::count());

        // Trùng slug trong cùng một cơ sở thì bị chặn
        $this->actingAs($this->admin())
            ->post('/admin/dental-services', $payload($first->id))
            ->assertSessionHasErrors('translations.0.slug');
        $this->assertSame(2, DentalService::count());
    }

    public function test_publishing_a_facility_without_a_date_stamps_it_automatically(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/dental-facilities', [
                'dental_category_id' => DentalCategory::first()->id,
                'status' => 'published',
                'translations' => [['locale' => 'vi', 'name' => 'Không ngày', 'slug' => 'khong-ngay']],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $facility = DentalFacility::first();
        $this->assertNotNull($facility->published_at);
        $this->assertTrue($facility->isPublished());
    }

    public function test_publishing_a_service_without_a_date_stamps_it_automatically(): void
    {
        $facility = $this->facility();

        // Form dịch vụ không còn ô thời điểm xuất bản
        $this->actingAs($this->admin())->post('/admin/dental-services', [
            'dental_facility_id' => $facility->id,
            'status' => 'published',
            'translations' => [['locale' => 'vi', 'title' => 'Niềng răng', 'slug' => 'nieng-rang']],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $service = DentalService::first();
        $this->assertNotNull($service->published_at);
        $this->assertTrue($service->isPublished());
    }

    public function test_updating_a_service_keeps_its_publish_date_and_order(): void
    {
        $facility = $this->facility();
        $service = DentalService::create([
            'dental_facility_id' => $facility->id,
            'status' => 'published',
            'published_at' => now()->subMonth(),
            'sort_order' => 7,
        ]);
        $service->translateOrNew('vi')->fill(['title' => 'Tẩy trắng', 'slug' => 'tay-trang']);
        $service->save();

        $this->actingAs($this->admin())->put('/admin/dental-services/'.$service->id, [
            'dental_facility_id' => $facility->id,
            'status' => 'published',
            'translations' => [['locale' => 'vi', 'title' => 'Tẩy trắng răng', 'slug' => 'tay-trang']],
        ])->assertRedirect();

        $fresh = $service->fresh();
        $this->assertTrue($fresh->published_at->isSameDay(now()->subMonth()));
        $this->assertSame(7, $fresh->sort_order);
    }

    public function test_admin_can_bulk_delete_facilities(): void
    {
        $a = $this->facility('bv-a');
        $b = $this->facility('bv-b');

        $this->actingAs($this->admin())
            ->post('/admin/dental-facilities/bulk-delete', ['ids' => [$a->id, $b->id]])
            ->assertRedirect();

        $this->assertSame(0, DentalFacility::count());
    }

    public function test_deleting_a_facility_cascades_to_its_services(): void
    {
        $facility = $this->facility();
        $service = DentalService::create(['dental_facility_id' => $facility->id, 'status' => 'draft']);
        $service->translateOrNew('vi')->fill(['title' => 'Tẩy trắng', 'slug' => 'tay-trang']);
        $service->save();

        $facility->forceDelete();

        $this->assertSame(0, DentalService::withTrashed()->count());
    }
}
