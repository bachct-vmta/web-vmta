<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Mews\Purifier\Facades\Purifier;
use Packages\Content\Src\Enums\AchievementSectionPosition;

class UpdateAchievementSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('achievement.manage');
    }

    public function position(): AchievementSectionPosition
    {
        return AchievementSectionPosition::from((string) $this->route('position'));
    }

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

    public function rules(): array
    {
        return match ($this->position()) {
            AchievementSectionPosition::Hero         => $this->heroRules(),
            AchievementSectionPosition::Capabilities => $this->capabilitiesRules(),
            AchievementSectionPosition::Assurance    => $this->assuranceRules(),
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
        ], $this->localeFields([
            'title'    => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body'     => ['nullable', 'string', 'max:5000'],
            'items'    => ['required', 'array', 'size:3'],
        ]));

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon_media_id"] = ['nullable', 'integer', 'exists:media_files,id'];
            $rules["translations.{$locale}.items.*.value"]         = ['required', 'string', 'max:50'];
            $rules["translations.{$locale}.items.*.label"]         = ['required', 'string', 'max:200'];
        }

        return $rules;
    }

    private function capabilitiesRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'size:4'],
        ]);

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon_media_id"] = ['nullable', 'integer', 'exists:media_files,id'];
            $rules["translations.{$locale}.items.*.title"]         = ['required', 'string', 'max:200'];
            $rules["translations.{$locale}.items.*.body"]          = ['required', 'string', 'max:500'];
        }

        return $rules;
    }

    private function assuranceRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'size:4'],
        ]);

        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.icon_media_id"] = ['nullable', 'integer', 'exists:media_files,id'];
            $rules["translations.{$locale}.items.*.body"]          = ['required', 'string', 'max:500'];
        }

        return $rules;
    }
}
