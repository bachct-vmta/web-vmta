<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Packages\Content\Database\Seeders\AboutSectionSeeder;
use Packages\Content\Database\Seeders\AchievementSectionSeeder;
use Packages\Content\Database\Seeders\AllianceSectionSeeder;
use Packages\Content\Database\Seeders\HomeSectionSeeder;
use Packages\Content\Database\Seeders\MedicalCaseSeeder;
use Packages\Dental\Database\Seeders\DentalCategorySeeder;
use Packages\Inquiry\Database\Seeders\ContactSectionSeeder;
use Packages\Core\Database\Seeders\AdminSeeder;
use Packages\Core\Src\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        $this->call([
            HomeSectionSeeder::class,
            AboutSectionSeeder::class,
            AllianceSectionSeeder::class,
            AchievementSectionSeeder::class,
            MedicalCaseSeeder::class,
            ContactSectionSeeder::class,
            DentalCategorySeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
