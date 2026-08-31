<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Models\AboutSection;

/**
 * Seeds 7 about-page sections with VI content verbatim from gioi-thieu.blade.php.
 * EN defaults to VI copy — content team translates later.
 * Idempotent via firstOrNew.
 */
class AboutSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->payload() as $row) {
                $position = $row['position'];
                $section  = AboutSection::firstOrNew(['position' => $position->value]);
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
            $this->whoAre(),
            $this->coreValues(),
            $this->howWorks(),
            $this->difference(),
            $this->whyChoose(),
            $this->startWithUs(),
        ];
    }

    private function hero(): array
    {
        $vi = [
            'title'     => 'VMTA – Kiến trúc sư trưởng cho hệ sinh thái du lịch việt nam',
            'body'      => 'Kết nối y tế – nghỉ dưỡng – công nghệ trong một hệ điều hành thống nhất mang đến hành trình chăm sóc sức khỏe toàn diện và cá nhân hóa.',
            'cta_label' => 'Khám Phá Hành trình',
            'subtitle'  => 'tham gia hệ sinh thái',
        ];

        return ['position' => AboutSectionPosition::Hero, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function whoAre(): array
    {
        $items = [
            ['title' => 'Với khách hàng', 'body' => 'Thiết kế những hành trình chăm sóc sức khỏe an toàn, minh bạch và cá nhân hóa, giúp khách hàng an tâm khi điều trị.'],
            ['title' => 'Với đối tác',    'body' => 'Xây dựng hệ điều hành kết nối thông minh, giúp tối ưu công suất, chuẩn hóa vận hành và nâng cao giá trị thương hiệu.'],
            ['title' => 'Với ngành',      'body' => 'Góp phần định hình một hệ sinh thái Du lịch Y tế chuyên nghiệp, minh bạch và bền vững tại Việt Nam.'],
        ];
        $vi = [
            'title' => 'VMTA Là ai ?',
            'body'  => "Không chỉ là dịch vụ. VMTA là một \"hệ điều hành\" cho du lịch Y tế Việt Nam\n\nVMTA là liên minh chiến lược tiên phong tại Việt Nam, kết nối các bệnh viện hàng đầu, khu nghỉ dưỡng cao cấp và đối tác vận hành trong một hệ sinh thái khép kín.\n\nChúng tôi thiết kế và điều phối toàn bộ hành trình chăm sóc sức khỏe – từ thẩm định hồ sơ, điều trị đến phục hồi – đảm bảo sự liền mạch, an toàn và hiệu quả.\n\nVMTA hướng đến sứ mệnh:",
            'items' => $items,
        ];

        return ['position' => AboutSectionPosition::WhoAre, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function coreValues(): array
    {
        $items = [
            ['title' => 'ĐỊNH CHUẨN QUỐC TẾ',   'body' => 'Xây dựng và áp dụng bộ tiêu chuẩn thẩm định nghiêm ngặt đối với toàn bộ đối tác trong hệ sinh thái.'],
            ['title' => 'Điều phối tập trung',    'body' => 'Quản trị toàn bộ hành trình khách hàng thông qua hệ thống điều phối trung tâm, đảm bảo sự liền mạch và chính xác.'],
            ['title' => 'Y Khoa nhân văn',        'body' => 'Kết hợp điều trị y khoa với chăm sóc tinh thần và môi trường phục hồi, hướng đến sự cân bằng toàn diện.'],
        ];
        $vi = ['title' => 'giá trị cốt lõi của vmta', 'items' => $items];

        return ['position' => AboutSectionPosition::CoreValues, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function howWorks(): array
    {
        $items = [
            ['title' => 'Thẩm định hồ sơ từ xa',          'body' => 'Đánh giá tình trạng và đề xuất phương án điều trị phù hợp'],
            ['title' => 'Thiết kế hành trình cá nhân hóa', 'body' => 'Xây dựng lộ trình điều trị và phục hồi dựa trên nhu cầu cụ thể'],
            ['title' => 'Điều phối điều trị',              'body' => 'Kết nối và vận hành giữa các đơn vị trong hệ sinh thái'],
            ['title' => 'Theo dõi và chăm sóc hậu điều trị', 'body' => 'Đảm bảo quá trình phục hồi được kiểm soát và tối ưu'],
        ];
        $vi = ['title' => 'Cách VMTA Hoạt Động', 'items' => $items];

        return ['position' => AboutSectionPosition::HowWorks, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function difference(): array
    {
        $items = [
            ['text' => 'Thẩm định nghiêm ngặt trước khi đưa vào hệ sinh thái'],
            ['text' => 'Quản trị toàn bộ hành trình thay vì cung cấp dịch vụ rời rạc'],
            ['text' => 'Ứng dụng công nghệ để theo dõi và tối ưu trải nghiệm'],
            ['text' => 'Đồng hành cùng khách hàng và đối tác trong dài hạn'],
        ];
        $vi = ['title' => 'KHÁC BIỆT CỦA VMTA', 'items' => $items];

        return ['position' => AboutSectionPosition::Difference, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function whyChoose(): array
    {
        $items = [
            ['title' => 'Năng lực y khoa ngày càng nâng cao',          'body' => 'Đội ngũ y bác sĩ giỏi, trang thiết bị hiện đại, chuyên môn đạt chuẩn quốc tế'],
            ['title' => 'Hệ thống nghỉ dưỡng phát triển mạnh',         'body' => 'Nhiều khu nghỉ dưỡng cao cấp tại những điểm đến hấp dẫn'],
            ['title' => 'Chi phí cạnh tranh trên thị trường quốc tế',  'body' => 'Chất lượng tốt với chi phí hợp lý, giúp tối ưu hiệu quả điều trị'],
            ['title' => 'Thị trường cần một đơn vị điều phối & Bảo chứng', 'body' => 'Khoảng trống về đơn vị quản trị toàn diện và bảo chứng chất lượng'],
        ];
        $vi = ['title' => 'TẠI SAO NÊN LỰA CHỌN VMTA', 'items' => $items];

        return ['position' => AboutSectionPosition::WhyChoose, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function startWithUs(): array
    {
        $vi = [
            'title'     => 'Bắt đầu hành trình cùng VMTA',
            'body'      => "Dù bạn là khách hàng đang tìm kiếm giải pháp chăm sóc sức khỏe, hay đối tác mong muốn tham gia hệ sinh thái. VMTA luôn sẵn sàng đồng hành.",
            'cta_label' => 'NHẬN TƯ VẤN',
            'subtitle'  => 'tham gia hệ sinh thái',
        ];

        return ['position' => AboutSectionPosition::StartWithUs, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }
}
