<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('achievement.manage');
    }

    public function rules(): array
    {
        return array_merge([
            'image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'reverse'        => ['nullable', 'boolean'],
            'sort_order'     => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active'      => ['nullable', 'boolean'],

            'translations'                  => ['required', 'array'],
            'translations.vi.slug'          => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('medical_case_translations', 'slug')->where('locale', 'vi')],
            'translations.en.slug'          => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('medical_case_translations', 'slug')->where('locale', 'en')],
            'translations.*.title'          => ['required', 'string', 'max:255'],
            'translations.*.subtitle'       => ['nullable', 'string', 'max:500'],
            'translations.*.intro'          => ['nullable', 'string', 'max:2000'],
            'translations.*.col1_items'     => ['nullable', 'array', 'max:10'],
            'translations.*.col1_items.*'   => ['string', 'max:255'],
            'translations.*.col2_items'     => ['nullable', 'array', 'max:10'],
            'translations.*.col2_items.*'   => ['string', 'max:255'],
            'translations.*.col3_body'      => ['nullable', 'string', 'max:2000'],
        ], $this->detailContentRules());
    }

    private function detailContentRules(): array
    {
        return [
            'translations.*.detail_content' => ['nullable', 'array'],
            'translations.*.detail_content.*' => ['nullable'],
            'translations.*.detail_content.reason_items' => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.reason_items.*.title' => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.reason_items.*.body' => ['nullable', 'string', 'max:500'],
            'translations.*.detail_content.reason_items.*.icon' => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.breakthrough_left_items' => ['nullable', 'array', 'max:8'],
            'translations.*.detail_content.breakthrough_left_items.*.title' => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.breakthrough_left_items.*.body' => ['nullable', 'string', 'max:700'],
            'translations.*.detail_content.breakthrough_left_items.*.icon' => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.breakthrough_right_items' => ['nullable', 'array', 'max:8'],
            'translations.*.detail_content.breakthrough_right_items.*.title' => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.breakthrough_right_items.*.body' => ['nullable', 'string', 'max:700'],
            'translations.*.detail_content.breakthrough_right_items.*.icon' => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.choice_items' => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.choice_items.*.title' => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.choice_items.*.body' => ['nullable', 'string', 'max:500'],
            'translations.*.detail_content.process_items' => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.process_items.*.body' => ['nullable', 'string', 'max:300'],
            'translations.*.detail_content.cta_points' => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.cta_points.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reverse'   => $this->boolean('reverse'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
        ]);
    }

    public function baseAttributes(): array
    {
        $v = $this->validated();
        // Parent slug derived from VI translation slug (kept for backward compat with hardcoded controller lookups).
        return [
            'slug'           => $v['translations']['vi']['slug'] ?? null,
            'image_media_id' => $v['image_media_id'] ?: null,
            'reverse'        => $v['reverse'] ?? false,
            'sort_order'     => $v['sort_order'] ?? 0,
            'is_active'      => $v['is_active'] ?? true,
        ];
    }

    public function translationsPayload(): array
    {
        return $this->validated()['translations'] ?? [];
    }
}
