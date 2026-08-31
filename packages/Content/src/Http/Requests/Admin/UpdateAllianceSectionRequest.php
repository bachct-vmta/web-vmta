<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Mews\Purifier\Facades\Purifier;
use Packages\Content\Src\Enums\AllianceSectionPosition;

class UpdateAllianceSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('alliance.manage');
    }

    public function position(): AllianceSectionPosition
    {
        $position = AllianceSectionPosition::tryFrom((string) $this->route('position'));
        abort_if($position === null, 404);
        return $position;
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
            AllianceSectionPosition::Hero      => $this->heroRules(),
            AllianceSectionPosition::Overview  => $this->overviewRules(),
            AllianceSectionPosition::Standards => $this->standardsRules(),
            AllianceSectionPosition::Map       => $this->mapRules(),
            AllianceSectionPosition::JoinForm  => $this->joinFormRules(),
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
            'title'    => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'body'     => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function overviewRules(): array
    {
        return $this->localeFields([
            'title'    => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'body'     => ['nullable', 'string', 'max:50000'],
        ]);
    }

    private function standardsRules(): array
    {
        return $this->localeFields([
            'title' => ['required', 'string', 'max:200'],
            'body'  => ['nullable', 'string', 'max:50000'], // CKEditor HTML — sanitized via Purifier in prepareForValidation
        ]);
    }

    private function mapRules(): array
    {
        return $this->localeFields([
            'title'    => ['required', 'string', 'max:200'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'body'     => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function joinFormRules(): array
    {
        return $this->localeFields([
            'title'     => ['required', 'string', 'max:200'],
            'body'      => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
        ]);
    }
}
