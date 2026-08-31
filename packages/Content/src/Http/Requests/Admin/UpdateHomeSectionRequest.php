<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Mews\Purifier\Facades\Purifier;
use Packages\Content\Src\Enums\HomeSectionPosition;

class UpdateHomeSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('home.manage');
    }

    public function position(): HomeSectionPosition
    {
        return HomeSectionPosition::from((string) $this->route('position'));
    }

    /**
     * Sanitise rich-text + URL fields before validation so the persisted payload
     * cannot embed `<script>` (via body) or `javascript:` schemes (via cta_url).
     */
    protected function prepareForValidation(): void
    {
        $translations = (array) $this->input('translations', []);

        foreach ($translations as $locale => $payload) {
            if (! is_array($payload)) {
                continue;
            }

            if (isset($payload['body']) && is_string($payload['body'])) {
                $payload['body'] = Purifier::clean($payload['body']);
            }

            $translations[$locale] = $payload;
        }

        $this->merge(['translations' => $translations]);
    }

    /**
     * @return array<int, string>
     */
    private function safeUrlRule(): array
    {
        // Reject `javascript:` / `data:` schemes — allow http(s) absolute URLs and
        // root-relative paths (used by site internal links like `/vi/contact`).
        return ['nullable', 'string', 'max:500', 'regex:#^(https?://|/)#i'];
    }

    /**
     * @return array<int, string>
     */
    private function safeHlsUrlRule(): array
    {
        // Kept for backward compat — delegates to safeIframeUrlRule.
        return $this->safeIframeUrlRule();
    }

    /**
     * Allow HLS .m3u8 playlists, YouTube /embed/ URLs, and Vimeo player URLs.
     * Rejects arbitrary domains (prevents clickjacking / XSS via iframe src injection).
     *
     * @return array<int, string>
     */
    private function safeIframeUrlRule(): array
    {
        return [
            'nullable',
            'string',
            'max:500',
            'regex:#^https://(www\.)?(youtube\.com/embed|player\.vimeo\.com)/|^https?://.*\.m3u8(\?|$)#i',
        ];
    }

    public function rules(): array
    {
        return match ($this->position()) {
            HomeSectionPosition::Hero => $this->heroRules(),
            HomeSectionPosition::Values => $this->valuesRules(),
            HomeSectionPosition::About => $this->aboutRules(),
            HomeSectionPosition::Solutions => $this->solutionsRules(),
            HomeSectionPosition::VisionMission => $this->visionMissionRules(),
            HomeSectionPosition::Benefits => $this->benefitsRules(),
            HomeSectionPosition::Technology => $this->technologyRules(),
            HomeSectionPosition::WhyVN => $this->whyVNRules(),
        };
    }

    private function localeFields(array $extra): array
    {
        $rules = [];
        foreach (['vi', 'en'] as $locale) {
            foreach ($extra as $field => $fieldRules) {
                $rules["translations.{$locale}.{$field}"] = $fieldRules;
            }
        }

        return $rules;
    }

    private function heroRules(): array
    {
        $rules = array_merge([
            'image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            // Toggle for the marquee strip; absent when checkbox unchecked is handled
            // by the hidden companion input in the admin form (always sends 0/1).
            'marquee_active' => ['nullable', 'boolean'],
        ], $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:5000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => $this->safeUrlRule(),
            'items' => ['required', 'array', 'min:4', 'max:10'],
        ]));

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.label"] = ['required', 'string', 'max:80'];
            $rules["translations.{$locale}.items.*.value"] = ['required', 'string', 'max:80'];
        }

        return $rules;
    }

    private function valuesRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'size:3'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon"] = ['nullable', 'string', 'max:60'];
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:80'];
            $rules["translations.{$locale}.items.*.body"] = ['required', 'string', 'max:300'];
        }

        return $rules;
    }

    private function aboutRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:5000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => $this->safeUrlRule(),
            'items' => ['required', 'array', 'size:3'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.bullet"] = ['required', 'string', 'max:200'];
        }

        return $rules;
    }

    private function solutionsRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'size:3'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon"] = ['nullable', 'string', 'max:60'];
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:80'];
            $rules["translations.{$locale}.items.*.body"] = ['required', 'string', 'max:300'];
        }

        return $rules;
    }

    private function visionMissionRules(): array
    {
        $rules = array_merge([
            'video_url' => $this->safeIframeUrlRule(),
        ], $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'size:3'],
        ]));

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.audience"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.body"] = ['required', 'string', 'max:300'];
        }

        return $rules;
    }

    private function benefitsRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'size:2'],
        ]);

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.audience"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.subtitle"] = ['required', 'string', 'max:300'];
            $rules["translations.{$locale}.items.*.bullets"] = ['required', 'array', 'min:1', 'max:8'];
            $rules["translations.{$locale}.items.*.bullets.*"] = ['required', 'string', 'max:300'];
            $rules["translations.{$locale}.items.*.image_url"] = $this->safeUrlRule();
        }

        return $rules;
    }

    private function technologyRules(): array
    {
        $rules = array_merge([
            'image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
        ], $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'size:2'],
        ]));

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:160'];
            $rules["translations.{$locale}.items.*.bullets"] = ['required', 'array', 'min:1', 'max:8'];
            $rules["translations.{$locale}.items.*.bullets.*"] = ['required', 'string', 'max:300'];
        }

        return $rules;
    }

    private function whyVNRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'size:3'],
        ]);

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon"] = ['nullable', 'string', 'max:60'];
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:80'];
            $rules["translations.{$locale}.items.*.body"] = ['required', 'string', 'max:300'];
        }

        return $rules;
    }
}
