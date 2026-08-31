<?php

namespace Packages\Dental\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Dental\Src\Enums\PublishStatus;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\Translations\DentalCategoryTranslation;

class DentalCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->rows() as $index => $row) {
            $existing = DentalCategoryTranslation::query()
                ->where('locale', 'vi')
                ->where('slug', $row['vi']['slug'])
                ->first();

            $category = $existing
                ? DentalCategory::find($existing->dental_category_id)
                : DentalCategory::create([
                    'status' => PublishStatus::Published->value,
                    'published_at' => now(),
                    'sort_order' => $index,
                ]);

            foreach (['vi', 'en'] as $locale) {
                $category->translateOrNew($locale)->fill($row[$locale]);
            }

            $category->save();
        }
    }

    /**
     * @return array<int, array{vi: array{name:string,slug:string}, en: array{name:string,slug:string}}>
     */
    protected function rows(): array
    {
        return [
            [
                'vi' => ['name' => 'Bệnh viện', 'slug' => 'benh-vien'],
                'en' => ['name' => 'Hospitals', 'slug' => 'hospitals'],
            ],
            [
                'vi' => ['name' => 'Phòng khám', 'slug' => 'phong-kham'],
                'en' => ['name' => 'Clinics', 'slug' => 'clinics'],
            ],
        ];
    }
}
