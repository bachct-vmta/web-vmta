<?php

namespace Packages\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Catalog\Src\Models\Specialty;

/**
 * Seeds the baseline medical specialties referenced by vmta.vn (medical tourism focus).
 * Idempotent: upserts by VI slug — re-running won't duplicate.
 */
class SpecialtySeeder extends Seeder
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
        $existing = Specialty::whereHas('translations', fn ($q) => $q->where('locale', 'vi')->where('slug', $row['vi']['slug']))->first();
        $specialty = $existing ?? new Specialty;
        $specialty->icon = $row['icon'] ?? null;
        $specialty->sort_order = $sortOrder;
        $specialty->is_active = true;
        $specialty->save();

        foreach (['vi', 'en'] as $locale) {
            $specialty->translateOrNew($locale)->fill($row[$locale])->save();
        }
    }

    /**
     * @return array<int, array{vi: array{name: string, slug: string, description: string}, en: array{name: string, slug: string, description: string}, icon?: string}>
     */
    private function items(): array
    {
        return [
            [
                'icon' => 'cardiology',
                'vi' => ['name' => 'Tim mạch', 'slug' => 'tim-mach', 'description' => 'Chẩn đoán và điều trị bệnh tim mạch.'],
                'en' => ['name' => 'Cardiology', 'slug' => 'cardiology', 'description' => 'Heart and vascular care.'],
            ],
            [
                'icon' => 'dentistry',
                'vi' => ['name' => 'Nha khoa', 'slug' => 'nha-khoa', 'description' => 'Răng — hàm — mặt, implant, niềng răng.'],
                'en' => ['name' => 'Dentistry', 'slug' => 'dentistry', 'description' => 'Dental, oral surgery, implants, orthodontics.'],
            ],
            [
                'icon' => 'orthopedics',
                'vi' => ['name' => 'Cơ xương khớp', 'slug' => 'co-xuong-khop', 'description' => 'Phẫu thuật và phục hồi chức năng.'],
                'en' => ['name' => 'Orthopedics', 'slug' => 'orthopedics', 'description' => 'Bones, joints, sports medicine.'],
            ],
            [
                'icon' => 'oncology',
                'vi' => ['name' => 'Ung bướu', 'slug' => 'ung-buou', 'description' => 'Tầm soát và điều trị ung thư.'],
                'en' => ['name' => 'Oncology', 'slug' => 'oncology', 'description' => 'Cancer screening and treatment.'],
            ],
            [
                'icon' => 'dermatology',
                'vi' => ['name' => 'Da liễu — Thẩm mỹ', 'slug' => 'da-lieu', 'description' => 'Da liễu và thẩm mỹ không phẫu thuật.'],
                'en' => ['name' => 'Dermatology', 'slug' => 'dermatology', 'description' => 'Dermatology and aesthetic medicine.'],
            ],
            [
                'icon' => 'fertility',
                'vi' => ['name' => 'Sản phụ khoa & Hỗ trợ sinh sản', 'slug' => 'san-phu-khoa', 'description' => 'IVF, thụ tinh ống nghiệm.'],
                'en' => ['name' => 'OB-GYN & Fertility', 'slug' => 'ob-gyn-fertility', 'description' => 'IVF, IUI, women\'s health.'],
            ],
            [
                'icon' => 'rehab',
                'vi' => ['name' => 'Phục hồi chức năng', 'slug' => 'phuc-hoi-chuc-nang', 'description' => 'Vật lý trị liệu sau điều trị.'],
                'en' => ['name' => 'Rehabilitation', 'slug' => 'rehabilitation', 'description' => 'Physical therapy & recovery.'],
            ],
            [
                'icon' => 'check-up',
                'vi' => ['name' => 'Khám tổng quát', 'slug' => 'kham-tong-quat', 'description' => 'Tầm soát sức khoẻ định kỳ.'],
                'en' => ['name' => 'Health Check-up', 'slug' => 'health-check-up', 'description' => 'Periodic health screening.'],
            ],
        ];
    }
}
