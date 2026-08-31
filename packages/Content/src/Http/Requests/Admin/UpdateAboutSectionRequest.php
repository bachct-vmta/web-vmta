<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Mews\Purifier\Facades\Purifier;
use Packages\Content\Src\Enums\AboutSectionPosition;

class UpdateAboutSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('about.manage');
    }

    public function position(): AboutSectionPosition
    {
        return AboutSectionPosition::from((string) $this->route('position'));
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
            AboutSectionPosition::Hero        => $this->heroRules(),
            AboutSectionPosition::WhoAre      => $this->whoAreRules(),
            AboutSectionPosition::CoreValues  => $this->coreValuesRules(),
            AboutSectionPosition::HowWorks    => $this->howWorksRules(),
            AboutSectionPosition::Difference  => $this->differenceRules(),
            AboutSectionPosition::WhyChoose   => $this->whyChooseRules(),
            AboutSectionPosition::StartWithUs => $this->startWithUsRules(),
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
        return $this->localeFields([
            'title'     => ['required', 'string', 'max:200'],
            'body'      => ['nullable', 'string', 'max:500'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_link'  => ['nullable', 'string', 'max:500'],
            'subtitle'  => ['nullable', 'string', 'max:80'],
            'cta2_link' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function whoAreRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'body'  => ['nullable', 'string', 'max:50000'],
            // Mission cards are dynamic: keep at least 1, no upper bound.
            'items' => ['required', 'array', 'min:1'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.body"]  = ['required', 'string', 'max:400'];
        }
        // 2 illustration images (locale-agnostic, optional)
        $rules['image_1_media_id'] = ['nullable', 'integer', 'exists:media_files,id'];
        $rules['image_2_media_id'] = ['nullable', 'integer', 'exists:media_files,id'];
        return $rules;
    }

    private function coreValuesRules(): array
    {
        // Value cards are fully dynamic: admin may add rows or delete every row,
        // so items is optional (absent/empty = no value cards). Any submitted row
        // must still be fully filled.
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['nullable', 'array'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.body"]  = ['required', 'string', 'max:400'];
        }
        return $rules;
    }

    private function howWorksRules(): array
    {
        // Steps are dynamic: keep at least 1, no upper bound (admin can add/remove rows).
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'min:1'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.body"]  = ['required', 'string', 'max:400'];
        }
        return $rules;
    }

    private function differenceRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['required', 'array', 'min:1'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.text"] = ['required', 'string', 'max:300'];
        }
        return $rules;
    }

    private function whyChooseRules(): array
    {
        // Reason cards are fully dynamic: admin may add rows or delete every row,
        // so items is optional (absent/empty = no reason cards). Any submitted row
        // must still be fully filled.
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            'items' => ['nullable', 'array'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.title"] = ['required', 'string', 'max:120'];
            $rules["translations.{$locale}.items.*.body"]  = ['required', 'string', 'max:400'];
        }
        return $rules;
    }

    private function startWithUsRules(): array
    {
        return $this->localeFields([
            'title'     => ['required', 'string', 'max:160'],
            'body'      => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_link'  => ['nullable', 'string', 'max:500'],
            'subtitle'  => ['nullable', 'string', 'max:80'],
            'cta2_link' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
