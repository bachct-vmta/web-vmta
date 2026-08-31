<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;
use Packages\Core\Src\Models\MediaFile;

/**
 * Seed 12 baseline specialties from the scraped JSON produced by
 * scripts/scrape-wp-specialty-assets.sh. Idempotent — replays overwrite the
 * same rows via updateOrCreate on (specialty_id, locale).
 *
 * The JSON ships as VI-only baseline. Re-translating to EN is left to admins
 * via the CMS form (Phase 4) — keeping the seeder thin avoids drift between
 * scraped Vietnamese copy and machine-translated English copy.
 */
class SpecialtyContentSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/specialty-content-seed.json');
        if (! File::exists($jsonPath)) {
            $this->command?->warn("Seed JSON not found at {$jsonPath}, skipping.");
            return;
        }

        $entries = json_decode(File::get($jsonPath), true);
        if (! is_array($entries)) {
            $this->command?->error('Invalid JSON in specialty-content-seed.json');
            return;
        }

        foreach ($entries as $idx => $entry) {
            DB::transaction(function () use ($entry, $idx) {
                $iconPath = $entry['icon_path'] ?? null;
                $heroMedia = $this->importMedia($entry['hero_image_path'] ?? null);
                $introMedia = $this->importMedia($entry['intro_image_path'] ?? null);

                $slug = $entry['slug'] ?? null;
                if (! $slug) {
                    return;
                }

                $existing = SpecialtyTranslation::where('locale', 'vi')->where('slug', $slug)->first();
                $specialtyId = $existing?->specialty_id;

                $specialty = $specialtyId
                    ? Specialty::find($specialtyId)
                    : null;

                $specialtyAttrs = [
                    'icon' => $iconPath,
                    'hero_media_id' => $heroMedia?->id,
                    'intro_image_media_id' => $introMedia?->id,
                    'sort_order' => $entry['sort_order'] ?? ($idx + 1),
                    'is_active' => true,
                    'show_lead_form' => $entry['show_lead_form'] ?? true,
                ];

                if ($specialty) {
                    $specialty->update($specialtyAttrs);
                } else {
                    $specialty = Specialty::create($specialtyAttrs);
                }

                $strengths = $this->mapRepeaterMedia($entry['strengths'] ?? []);
                $hospitals = $this->mapRepeaterMedia($entry['hospitals'] ?? []);

                SpecialtyTranslation::updateOrCreate(
                    ['specialty_id' => $specialty->id, 'locale' => 'vi'],
                    array_filter([
                        'name' => $entry['name'] ?? null,
                        'slug' => $slug,
                        'description' => $entry['intro_lead'] ?? null,
                        'hero_h1' => $entry['hero_h1'] ?? null,
                        'breadcrumb_label' => $entry['breadcrumb_label'] ?? null,
                        'intro_h2' => $entry['intro_h2'] ?? null,
                        'intro_lead' => $entry['intro_lead'] ?? null,
                        'intro_body_html' => $entry['intro_body_html'] ?? null,
                        'strengths_h2_line1' => $entry['strengths_h2_line1'] ?? null,
                        'strengths_h2_line2' => $entry['strengths_h2_line2'] ?? null,
                        'strengths_json' => $strengths ?: null,
                        'hospitals_h2_line1' => $entry['hospitals_h2_line1'] ?? null,
                        'hospitals_h2_line2' => $entry['hospitals_h2_line2'] ?? null,
                        'hospitals_subtitle' => $entry['hospitals_subtitle'] ?? null,
                        'hospitals_json' => $hospitals ?: null,
                        'lead_h2_line1' => $entry['lead_h2_line1'] ?? null,
                        'lead_h2_line2' => $entry['lead_h2_line2'] ?? null,
                        'lead_subtitle' => $entry['lead_subtitle'] ?? null,
                        'lead_body_html' => $entry['lead_body_html'] ?? null,
                        'lead_demand_placeholder' => $entry['lead_demand_placeholder'] ?? null,
                        'lead_submit_label' => $entry['lead_submit_label'] ?? null,
                        'seo_title' => $entry['seo_title'] ?? null,
                        'seo_description' => $entry['seo_description'] ?? null,
                        'seo_og_image' => $entry['seo_og_image'] ?? null,
                    ], fn ($v) => $v !== null && $v !== ''),
                );

                $this->command?->info("Seeded specialty: {$slug}");
            });
        }
    }

    private function importMedia(?string $path): ?MediaFile
    {
        if (! $path) {
            return null;
        }
        $abs = storage_path('app/public/'.ltrim($path, '/'));
        if (! File::exists($abs)) {
            return null;
        }

        $existing = MediaFile::where('permalink', $path)->first();
        if ($existing) {
            return $existing;
        }

        return MediaFile::create([
            'name' => basename($path),
            'permalink' => $path,
            'size' => File::size($abs),
            'mine_type' => File::mimeType($abs) ?: null,
            'storage_driver' => 'local',
        ]);
    }

    private function mapRepeaterMedia(array $items): array
    {
        return array_values(array_map(function ($item) {
            if (! is_array($item)) {
                return $item;
            }
            if (! empty($item['image_path']) && empty($item['image_media_id'])) {
                $media = $this->importMedia($item['image_path']);
                if ($media) {
                    $item['image_media_id'] = $media->id;
                }
            }
            unset($item['image_path']);
            return $item;
        }, $items));
    }
}
