<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\AllianceSection;

/**
 * Seeds 5 alliance-page sections.
 * Placeholder copy — replace with scraped vmta.test content once available.
 * Idempotent via firstOrNew.
 */
class AllianceSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->payload() as $row) {
                $position = $row['position'];
                $section  = AllianceSection::firstOrNew(['position' => $position->value]);
                $section->position   = $position;
                $section->is_active  = true;
                $section->sort_order = $position->defaultSortOrder();
                $section->save();

                foreach ($row['translations'] as $locale => $payload) {
                    $section->translateOrNew($locale)->fill($payload)->save();
                }
            }
        });
    }

    private function payload(): array
    {
        return [
            $this->hero(),
            $this->overview(),
            $this->standards(),
            $this->map(),
            $this->joinForm(),
        ];
    }

    private function hero(): array
    {
        $vi = [
            'title'    => 'Mạng lưới Liên minh Du lịch Y tế VMTA',
            'subtitle' => 'Hệ sinh thái kết nối các bệnh viện, khu nghỉ dưỡng và đối tác chiến lược',
            'body'     => 'Liên minh tiên phong định hình hệ sinh thái Du lịch Y tế Việt Nam — kết nối điều trị y khoa, nghỉ dưỡng phục hồi và công nghệ vận hành trong một chuẩn mực thống nhất.',
        ];
        $en = [
            'title'    => 'VMTA Medical Tourism Alliance Network',
            'subtitle' => 'An ecosystem linking hospitals, resorts and strategic partners',
            'body'     => 'A pioneering alliance shaping Vietnam medical-tourism ecosystem — integrating clinical care, recovery resorts and operations technology under a single standard.',
        ];

        return ['position' => AllianceSectionPosition::Hero, 'translations' => ['vi' => $vi, 'en' => $en]];
    }

    private function overview(): array
    {
        $vi = [
            'title'    => 'TỔNG QUAN MẠNG LƯỚI',
            'subtitle' => 'VMTA – Liên minh Du lịch Y tế Việt Nam',
            'body'     => '<p>Mạng lưới VMTA tập hợp các bệnh viện hàng đầu, khu nghỉ dưỡng cao cấp và đối tác vận hành trong một hệ sinh thái khép kín.</p><p>Chúng tôi điều phối toàn bộ hành trình chăm sóc — từ thẩm định hồ sơ, điều trị đến phục hồi — bảo đảm liền mạch, an toàn và hiệu quả.</p>',
        ];
        $en = [
            'title'    => 'NETWORK OVERVIEW',
            'subtitle' => 'VMTA – Vietnam Medical Tourism Alliance',
            'body'     => '<p>The VMTA network unites leading hospitals, premium resorts and operations partners within a closed-loop ecosystem.</p><p>We orchestrate the full care journey — from medical assessment through treatment and recovery — guaranteeing continuity, safety and efficiency.</p>',
        ];

        return ['position' => AllianceSectionPosition::Overview, 'translations' => ['vi' => $vi, 'en' => $en]];
    }

    private function standards(): array
    {
        $itemsVi = [
            ['icon' => '/images/alliance/asset-1.jpg', 'label' => 'CHẤT LƯỢNG Y KHOA', 'description' => 'Bệnh viện và đội ngũ y bác sĩ đạt chuẩn quốc tế.'],
            ['icon' => '/images/alliance/asset-2.jpg', 'label' => 'AN TOÀN BỆNH NHÂN', 'description' => 'Quy trình minh bạch, kiểm soát rủi ro xuyên suốt hành trình.'],
            ['icon' => '/images/alliance/asset-3.jpg', 'label' => 'TRẢI NGHIỆM TOÀN DIỆN', 'description' => 'Kết hợp điều trị y khoa với nghỉ dưỡng phục hồi cao cấp.'],
            ['icon' => '/images/alliance/asset-4.jpg', 'label' => 'CÔNG NGHỆ ĐIỀU PHỐI', 'description' => 'Hệ thống dữ liệu tập trung giúp theo dõi và tối ưu trải nghiệm.'],
            ['icon' => '/images/alliance/asset-5.jpg', 'label' => 'BẢO CHỨNG THƯƠNG HIỆU', 'description' => 'VMTA chuẩn hoá và đảm bảo chất lượng đối tác trong hệ sinh thái.'],
        ];
        $itemsEn = [
            ['icon' => '/images/alliance/asset-1.jpg', 'label' => 'CLINICAL QUALITY', 'description' => 'International-standard hospitals and medical professionals.'],
            ['icon' => '/images/alliance/asset-2.jpg', 'label' => 'PATIENT SAFETY', 'description' => 'Transparent workflows with end-to-end risk control.'],
            ['icon' => '/images/alliance/asset-3.jpg', 'label' => 'HOLISTIC EXPERIENCE', 'description' => 'Medical care combined with premium recovery hospitality.'],
            ['icon' => '/images/alliance/asset-4.jpg', 'label' => 'ORCHESTRATION TECH', 'description' => 'Centralized data platform to monitor and optimize the journey.'],
            ['icon' => '/images/alliance/asset-5.jpg', 'label' => 'BRAND ASSURANCE', 'description' => 'VMTA standardizes and certifies partner quality across the ecosystem.'],
        ];

        return [
            'position'     => AllianceSectionPosition::Standards,
            'translations' => [
                'vi' => ['title' => 'TIÊU CHUẨN LIÊN MINH', 'items' => $itemsVi],
                'en' => ['title' => 'ALLIANCE STANDARDS', 'items' => $itemsEn],
            ],
        ];
    }

    private function map(): array
    {
        $vi = [
            'title'    => 'BẢN ĐỒ MẠNG LƯỚI',
            'subtitle' => 'Các điểm kết nối trên toàn quốc',
            'body'     => 'Mạng lưới liên minh trải dài từ Bắc đến Nam, kết nối các trung tâm y khoa và khu nghỉ dưỡng tại những điểm đến trọng yếu của Việt Nam.',
        ];
        $en = [
            'title'    => 'NETWORK MAP',
            'subtitle' => 'Connection points nationwide',
            'body'     => 'The alliance network spans the country, linking medical centers and resorts at key destinations across Vietnam.',
        ];

        return ['position' => AllianceSectionPosition::Map, 'translations' => ['vi' => $vi, 'en' => $en]];
    }

    private function joinForm(): array
    {
        $vi = [
            'title'     => 'Tham Gia Liên Minh',
            'body'      => 'Đăng ký trở thành đối tác của mạng lưới VMTA. Đội ngũ chúng tôi sẽ liên hệ trong thời gian sớm nhất.',
            'cta_label' => 'Gửi đăng ký',
        ];
        $en = [
            'title'     => 'Join the Alliance',
            'body'      => 'Register to become a VMTA network partner. Our team will reach out shortly.',
            'cta_label' => 'Submit registration',
        ];

        return ['position' => AllianceSectionPosition::JoinForm, 'translations' => ['vi' => $vi, 'en' => $en]];
    }
}
