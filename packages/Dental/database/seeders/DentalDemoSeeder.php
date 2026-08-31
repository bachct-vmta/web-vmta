<?php

namespace Packages\Dental\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Core\Src\Models\MediaFile;
use Packages\Dental\Src\Enums\PublishStatus;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Models\DentalService;

/**
 * Dữ liệu mẫu dựng lại đúng nội dung trong Figma để đối chiếu giao diện.
 * Không gọi từ DatabaseSeeder — chạy tay khi cần: php artisan db:seed --class=...
 */
class DentalDemoSeeder extends Seeder
{
    private const ASSET_DIR = 'branches/kham-nha';

    public function run(): void
    {
        $this->call(DentalCategorySeeder::class);

        $sample = $this->media('facility-sample.jpg', 'Ảnh cơ sở mẫu');

        $hospitals = DentalCategory::whereTranslation('slug', 'benh-vien', 'vi')->first();
        $clinics = DentalCategory::whereTranslation('slug', 'phong-kham', 'vi')->first();

        foreach (range(1, 5) as $i) {
            $this->facility($hospitals, 'Bệnh Viện '.$i, $sample, $i - 1);
        }

        foreach (range(1, 5) as $i) {
            $this->facility($clinics, 'Phòng Khám '.$i, $sample, $i - 1);
        }

        $first = DentalFacility::whereTranslation('slug', 'benh-vien-1', 'vi')->first();
        $first->update(['certificates_media_ids' => $this->certificateIds()]);

        foreach ($this->services() as $index => [$title, $icon]) {
            $this->service($first, $title, $icon, $index);
        }
    }

    private function facility(DentalCategory $category, string $name, MediaFile $cover, int $order): DentalFacility
    {
        $slug = \Illuminate\Support\Str::slug($name);

        $facility = DentalFacility::whereTranslation('slug', $slug, 'vi')->first()
            ?? new DentalFacility;

        $facility->fill([
            'dental_category_id' => $category->id,
            'status' => PublishStatus::Published->value,
            'published_at' => now(),
            'is_operating' => true,
            'cover_media_id' => $cover->id,
            'sort_order' => $order,
        ])->save();

        $facility->translateOrNew('vi')->fill([
            'name' => $name,
            'slug' => $slug,
            'address' => '123 Nguyễn Văn Cừ, HCM',
        ]);
        $facility->save();

        return $facility;
    }

    private function service(DentalFacility $facility, string $title, string $iconFile, int $order): void
    {
        $slug = \Illuminate\Support\Str::slug($title);

        $service = DentalService::where('dental_facility_id', $facility->id)
            ->whereTranslation('slug', $slug, 'vi')->first() ?? new DentalService;

        $service->fill([
            'dental_facility_id' => $facility->id,
            'status' => PublishStatus::Published->value,
            'published_at' => now(),
            'icon_media_id' => $this->media($iconFile, $title)->id,
            'video_url' => 'https://youtu.be/aqz-KE-bpKQ',
            'sort_order' => $order,
        ])->save();

        $service->translateOrNew('vi')->fill([
            'title' => $title,
            'slug' => $slug,
            'hero_h1' => 'Những điều bạn cần biết khi '.mb_strtolower($title),
            'video_caption' => 'Khách chia sẻ về việc '.mb_strtolower($title).' ở nha khoa',
            'comparison_html' => $this->comparisonHtml(),
            'price_table_html' => $this->priceTableHtml(),
        ]);
        $service->save();
    }

    private function media(string $file, string $alt): MediaFile
    {
        $permalink = self::ASSET_DIR.'/'.$file;

        return MediaFile::firstOrCreate(
            ['permalink' => $permalink],
            ['name' => $file, 'alt' => $alt, 'size' => 0, 'mine_type' => str_ends_with($file, '.png') ? 'image/png' : 'image/jpeg'],
        );
    }

    /**
     * @return array<int, int>
     */
    private function certificateIds(): array
    {
        return collect(['icon-boc-rang-su.png', 'icon-cay-ghep-implant.png', 'icon-nieng-rang.png',
            'icon-tay-trang-rang.png', 'icon-nho-rang-khon.png', 'icon-dieu-tri-tuy.png'])
            ->map(fn (string $f) => $this->media($f, 'Chứng nhận')->id)
            ->all();
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    private function services(): array
    {
        return [
            ['Bọc Răng Sứ', 'icon-boc-rang-su.png'],
            ['Cấy Ghép Implant', 'icon-cay-ghep-implant.png'],
            ['Niềng Răng Thẩm Mỹ', 'icon-nieng-rang.png'],
            ['Mặt Dán Sứ Veneer', 'icon-mat-dan-su-veneer.png'],
            ['Tẩy Trắng Răng', 'icon-tay-trang-rang.png'],
            ['Nhổ Răng Khôn', 'icon-nho-rang-khon.png'],
            ['Bệnh Lý Nha Chu', 'icon-benh-ly-nha-chu.png'],
            ['Điều Trị Tuỷ', 'icon-dieu-tri-tuy.png'],
            ['Cạo Vôi Răng', 'icon-cao-voi-rang.png'],
            ['Chăm Sóc Răng Miệng Cho Thai Phụ', 'icon-cham-soc-rang-mieng-thai-phu.png'],
            ['Nha Khoa Trẻ Em', 'icon-nha-khoa-tre-em.png'],
        ];
    }

    private function comparisonHtml(): string
    {
        return <<<'HTML'
<table><thead><tr><th>Răng sứ kim loại</th><th>Răng toàn sứ</th><th>Dán sứ</th></tr></thead>
<tbody><tr>
<td><ul><li>Thành phần là sườn kim loại phủ sứ.</li><li>Khả năng chịu lực tốt.</li><li>Tính thẩm mỹ không cao do dễ thấy sườn kim loại bên trong khi có ánh sáng chiếu vào.</li><li>Thường sử dụng cho răng hàm.</li></ul></td>
<td><ul><li>Thành phần 100% làm từ sứ.</li><li>Thẩm mỹ đẹp như răng thật.</li><li>Khả năng chịu lực tốt.</li><li>Không gây dị ứng cho người sử dụng.</li></ul></td>
<td><ul><li>Thành phần 100% làm từ sứ.</li><li>Thẩm mỹ đẹp tự nhiên như răng thật.</li><li>Rất mỏng, hạn chế mài răng, độ cứng, độ đàn hồi cao.</li><li>Phù hợp với khách hàng có cung răng đều.</li></ul></td>
</tr></tbody></table>
HTML;
    }

    private function priceTableHtml(): string
    {
        return <<<'HTML'
<table><thead><tr><th>Dịch Vụ</th><th>Bảo Hành</th><th>ĐVT</th><th>Giá (VNĐ)<small>*Chưa bao gồm thuế GTGT</small></th><th>Thuế GTGT</th></tr></thead>
<tbody>
<tr><td colspan="5">RĂNG SỨ KIM LOẠI</td></tr>
<tr><td>Răng sứ kim loại Titan</td><td>3 năm</td><td>Răng</td><td>3.000.000</td><td>8%</td></tr>
<tr><td colspan="5">RĂNG TOÀN SỨ</td></tr>
<tr><td>Răng toàn sứ Argen</td><td>7 năm</td><td>Răng</td><td>6.000.000</td><td>8%</td></tr>
<tr><td>Răng toàn sứ Sagemax</td><td>10 năm</td><td>Răng</td><td>8.800.000</td><td>8%</td></tr>
<tr><td>Răng toàn sứ Ceramill</td><td>10 năm</td><td>Răng</td><td>10.500.000</td><td>8%</td></tr>
<tr><td colspan="5">DÁN SỨ</td></tr>
<tr><td>Dán sứ Emax</td><td>7 năm</td><td>Răng</td><td>8.800.000</td><td>8%</td></tr>
<tr><td>Dán sứ Lisi</td><td>10 năm</td><td>Răng</td><td>12.800.000</td><td>8%</td></tr>
</tbody></table>
HTML;
    }
}
