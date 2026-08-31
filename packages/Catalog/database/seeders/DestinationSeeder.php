<?php

namespace Packages\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Catalog\Src\Models\Destination;

/**
 * Seeds the baseline Vietnam medical-tourism destinations from vmta.vn coverage.
 * Idempotent: upserts by VI slug.
 */
class DestinationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->items() as $idx => $row) {
                $this->upsert($idx + 1, $row);
            }
        });
    }

    private function upsert(int $sortOrder, array $row): void
    {
        $existing = Destination::whereHas('translations', fn ($q) => $q->where('locale', 'vi')->where('slug', $row['vi']['slug']))->first();
        $destination = $existing ?? new Destination;
        $destination->country_code = $row['country_code'] ?? 'VN';
        $destination->region = $row['region'] ?? null;
        $destination->latitude = $row['lat'] ?? null;
        $destination->longitude = $row['lng'] ?? null;
        $destination->sort_order = $sortOrder;
        $destination->is_active = true;
        $destination->save();

        foreach (['vi', 'en'] as $locale) {
            $destination->translateOrNew($locale)->fill($row[$locale])->save();
        }
    }

    /**
     * @return array<int, array{country_code: string, region: string, lat: float, lng: float, vi: array{name: string, slug: string, description: string}, en: array{name: string, slug: string, description: string}}>
     */
    private function items(): array
    {
        return [
            [
                'country_code' => 'VN', 'region' => 'North', 'lat' => 21.0285, 'lng' => 105.8542,
                'vi' => ['name' => 'Hà Nội', 'slug' => 'ha-noi', 'description' => 'Thủ đô — trung tâm y tế lớn nhất miền Bắc.'],
                'en' => ['name' => 'Hanoi', 'slug' => 'hanoi', 'description' => 'Capital — largest medical hub in the North.'],
            ],
            [
                'country_code' => 'VN', 'region' => 'Central', 'lat' => 16.0544, 'lng' => 108.2022,
                'vi' => ['name' => 'Đà Nẵng', 'slug' => 'da-nang', 'description' => 'Thành phố biển trung tâm — du lịch y tế kết hợp nghỉ dưỡng.'],
                'en' => ['name' => 'Da Nang', 'slug' => 'da-nang', 'description' => 'Coastal city — medical tourism combined with leisure.'],
            ],
            [
                'country_code' => 'VN', 'region' => 'South', 'lat' => 10.7626, 'lng' => 106.6602,
                'vi' => ['name' => 'TP. Hồ Chí Minh', 'slug' => 'ho-chi-minh', 'description' => 'Trung tâm y tế lớn nhất phía Nam.'],
                'en' => ['name' => 'Ho Chi Minh City', 'slug' => 'ho-chi-minh', 'description' => 'Largest medical hub in the South.'],
            ],
            [
                'country_code' => 'VN', 'region' => 'Central', 'lat' => 12.2388, 'lng' => 109.1967,
                'vi' => ['name' => 'Nha Trang', 'slug' => 'nha-trang', 'description' => 'Khu nghỉ dưỡng kết hợp wellness.'],
                'en' => ['name' => 'Nha Trang', 'slug' => 'nha-trang', 'description' => 'Beach resort + wellness destination.'],
            ],
            [
                'country_code' => 'VN', 'region' => 'Central Highlands', 'lat' => 11.9404, 'lng' => 108.4583,
                'vi' => ['name' => 'Đà Lạt', 'slug' => 'da-lat', 'description' => 'Khí hậu ôn hoà — phục hồi sức khoẻ.'],
                'en' => ['name' => 'Da Lat', 'slug' => 'da-lat', 'description' => 'Cool climate — health recovery retreats.'],
            ],
            [
                'country_code' => 'VN', 'region' => 'Mekong Delta', 'lat' => 10.0452, 'lng' => 105.7469,
                'vi' => ['name' => 'Cần Thơ', 'slug' => 'can-tho', 'description' => 'Trung tâm y tế đồng bằng sông Cửu Long.'],
                'en' => ['name' => 'Can Tho', 'slug' => 'can-tho', 'description' => 'Mekong Delta medical center.'],
            ],
        ];
    }
}
