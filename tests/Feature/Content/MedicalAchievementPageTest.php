<?php

namespace Tests\Feature\Content;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Content\Database\Seeders\AchievementSectionSeeder;
use Packages\Content\Database\Seeders\MedicalCaseSeeder;
use Tests\TestCase;

class MedicalAchievementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_achievement_page_renders_from_dedicated_route(): void
    {
        $this->seed(AchievementSectionSeeder::class);

        $response = $this->get('/vi/thanh-tuu-y-khoa/');

        $response->assertStatus(200);
        $response->assertSee('Thành tựu Y khoa', false);
        $response->assertSee('Kỳ tích ghép đồng thời tim – phổi', false);
        $response->assertSee('VMTA – BẢO CHỨNG CHO HÀNH TRÌNH Y TẾ AN TOÀN', false);
        $response->assertSee('/images/medical-achievements/hero-operating-room.jpg', false);
        $response->assertSee('/vi/lien-he', false);
    }

    public function test_heart_lung_detail_page_renders_seeded_case_content(): void
    {
        $this->seed(MedicalCaseSeeder::class);

        $response = $this->get('/vi/ghep-dong-thoi-tim-phoi/');

        $response->assertStatus(200);
        $response->assertSee('GHÉP ĐỒNG THỜI', false);
        $response->assertSee('BỆNH VIỆN HỮU NGHỊ VIỆT ĐỨC', false);
        $response->assertSee('PHƯƠNG ÁN ĐIỀU TRỊ PHÙ HỢP', false);
        $response->assertSee('/images/heart-lung-transplant/choice-surgery-room.jpg', false);
        $response->assertSee('/images/heart-lung-transplant/choice-doctor-support.jpg', false);
        $response->assertSee('/images/heart-lung-transplant/choice-intensive-care.jpg', false);
        $response->assertSee('/images/heart-lung-transplant/choice-recovery-resort.jpg', false);
        $response->assertSee('/images/heart-lung-transplant/icon-stethoscope.png', false);

        $this->assertFileExists(public_path('images/heart-lung-transplant/cta-bg.jpg'));
        $this->assertFileExists(public_path('images/heart-lung-transplant/icon-stethoscope.png'));
    }
}
