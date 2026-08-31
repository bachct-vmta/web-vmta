<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Models\AchievementSection;
use Packages\Content\Src\Models\MedicalCase;

/**
 * Seeds Achievement page: 3 sections (hero/capabilities/assurance) + 3 medical cases.
 * Source: blade hard-coded content at thanh-tuu-y-khoa.blade.php (vi). EN clones VI.
 * Idempotent via firstOrNew (sections) + updateOrCreate (cases).
 */
class AchievementSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedSections();
            $this->seedCases();
        });
    }

    private function seedSections(): void
    {
        foreach ($this->sectionsPayload() as $row) {
            $position = $row['position'];
            $section = AchievementSection::firstOrNew(['position' => $position->value]);
            $section->position = $position;
            $section->is_active = true;
            $section->sort_order = $position->defaultSortOrder();
            $section->save();

            foreach ($row['translations'] as $locale => $payload) {
                $section->translateOrNew($locale)->fill($payload)->save();
            }
        }
    }

    private function seedCases(): void
    {
        foreach ($this->casesPayload() as $row) {
            $case = MedicalCase::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'reverse'    => $row['reverse'],
                    'sort_order' => $row['sort_order'],
                    'is_active'  => true,
                ],
            );

            foreach ($row['translations'] as $locale => $payload) {
                $case->translateOrNew($locale)->fill($payload)->save();
            }
        }
    }

    private function sectionsPayload(): array
    {
        return [
            $this->hero(),
            $this->capabilities(),
            $this->assurance(),
        ];
    }

    private function hero(): array
    {
        $statsVi = [
            ['icon_path' => 'stat-heart.png',    'value' => '1000+',   'label' => 'Ca ghép tạng mỗi ngày'],
            ['icon_path' => 'stat-hospital.png', 'value' => '200+',    'label' => "Bệnh viện & phòng khám\nchất lượng cao"],
            ['icon_path' => 'stat-people.png',   'value' => '50.000+', 'label' => "Khách hàng quốc tế\ntin tưởng lựa chọn"],
        ];
        $statsEn = [
            ['icon_path' => 'stat-heart.png',    'value' => '1000+',   'label' => 'Organ transplants daily'],
            ['icon_path' => 'stat-hospital.png', 'value' => '200+',    'label' => "High-quality hospitals\n& clinics"],
            ['icon_path' => 'stat-people.png',   'value' => '50,000+', 'label' => "International patients\ntrust our network"],
        ];

        $bodyVi = "Việt Nam đang từng bước khẳng định vị thế trên bản đồ y khoa thế giới, không chỉ bằng chi phí cạnh tranh mà còn bằng những thành tựu y học mang tính đột phá.\n\n"
                . "Từ những ca ghép tạng phức tạp đến ứng dụng công nghệ y học chính xác, các bác sĩ Việt Nam đã chinh phục những giới hạn tưởng chừng không thể.\n\n"
                . "Đây không chỉ là thành tựu – mà là những câu chuyện hồi sinh.";

        $bodyEn = "Vietnam is steadily asserting its position on the world medical map — not only through competitive cost but also through breakthrough medical achievements.\n\n"
                . "From complex organ transplants to precision medicine, Vietnamese doctors have conquered limits once thought impossible.\n\n"
                . "These are not just achievements — they are stories of revival.";

        return [
            'position' => AchievementSectionPosition::Hero,
            'translations' => [
                'vi' => [
                    'title'    => "Thành tựu Y khoa\ntiêu biểu tại Việt Nam",
                    'subtitle' => 'Khẳng định năng lực y học – Mở ra cơ hội sống mới',
                    'body'     => $bodyVi,
                    'items'    => $statsVi,
                ],
                'en' => [
                    'title'    => "Outstanding Medical Achievements\nin Vietnam",
                    'subtitle' => 'Affirming medical capability – Opening new chances of life',
                    'body'     => $bodyEn,
                    'items'    => $statsEn,
                ],
            ],
        ];
    }

    private function capabilities(): array
    {
        $itemsVi = [
            ['icon_path' => 'icon-doctors.png',           'title' => "ĐỘI NGŨ BÁC SĨ\nGIÀU KINH NGHIỆM", 'body' => 'Được đào tạo bài bản trong nước và quốc tế, không ngừng cập nhật kỹ thuật hiện đại'],
            ['icon_path' => 'icon-technique.png',         'title' => "Làm chủ kỹ thuật\nphức tạp",       'body' => 'Thực hiện thành công những ca phẫu thuật ở trình độ cao nhất thế giới'],
            ['icon_path' => 'icon-modern-tech.png',       'title' => "ứng dụng công nghệ\nhiện đại",     'body' => 'Từ công nghệ 3D, AI đến hệ thống robot và cảm biến tiên tiến'],
            ['icon_path' => 'icon-trust-destination.png', 'title' => 'ĐIỂM ĐẾN ĐÁNG TIN CẬY',           'body' => 'Chất lượng chuyên môn đạt chuẩn quốc tế và chi phí tối ưu'],
        ];
        $itemsEn = [
            ['icon_path' => 'icon-doctors.png',           'title' => "EXPERIENCED\nMEDICAL TEAM",       'body' => 'Trained locally and internationally, continuously updating modern techniques'],
            ['icon_path' => 'icon-technique.png',         'title' => "Mastery of\ncomplex techniques", 'body' => 'Successfully performing world-class surgeries'],
            ['icon_path' => 'icon-modern-tech.png',       'title' => "Application of\nmodern tech",    'body' => 'From 3D, AI to robotics and advanced sensors'],
            ['icon_path' => 'icon-trust-destination.png', 'title' => 'TRUSTED DESTINATION',             'body' => 'International-standard expertise with optimal cost'],
        ];

        return [
            'position' => AchievementSectionPosition::Capabilities,
            'translations' => [
                'vi' => ['title' => 'VIỆT NAM – NĂNG LỰC Y HỌC ĐANG VƯƠN TẦM QUỐC TẾ', 'items' => $itemsVi],
                'en' => ['title' => 'VIETNAM – MEDICAL CAPABILITY REACHING GLOBAL STANDARDS', 'items' => $itemsEn],
            ],
        ];
    }

    private function assurance(): array
    {
        $itemsVi = [
            ['icon_path' => 'icon-plan.png',       'body' => 'Hiểu rõ phương án điều trị trước khi quyết định'],
            ['icon_path' => 'icon-select.png',     'body' => 'Lựa chọn cơ sở y tế phù hợp nhất'],
            ['icon_path' => 'icon-coordinate.png', 'body' => 'Được điều phối và hỗ trợ xuyên suốt hành trình'],
            ['icon_path' => 'icon-quality.png',    'body' => 'An tâm với cam kết chất lượng dịch vụ'],
        ];
        $itemsEn = [
            ['icon_path' => 'icon-plan.png',       'body' => 'Understand treatment plans before deciding'],
            ['icon_path' => 'icon-select.png',     'body' => 'Choose the most suitable medical facility'],
            ['icon_path' => 'icon-coordinate.png', 'body' => 'Coordinated support throughout the journey'],
            ['icon_path' => 'icon-quality.png',    'body' => 'Peace of mind with our service-quality commitment'],
        ];

        return [
            'position' => AchievementSectionPosition::Assurance,
            'translations' => [
                'vi' => ['title' => 'VMTA – BẢO CHỨNG CHO HÀNH TRÌNH Y TẾ AN TOÀN', 'items' => $itemsVi],
                'en' => ['title' => 'VMTA – ASSURANCE FOR A SAFE MEDICAL JOURNEY', 'items' => $itemsEn],
            ],
        ];
    }

    private function casesPayload(): array
    {
        return [
            $this->caseHeartLung(),
            $this->caseKidneyAuto(),
            $this->caseHip3D(),
        ];
    }

    private function caseHeartLung(): array
    {
        return [
            'slug'       => 'ghep-dong-thoi-tim-phoi',
            'reverse'    => false,
            'sort_order' => 10,
            'translations' => [
                'vi' => [
                    'title'      => 'Kỳ tích ghép đồng thời tim – phổi',
                    'subtitle'   => 'Giải cứu bệnh nhân suy đa tạng giai đoạn cuối bằng kỹ thuật khó nhất thế giới',
                    'intro'      => 'Lần đầu tiên tại Việt Nam, ca ghép khối tim – phổi đã được thực hiện thành công mở ra cơ hội sống cho những bệnh nhân ở giai đoạn cuối.',
                    'col1_items' => ['Cải tiến nối phế quản gốc', 'Cắt giảm kích thước phổi phù hợp', 'Phác đồ gần 40 loại thuốc', 'Dinh dưỡng và phục hồi cá thể hóa'],
                    'col2_items' => ['Chức năng tim – phổi hồi phục ổn định', 'Tự thở và sinh hoạt trở lại', 'Tỷ lệ sống sau 1 năm: 72% – 90%', 'Tỷ lệ sống sau 5 năm: -60%'],
                    'col3_body'  => 'Đưa Việt Nam vào nhóm quốc gia làm chủ kỹ thuật ghép tạng phức tạp nhất thế giới',
                ],
                'en' => [
                    'title'      => 'Simultaneous heart–lung transplant milestone',
                    'subtitle'   => 'Saving end-stage multi-organ failure patients with one of the world\'s most demanding techniques',
                    'intro'      => 'For the first time in Vietnam, a combined heart–lung transplant was performed successfully, opening life chances for end-stage patients.',
                    'col1_items' => ['Improved bronchial anastomosis', 'Right-sized lung reduction', 'Nearly 40-drug protocol', 'Personalised nutrition and recovery'],
                    'col2_items' => ['Stable heart–lung function recovery', 'Patient breathes and lives independently', '1-year survival 72%–90%', '5-year survival ~60%'],
                    'col3_body'  => 'Places Vietnam among the nations mastering the world\'s most complex transplant techniques',
                ],
            ],
        ];
    }

    private function caseKidneyAuto(): array
    {
        return [
            'slug'       => 'ghep-than-tu-than',
            'reverse'    => true,
            'sort_order' => 20,
            'translations' => [
                'vi' => [
                    'title'      => 'Kỳ tích “GHÉP THẬN TỰ THÂN”',
                    'subtitle'   => 'Lần đầu tiên tại Việt Nam, kỹ thuật ghép thận tự thân giúp “cứu sống” quả thận thay vì phải cắt bỏ đã được thực hiện thành công.',
                    'intro'      => 'Lần đầu tiên tại Việt Nam, ca ghép khối tim – phổi đã được thực hiện thành công mở ra cơ hội sống cho những bệnh nhân ở giai đoạn cuối.',
                    'col1_items' => ['Đưa thận ra ngoài cơ thể để xử lý', 'Vì phẫu tái tạo mạch máu trong môi trường kiểm soát', 'oàn tất trong thời gian tối ưu', 'Ghép lại với độ chính xác cao'],
                    'col2_items' => ['Bảo tồn 100% chức năng thận', 'Không cần dùng thuốc chống thải ghép', 'Hồi phục nhanh, ổn định lâu dài'],
                    'col3_body'  => 'Mở ra hướng điều trị mới cho các ca bệnh phức tạp, giúp người bệnh giữ lại cơ quan của chính mình',
                ],
                'en' => [
                    'title'      => 'Autotransplant kidney milestone',
                    'subtitle'   => 'For the first time in Vietnam, autologous kidney transplantation saved the kidney instead of removing it.',
                    'intro'      => 'A new technique that preserves the patient\'s own kidney, performed successfully in Vietnam for the first time.',
                    'col1_items' => ['Kidney removed for ex-vivo handling', 'Vascular reconstruction in a controlled environment', 'Completed within optimal time', 'Reimplantation with high precision'],
                    'col2_items' => ['100% kidney function preserved', 'No anti-rejection drugs required', 'Fast recovery, long-term stability'],
                    'col3_body'  => 'Opens new treatment paths for complex cases, letting patients keep their own organ',
                ],
            ],
        ];
    }

    private function caseHip3D(): array
    {
        return [
            'slug'       => 'thay-khop-hang-in-3d',
            'reverse'    => false,
            'sort_order' => 30,
            'translations' => [
                'vi' => [
                    'title'      => 'THAY KHỚP HÁNG TOÀN PHẦN BẰNG CÔNG NGHỆ IN 3D',
                    'subtitle'   => 'Giải cứu bệnh nhân suy đa tạng giai đoạn cuối bằng kỹ thuật khó nhất thế giới',
                    'intro'      => 'Lần đầu tiên tại Việt Nam, bệnh nhi 12 tuổi được thay khớp hàng toàn phần bằng công nghệ in 3D và thiết bị định vị PSI thiết kế riêng biệt.',
                    'col1_items' => ['Mô phỏng 3D “phẫu thuật thử”', 'Thiết bị định vị PSI cá thể hóa', 'Vật liệu Titan siêu bền', 'Phục hồi chức năng bằng cảm biến vận động'],
                    'col2_items' => ['24h sau mổ: tự ngồi và tập đi', 'Sau 2 tháng: dáng đi tự nhiên', 'Chức năng vận động đạt 90% so với người khỏe mạnh', 'Đau nhẹ, hồi phục nhanh'],
                    'col3_body'  => 'Đánh dấu bước tiến lớn của y học cá thể hóa trong lĩnh vực chấn thương chỉnh hình nhi khoa',
                ],
                'en' => [
                    'title'      => 'TOTAL HIP REPLACEMENT USING 3D PRINTING',
                    'subtitle'   => 'A milestone in pediatric orthopaedics using 3D-printed implants and patient-specific PSI guides',
                    'intro'      => 'For the first time in Vietnam, a 12-year-old underwent total hip replacement using 3D printing and a dedicated PSI device.',
                    'col1_items' => ['"Surgical rehearsal" via 3D simulation', 'Patient-specific PSI guide', 'Highly durable titanium material', 'Rehabilitation with motion sensors'],
                    'col2_items' => ['24 h post-op: sits and walks unaided', 'After 2 months: natural gait', 'Motor function ~90% of healthy peers', 'Low pain, fast recovery'],
                    'col3_body'  => 'A major step forward for personalised medicine in pediatric orthopaedic trauma',
                ],
            ],
        ];
    }
}
