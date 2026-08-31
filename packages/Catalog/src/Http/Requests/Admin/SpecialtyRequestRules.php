<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation + payload normalisation for Specialty Store + Update requests.
 *
 * Validates the 22 landing-page fields per locale, the nested strengths_json /
 * hospitals_json repeater shapes, and the per-specialty fields (hero/intro media
 * + show_lead_form toggle). Normalises CTA links and prunes empty repeater rows
 * before validation so the form can submit half-empty rows without exploding.
 */
class SpecialtyRequestRules
{
    private const URL_RULE_PATTERN = '/^(https?:\/\/|\/[^\s]*|#[\w-]+)$/i';

    public static function rules(FormRequest $request, ?int $specialtyId): array
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));

        return [
            'icon' => ['nullable', 'string', 'max:100'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'hero_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'intro_image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'show_lead_form' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                SlugUniquenessRule::make($request, 'specialty_translations', 'specialty_id', $specialtyId),
            ],
            'translations.*.description' => ['nullable', 'string', 'max:1000'],
            'translations.*.hero_h1' => ['nullable', 'string', 'max:255'],
            'translations.*.breadcrumb_label' => ['nullable', 'string', 'max:120'],

            'translations.*.intro_h2' => ['nullable', 'string', 'max:500'],
            'translations.*.intro_lead' => ['nullable', 'string', 'max:500'],
            'translations.*.intro_body_html' => ['nullable', 'string', 'max:50000'],

            'translations.*.strengths_h2_line1' => ['nullable', 'string', 'max:255'],
            'translations.*.strengths_h2_line2' => ['nullable', 'string', 'max:255'],
            'translations.*.strengths_json' => ['nullable', 'array', 'max:24'],
            'translations.*.strengths_json.*.title' => ['nullable', 'string', 'max:255'],
            'translations.*.strengths_json.*.image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.strengths_json.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'translations.*.strengths_json.*.bullets' => ['nullable', 'array', 'max:8'],
            'translations.*.strengths_json.*.bullets.*' => ['nullable', 'string', 'max:255'],

            'translations.*.hospitals_h2_line1' => ['nullable', 'string', 'max:255'],
            'translations.*.hospitals_h2_line2' => ['nullable', 'string', 'max:255'],
            'translations.*.hospitals_subtitle' => ['nullable', 'string', 'max:500'],
            'translations.*.hospitals_json' => ['nullable', 'array', 'max:24'],
            'translations.*.hospitals_json.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.hospitals_json.*.partner_id' => ['nullable', 'integer', 'min:1'],
            'translations.*.hospitals_json.*.image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.hospitals_json.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'translations.*.hospitals_json.*.bullets' => ['nullable', 'array', 'max:8'],
            'translations.*.hospitals_json.*.bullets.*' => ['nullable', 'string', 'max:255'],
            'translations.*.hospitals_json.*.cta_primary.label' => ['nullable', 'string', 'max:120'],
            'translations.*.hospitals_json.*.cta_primary.url' => ['nullable', 'string', 'max:500', 'regex:'.self::URL_RULE_PATTERN],
            'translations.*.hospitals_json.*.cta_secondary.label' => ['nullable', 'string', 'max:120'],
            'translations.*.hospitals_json.*.cta_secondary.url' => ['nullable', 'string', 'max:500', 'regex:'.self::URL_RULE_PATTERN],

            'translations.*.lead_h2_line1' => ['nullable', 'string', 'max:255'],
            'translations.*.lead_h2_line2' => ['nullable', 'string', 'max:255'],
            'translations.*.lead_subtitle' => ['nullable', 'string', 'max:500'],
            'translations.*.lead_body_html' => ['nullable', 'string', 'max:10000'],
            'translations.*.lead_demand_placeholder' => ['nullable', 'string', 'max:255'],
            'translations.*.lead_submit_label' => ['nullable', 'string', 'max:120'],

            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_og_image' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Strip rows where every meaningful field is empty so partially-rendered
     * repeater frames don't trigger validation noise.
     */
    public static function normalisePayload(FormRequest $request): void
    {
        $translations = $request->input('translations', []);
        if (! is_array($translations)) {
            return;
        }

        foreach ($translations as $idx => $row) {
            if (isset($row['strengths_json']) && is_array($row['strengths_json'])) {
                $translations[$idx]['strengths_json'] = array_values(array_filter(
                    array_map(self::stripTransientKeys(...), $row['strengths_json']),
                    fn ($item) => self::strengthHasContent($item),
                ));
            }

            if (isset($row['hospitals_json']) && is_array($row['hospitals_json'])) {
                $translations[$idx]['hospitals_json'] = array_values(array_filter(
                    array_map(self::stripTransientKeys(...), $row['hospitals_json']),
                    fn ($item) => self::hospitalHasContent($item),
                ));
            }
        }

        $request->merge(['translations' => $translations]);
    }

    /** Strip client-only preview fields that don't belong in DB. */
    private static function stripTransientKeys(mixed $item): mixed
    {
        if (! is_array($item)) return $item;
        unset($item['image_url']);
        return $item;
    }

    private static function strengthHasContent(mixed $item): bool
    {
        if (! is_array($item)) return false;
        if (! empty($item['title'])) return true;
        if (! empty($item['image_media_id'])) return true;
        if (! empty($item['bullets']) && is_array($item['bullets'])) {
            foreach ($item['bullets'] as $b) {
                if (is_string($b) && trim($b) !== '') return true;
            }
        }
        return false;
    }

    private static function hospitalHasContent(mixed $item): bool
    {
        if (! is_array($item)) return false;
        foreach (['name', 'partner_id', 'image_media_id'] as $k) {
            if (! empty($item[$k])) return true;
        }
        foreach (['cta_primary', 'cta_secondary'] as $cta) {
            if (! empty($item[$cta]['label']) || ! empty($item[$cta]['url'])) return true;
        }
        if (! empty($item['bullets']) && is_array($item['bullets'])) {
            foreach ($item['bullets'] as $b) {
                if (is_string($b) && trim($b) !== '') return true;
            }
        }
        return false;
    }
}
