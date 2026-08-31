<?php

namespace Packages\Catalog\Src\Services\WpSpecialtyScraper;

use Illuminate\Support\Facades\DB;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Models\Translations\SpecialtyTranslation;

/**
 * Persist crawled + extracted specialty content into Specialty + 2 SpecialtyTranslation rows.
 *
 * Resolves the existing Specialty by VI slug (matches the "primary" locale by convention) or
 * creates a new one. Both VI and EN translations are upserted by (specialty_id, locale).
 * Caller is responsible for swapping image_url → image_media_id inside strengths/hospitals
 * arrays BEFORE invoking — this class is storage-only.
 */
class SpecialtyImporter
{
    private const ALLOWED_HTML_TAGS = '<p><br><strong><em><ul><ol><li><h3><h4><a><blockquote>';

    /**
     * @param  array{icon_permalink?: ?string, hero_media_id?: ?int, intro_media_id?: ?int}  $sharedMedia
     */
    public function import(string $viSlug, string $enSlug, array $viData, array $enData, array $sharedMedia): Specialty
    {
        return DB::transaction(function () use ($viSlug, $enSlug, $viData, $enData, $sharedMedia) {
            $existing = SpecialtyTranslation::where('locale', 'vi')->where('slug', $viSlug)->first();

            $specialty = $existing
                ? Specialty::find($existing->specialty_id)
                : Specialty::create(['is_active' => true, 'show_lead_form' => true, 'sort_order' => 0]);

            $specialty->update(array_filter([
                'icon' => $sharedMedia['icon_permalink'] ?? null,
                'hero_media_id' => $sharedMedia['hero_media_id'] ?? null,
                'intro_image_media_id' => $sharedMedia['intro_media_id'] ?? null,
            ], fn ($v) => $v !== null));

            $this->upsertTranslation($specialty->id, 'vi', $viSlug, $viData);
            $this->upsertTranslation($specialty->id, 'en', $enSlug, $enData);

            return $specialty;
        });
    }

    private function upsertTranslation(int $specialtyId, string $locale, string $slug, array $data): void
    {
        SpecialtyTranslation::updateOrCreate(
            ['specialty_id' => $specialtyId, 'locale' => $locale],
            [
                'slug' => $slug,
                'name' => $data['hero_h1'] ?? ucfirst($slug),
                'hero_h1' => $data['hero_h1'] ?? null,
                'breadcrumb_label' => $data['breadcrumb_label'] ?? null,
                'intro_h2' => $data['intro_h2'] ?? null,
                'intro_lead' => $data['intro_lead'] ?? null,
                'intro_body_html' => $this->sanitizeHtml($data['intro_body_html'] ?? null),
                'strengths_h2_line1' => $data['strengths_h2_line1'] ?? null,
                'strengths_h2_line2' => $data['strengths_h2_line2'] ?? null,
                'strengths_json' => array_values($data['strengths'] ?? []),
                'hospitals_h2_line1' => $data['hospitals_h2_line1'] ?? null,
                'hospitals_h2_line2' => $data['hospitals_h2_line2'] ?? null,
                'hospitals_subtitle' => $data['hospitals_subtitle'] ?? null,
                'hospitals_json' => array_values($data['hospitals'] ?? []),
                'lead_h2_line1' => $data['lead_h2_line1'] ?? null,
                'lead_h2_line2' => $data['lead_h2_line2'] ?? null,
                'lead_subtitle' => $data['lead_subtitle'] ?? null,
                'lead_body_html' => $this->sanitizeHtml($data['lead_body_html'] ?? null),
                'lead_demand_placeholder' => $data['lead_demand_placeholder'] ?? null,
                'lead_submit_label' => $data['lead_submit_label'] ?? null,
            ],
        );
    }

    private function sanitizeHtml(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return null;
        }

        return trim(strip_tags($html, self::ALLOWED_HTML_TAGS)) ?: null;
    }
}
