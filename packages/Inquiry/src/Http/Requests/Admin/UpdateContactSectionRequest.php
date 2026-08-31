<?php

namespace Packages\Inquiry\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;

class UpdateContactSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('contact.manage');
    }

    public function position(): ContactSectionPosition
    {
        return ContactSectionPosition::from((string) $this->route('position'));
    }

    public function rules(): array
    {
        return match ($this->position()) {
            ContactSectionPosition::Hero    => $this->heroRules(),
            ContactSectionPosition::Offices => $this->officesRules(),
        };
    }

    /** Build the same rule for both locales. */
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
            'title'    => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'body'     => ['nullable', 'string', 'max:500'],
            'extra'    => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function officesRules(): array
    {
        $rules = $this->localeFields([
            'title' => ['required', 'string', 'max:160'],
            // Office cards are dynamic: keep at least 1, no upper bound.
            'items' => ['required', 'array', 'min:1'],
        ]);
        foreach (['vi', 'en'] as $locale) {
            $rules["translations.{$locale}.items.*.name"]    = ['required', 'string', 'max:160'];
            $rules["translations.{$locale}.items.*.address"] = ['nullable', 'string', 'max:300'];
            $rules["translations.{$locale}.items.*.email"]   = ['nullable', 'string', 'max:160'];
            $rules["translations.{$locale}.items.*.phone"]   = ['nullable', 'string', 'max:60'];
            $rules["translations.{$locale}.items.*.note"]    = ['nullable', 'string', 'max:200'];
        }
        // Section illustration + map embed are locale-agnostic (single value).
        $rules['image_1_media_id'] = ['nullable', 'integer', 'exists:media_files,id'];
        $rules['map_embed']        = ['nullable', 'string', 'max:5000'];

        return $rules;
    }

}
