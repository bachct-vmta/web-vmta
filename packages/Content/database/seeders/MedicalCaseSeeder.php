<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\MedicalCase;

class MedicalCaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $case = MedicalCase::firstOrNew(['slug' => 'ghep-dong-thoi-tim-phoi']);

            if (! $case->exists) {
                $case->fill([
                    'image_media_id' => null,
                    'reverse' => false,
                    'sort_order' => 10,
                    'is_active' => true,
                ]);
                $case->save();
            }

            foreach ($this->translations() as $locale => $payload) {
                $translation = $case->translateOrNew($locale);
                if ($translation->exists && ! empty($translation->detail_content)) {
                    continue;
                }
                $translation->fill($payload)->save();
            }
        });
    }

    private function translations(): array
    {
        return [
            'vi' => $this->vi(),
            'en' => $this->en(),
        ];
    }

    private function vi(): array
    {
        return [
            'title' => 'Kỳ tích ghép đồng thời tim – phổi',
            'subtitle' => 'Cơ hội sống cho bệnh nhân suy đa tạng giai đoạn cuối',
            'intro' => 'Bệnh viện Hữu nghị Việt Đức thực hiện thành công ca ghép đồng thời tim và phổi đầu tiên tại Việt Nam.',
            'col1_items' => ['Cải tiến nối phế quản gốc', 'Cắt giảm kích thước phổi phù hợp', 'Phác đồ gần 40 loại thuốc'],
            'col2_items' => ['Chức năng tim – phổi hồi phục ổn định', 'Tự thở và sinh hoạt trở lại', 'Tỷ lệ sống sau 1 năm: 72% – 90%'],
            'col3_body' => 'Đưa Việt Nam vào nhóm quốc gia làm chủ kỹ thuật ghép tạng phức tạp nhất thế giới.',
            'detail_content' => [
                'hero_eyebrow' => 'KỲ TÍCH Y KHOA TẠI VIỆT NAM',
                'hero_title' => 'GHÉP ĐỒNG THỜI',
                'hero_highlight' => 'TIM – PHỔI',
                'hero_body' => 'Bệnh viện Hữu nghị Việt Đức thực hiện thành công ca ghép đồng thời tim và phổi đầu tiên tại Việt Nam, đánh dấu bước tiến vượt bậc của y học nước nhà trong lĩnh vực ghép tạng.',
                'cta_label' => 'NHẬN TƯ VẤN PHƯƠNG ÁN ĐIỀU TRỊ',
                'hospital_name' => 'BỆNH VIỆN HỮU NGHỊ VIỆT ĐỨC',
                'time_value' => '08/2025',
                'intro_body' => "Tháng 8/2025, Bệnh viện Hữu nghị Việt Đức xác lập cột mốc lịch sử khi thực hiện thành công ca ghép đồng thời tim và phổi cho bệnh nhân nữ 38 tuổi. Đây là kỹ thuật ghép tạng phức tạp nhất thế giới, mở ra hy vọng sống cho những bệnh nhân suy tạng giai đoạn cuối.\n\nCác đột phá kỹ thuật cốt lõi:\n\n- Cải tiến miệng nối: Kỹ thuật nối hai phế quản gốc giúp tối ưu hóa tưới máu và ngăn ngừa hoại tử.\n- Tạo hình phổi (Volume reduction): Phẫu thuật cắt giảm kích thước phổi người hiến để tương thích hoàn hảo với lồng ngực người nhận.\n- Hồi sức đa chuyên khoa: Phối hợp gần 40 loại thuốc cùng hệ thống lọc máu hiện đại để chống thải ghép và bảo vệ đa cơ quan.\n\nKết quả: Bệnh nhân phục hồi ổn định, tự thở và trở lại cuộc sống bình thường. Thành tựu này chính thức đưa Việt Nam lên bản đồ các quốc gia làm chủ hoàn toàn kỹ thuật ghép tạng khó nhất thế giới.",
                'reason_items' => [
                    ['icon' => 'icon-complex.png', 'title' => 'Kỹ thuật ghép tạng phức tạp bậc nhất', 'body' => 'Được xem là một trong những kỹ thuật khó nhất trong y học hiện đại'],
                    ['icon' => 'icon-teamwork.png', 'title' => 'Phối hợp đa chuyên khoa', 'body' => 'Tim mạch – Hô hấp – Hồi sức – Miễn dịch phối hợp chặt chẽ'],
                    ['icon' => 'icon-personalized.png', 'title' => 'Cá nhân hóa điều trị', 'body' => 'Phác đồ được thiết kế riêng cho từng bệnh nhân, tối ưu hiệu quả'],
                    ['icon' => 'icon-doctors.png', 'title' => 'Đội ngũ hàng đầu', 'body' => 'Bác sĩ giàu kinh nghiệm, đã xử lý nhiều ca bệnh phức tạp'],
                ],
                'breakthrough_left_items' => [
                    ['icon' => 'icon-airway.png', 'title' => 'Cải tiến miệng nối phế quản', 'body' => 'Thay vì nối khí quản theo phương pháp truyền thống, các bác sĩ nối hai phế quản gốc để đảm bảo tưới máu tốt nhất.'],
                    ['icon' => 'icon-volume.png', 'title' => 'Kỹ thuật cắt giảm kích thước phổi', 'body' => 'Phổi người hiến to hơn lồng ngực người nhận được phẫu thuật cắt giảm kích thước để phù hợp hoàn hảo.'],
                    ['icon' => 'icon-immune.png', 'title' => 'Phối hợp gần 40 loại thuốc', 'body' => 'Sử dụng phác đồ phối hợp cùng hệ thống lọc máu hiện đại để vừa chống thải ghép mạnh mẽ, vừa bảo vệ chức năng thận và kiểm soát vi khuẩn kháng thuốc.'],
                    ['icon' => 'icon-nutrition.png', 'title' => 'Quy trình cá nhân hóa', 'body' => 'Chế độ dinh dưỡng tĩnh mạch và phục hồi chức năng phổi được thiết kế riêng biệt cho bệnh nhân suy kiệt nặng.'],
                ],
                'breakthrough_right_items' => [
                    ['icon' => 'icon-oxygen.png', 'title' => 'Tự tiên lượng và sống sót', 'body' => 'Từ một người suy đa tạng, người bệnh đã có thể tự thở và dần quay lại cuộc sống bình thường.'],
                    ['icon' => 'icon-survival.png', 'title' => 'Tỷ lệ hồi phục khả quan', 'body' => 'Tỷ lệ sống sau 1 năm có thể lên đến 72%–90% và sau 5 năm đạt khoảng 60% – một bước nhảy vọt so với các phương pháp nội khoa truyền thống.'],
                    ['icon' => 'icon-vietnam.png', 'title' => 'Khẳng định bản lĩnh y học Việt Nam', 'body' => 'Việt Nam chính thức ghi danh vào bản đồ các quốc gia làm chủ kỹ thuật ghép tạng khó nhất thế giới.'],
                ],
                'breakthrough_note' => 'Ca ghép tim – phổi đầu tiên này không chỉ cứu sống một mạng người mà còn tạo tiền đề vững chắc cho chương trình ghép đa tạng tại Việt Nam.',
                'choice_items' => [
                    ['title' => 'Chi phí điều trị tối ưu', 'body' => 'Chi phí hợp lý hơn nhiều quốc gia nhưng chất lượng đạt chuẩn quốc tế.'],
                    ['title' => 'Đội ngũ bác sĩ giỏi', 'body' => 'Nhiều bác sĩ được đào tạo chuyên sâu tại các trung tâm y khoa hàng đầu thế giới.'],
                    ['title' => 'Hệ thống hiện đại', 'body' => 'Trang thiết bị tiên tiến, hồi sức chuyên sâu và chăm sóc toàn diện.'],
                    ['title' => 'Phục hồi toàn diện', 'body' => 'Kết hợp điều trị và phục hồi trong môi trường nghỉ dưỡng lý tưởng.'],
                ],
                'process_items' => [
                    ['body' => 'Đánh giá hồ sơ bệnh lý trước khi quyết định'],
                    ['body' => 'Kết nối với bệnh viện phù hợp'],
                    ['body' => 'Thiết kế lộ trình điều trị rõ ràng'],
                    ['body' => 'Đồng hành xuyên suốt hành trình'],
                ],
                'form_title' => "NHẬN TƯ VẤN\nPHƯƠNG ÁN ĐIỀU TRỊ PHÙ HỢP",
                'form_body' => 'Đội ngũ chuyên gia của VMTA sẽ đánh giá hồ sơ và đề xuất giải pháp tối ưu dành riêng cho bạn.',
                'cta_title' => "MỘT QUYẾT ĐỊNH ĐÚNG\nCÓ THỂ THAY ĐỔI CẢ CUỘC ĐỜI",
                'cta_body' => 'Đừng bỏ lỡ cơ hội tiếp cận những giải pháp y khoa tiên tiến.',
                'cta_points' => ['Hiểu rõ phương án điều trị', 'Lựa chọn bệnh viện phù hợp', 'Được đồng hành toàn diện'],
            ],
        ];
    }

    private function en(): array
    {
        $payload = $this->vi();
        $payload['title'] = 'Simultaneous heart – lung transplantation milestone';
        $payload['subtitle'] = 'A new chance for end-stage multi-organ failure patients';
        $payload['detail_content']['hero_eyebrow'] = 'MEDICAL MILESTONE IN VIETNAM';
        $payload['detail_content']['hero_title'] = 'SIMULTANEOUS';
        $payload['detail_content']['hero_highlight'] = 'HEART – LUNG TRANSPLANTATION';
        return $payload;
    }
}
