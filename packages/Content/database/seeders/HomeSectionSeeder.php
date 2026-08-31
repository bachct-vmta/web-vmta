<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\HomeSection;

/**
 * Seeds 8 home sections with VI content verbatim from live vmta.vn (VMTA.html)
 * and EN placeholder copy (TODO: content team translate to English).
 * Idempotent via firstOrNew.
 */
class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->payload() as $row) {
                /** @var HomeSectionPosition $position */
                $position = $row['position'];

                $section = HomeSection::firstOrNew(['position' => $position->value]);
                $section->position = $position;
                $section->is_active = true;
                $section->sort_order = $position->defaultSortOrder();
                $section->video_url = $row['video_url'] ?? null;
                $section->image_media_id = $row['image_media_id'] ?? null;
                $section->save();

                foreach ($row['translations'] as $locale => $payload) {
                    $section->translateOrNew($locale)->fill($payload)->save();
                }
            }
        });
    }

    /**
     * @return array<int, array{
     *   position: HomeSectionPosition,
     *   video_url?: string|null,
     *   image_media_id?: int|null,
     *   translations: array<string, array<string, mixed>>
     * }>
     */
    private function payload(): array
    {
        return [
            $this->hero(),
            $this->values(),
            $this->about(),
            $this->solutions(),
            $this->visionMission(),
            $this->benefits(),
            $this->technology(),
            $this->whyVN(),
        ];
    }

    private function hero(): array
    {
        // marquee items verbatim from VMTA.html lines 311-320
        $marquee = [
            ['label' => 'USA',      'value' => '10 000'],
            ['label' => 'Cambodia', 'value' => '10 000'],
            ['label' => 'France',   'value' => '10 000'],
            ['label' => 'Germany',  'value' => '10 000'],
        ];

        return [
            'position' => HomeSectionPosition::Hero,
            'translations' => [
                'vi' => [
                    'title' => 'Liên minh Du lịch Y tế Việt Nam',
                    'subtitle' => 'Kiến trúc sư trưởng cho hệ sinh thái Du lịch Y tế tầm vóc quốc gia',
                    'body' => 'Là liên minh du lịch y tế chính thức của Việt Nam thiết lập mô hình vận hành khép kín giữa các Bệnh viện hạng đặc biệt, các Khu nghỉ dưỡng cao cấp và đơn vị Công nghệ vận hành.',
                    'cta_label' => 'Tham gia hệ sinh thái',
                    'cta_url' => '/vi/contact',
                    'items' => $marquee,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'Liên minh Du lịch Y tế Việt Nam',
                    'subtitle' => 'Kiến trúc sư trưởng cho hệ sinh thái Du lịch Y tế tầm vóc quốc gia',
                    'body' => 'Là liên minh du lịch y tế chính thức của Việt Nam thiết lập mô hình vận hành khép kín giữa các Bệnh viện hạng đặc biệt, các Khu nghỉ dưỡng cao cấp và đơn vị Công nghệ vận hành.',
                    'cta_label' => 'Tham gia hệ sinh thái',
                    'cta_url' => '/en/contact',
                    'items' => $marquee,
                ],
            ],
        ];
    }

    private function values(): array
    {
        $viItems = [
            [
                'icon' => 'icon-1.png',
                'title' => 'Định chuẩn quốc tế',
                'body' => 'Thiết lập bộ tiêu chuẩn vận hành độc lập (Alliance Standards)...',
            ],
            [
                'icon' => 'icon-2.png',
                'title' => 'Điều phối tập trung',
                'body' => 'Một trung tâm vận hành duy nhất quản trị toàn bộ hành trình...',
            ],
            [
                'icon' => 'icon-3.png',
                'title' => 'Y thuật nhân văn',
                'body' => 'Kết hợp y học chuyên sâu với không gian nghỉ dưỡng...',
            ],
        ];

        return [
            'position' => HomeSectionPosition::Values,
            'translations' => [
                'vi' => [
                    'title' => 'GIÁ TRỊ CỐT LÕI',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'GIÁ TRỊ CỐT LÕI',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function about(): array
    {
        $viItems = [
            ['bullet' => 'Kết nối nguồn lực y tế'],
            ['bullet' => 'Chuẩn hóa toàn bộ hành trình'],
            ['bullet' => 'Tối ưu hiệu quả điều trị và phục hồi'],
        ];

        return [
            'position' => HomeSectionPosition::About,
            'translations' => [
                'vi' => [
                    'title' => 'VỀ VMTA',
                    'body' => "Không chỉ là dịch vụ. VMTA là một 'Hệ điều hành' cho Du lịch Y tế. VMTA là liên minh chiến lược tiên phong tại Việt Nam kết nối các bệnh viện hàng đầu, khu nghỉ dưỡng cao cấp và nền tảng công nghệ điều phối...",
                    'cta_label' => 'Tìm hiểu thêm',
                    'cta_url' => '/vi/gioi-thieu',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'VỀ VMTA',
                    'body' => "Không chỉ là dịch vụ. VMTA là một 'Hệ điều hành' cho Du lịch Y tế. VMTA là liên minh chiến lược tiên phong tại Việt Nam kết nối các bệnh viện hàng đầu, khu nghỉ dưỡng cao cấp và nền tảng công nghệ điều phối...",
                    'cta_label' => 'Tìm hiểu thêm',
                    'cta_url' => '/en/about',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function solutions(): array
    {
        $viItems = [
            [
                'icon' => 'icon-1.png',
                'title' => 'Quy trình Thẩm định Chuẩn Liên minh',
                'body' => 'Quy trình thẩm định đối tác chặt chẽ...',
            ],
            [
                'icon' => 'icon-2.png',
                'title' => 'Trung tâm Điều phối Vận hành',
                'body' => 'Hệ thống điều phối trung tâm đảm bảo hành trình liền mạch...',
            ],
            [
                'icon' => 'icon-3.png',
                'title' => 'Tích hợp Y tế & Nghỉ dưỡng',
                'body' => 'Giải pháp tích hợp kết hợp phác đồ điều trị...',
            ],
        ];

        return [
            'position' => HomeSectionPosition::Solutions,
            'translations' => [
                'vi' => [
                    'title' => 'GIẢI PHÁP THEN CHỐT',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'GIẢI PHÁP THEN CHỐT',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function visionMission(): array
    {
        $viItems = [
            [
                'audience' => 'Với khách hàng',
                'body' => 'Mang đến hành trình chăm sóc sức khỏe an toàn...',
            ],
            [
                'audience' => 'Với đối tác',
                'body' => 'Xây dựng hệ điều hành kết nối thông minh...',
            ],
            [
                'audience' => 'Với ngành',
                'body' => 'Định hình một hệ sinh thái Du lịch Y tế...',
            ],
        ];

        return [
            'position' => HomeSectionPosition::VisionMission,
            'video_url' => 'https://storageovp.vnews.gov.vn//mediacache//2026//04//10//TS_QTND_9520_DU//9NIWHWEJC38D//hls//master.m3u8',
            'translations' => [
                'vi' => [
                    'title' => 'TẦM NHÌN',
                    'subtitle' => 'SỨ MỆNH',
                    'body' => 'Trở thành biểu tượng bảo chứng cho chất lượng Du lịch Y tế tại Việt Nam, đưa Việt Nam trở thành điểm đến ưu tiên trên bản đồ y khoa toàn cầu.',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'TẦM NHÌN',
                    'subtitle' => 'SỨ MỆNH',
                    'body' => 'Trở thành biểu tượng bảo chứng cho chất lượng Du lịch Y tế tại Việt Nam, đưa Việt Nam trở thành điểm đến ưu tiên trên bản đồ y khoa toàn cầu.',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function benefits(): array
    {
        $viItems = [
            [
                'audience' => 'Dành cho khách hàng',
                'subtitle' => 'Hành trình chữa lành an tâm & chất lượng',
                'bullets' => [
                    'Thẩm định y khoa trước khi khởi hành',
                    'Cá nhân hóa toàn bộ trải nghiệm',
                    'Không cần tự kết nối các dịch vụ rời rạc',
                    'Được bảo chứng chất lượng tại mọi điểm chạm',
                ],
                'image_url' => 'benefits/row-1.jpg',
            ],
            [
                'audience' => 'Dành cho đối tác (Bệnh viện / Resort)',
                'subtitle' => 'Tối ưu nguồn lực & nâng tầm vị thế',
                'bullets' => [
                    'Tiếp cận tệp khách hàng từ ngoài nước',
                    'Chuẩn hóa quy trình vận hành theo chuẩn dịch vụ (SLA)',
                    'Gia tăng công suất khai thác và hiệu quả vận hành',
                    'Nâng cao hình ảnh thương hiệu trong hệ sinh thái',
                ],
                'image_url' => 'benefits/row-2.jpg',
            ],
        ];

        return [
            'position' => HomeSectionPosition::Benefits,
            'translations' => [
                'vi' => [
                    'title' => 'LỢI ÍCH ĐA TẦNG',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'LỢI ÍCH ĐA TẦNG',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function technology(): array
    {
        $viItems = [
            [
                'title' => 'Trung tâm Điều phối Vận hành – Bộ não Của liên minh',
                'bullets' => [
                    'Hệ thống tiếp nhận hồ sơ thông minh',
                    'Hệ thống điều phối lịch trình theo thời gian thực',
                ],
            ],
            [
                'title' => 'Quản trị sự phục hồi dựa trên dữ liệu',
                'bullets' => [
                    'Hệ thống quản lý dữ liệu hậu điều trị',
                    'Hệ thống phân tích và cá nhân hóa chăm sóc sức khỏe',
                ],
            ],
        ];

        return [
            'position' => HomeSectionPosition::Technology,
            'translations' => [
                'vi' => [
                    'title' => 'CÔNG NGHỆ & SỰ KHÁC BIỆT',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'CÔNG NGHỆ & SỰ KHÁC BIỆT',
                    'items' => $viItems,
                ],
            ],
        ];
    }

    private function whyVN(): array
    {
        $viItems = [
            [
                'icon' => 'icon-1.png',
                'title' => 'Tiềm năng bản địa',
                'body' => 'Việt Nam mạnh về IVF, Nha khoa và Phẫu thuật thẩm mỹ với chi phí cạnh tranh',
            ],
            [
                'icon' => 'icon-2.png',
                'title' => 'Hạ tầng',
                'body' => "Hệ thống resort wellness sẵn sàng là 'trạm phục hồi' tốt nhất thế giới",
            ],
            [
                'icon' => 'icon-3.png',
                'title' => 'Vai trò VMTA',
                'body' => 'Thực thể uy tín đứng ra thẩm định, kết nối và cam kết chất lượng cho cả hành trình phức tạp',
            ],
        ];

        return [
            'position' => HomeSectionPosition::WhyVN,
            'translations' => [
                'vi' => [
                    'title' => 'TẠI SAO CHỌN VIỆT NAM & VMTA?',
                    'items' => $viItems,
                ],
                'en' => [
                    // TODO: content team translate to English
                    'title' => 'TẠI SAO CHỌN VIỆT NAM & VMTA?',
                    'items' => $viItems,
                ],
            ],
        ];
    }
}
