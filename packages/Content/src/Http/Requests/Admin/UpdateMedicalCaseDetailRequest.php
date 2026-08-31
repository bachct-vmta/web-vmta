<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalCaseDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth()->user()?->hasPermission('achievement.manage');
    }

    public function rules(): array
    {
        return [
            'translations'                                              => ['required', 'array'],
            'translations.*.detail_content'                             => ['nullable', 'array'],
            'translations.*.detail_content.*'                           => ['nullable'],
            'translations.*.detail_content.intro_media_id'              => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.hero_highlight_text'         => ['nullable', 'string', 'max:200'],
            'translations.*.detail_content.breakthrough_center_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.reason_items'                => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.reason_items.*.title'        => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.reason_items.*.body'         => ['nullable', 'string', 'max:500'],
            'translations.*.detail_content.reason_items.*.icon'         => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.reason_items.*.icon_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.breakthrough_left_items'              => ['nullable', 'array', 'max:8'],
            'translations.*.detail_content.breakthrough_left_items.*.title'      => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.breakthrough_left_items.*.body'       => ['nullable', 'string', 'max:700'],
            'translations.*.detail_content.breakthrough_left_items.*.icon'       => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.breakthrough_left_items.*.icon_media_id'  => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.breakthrough_right_items'             => ['nullable', 'array', 'max:8'],
            'translations.*.detail_content.breakthrough_right_items.*.title'     => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.breakthrough_right_items.*.body'      => ['nullable', 'string', 'max:700'],
            'translations.*.detail_content.breakthrough_right_items.*.icon'      => ['nullable', 'string', 'max:120'],
            'translations.*.detail_content.breakthrough_right_items.*.icon_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.choice_items'                => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.choice_items.*.title'        => ['nullable', 'string', 'max:160'],
            'translations.*.detail_content.choice_items.*.body'         => ['nullable', 'string', 'max:500'],
            'translations.*.detail_content.choice_items.*.image_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
            'translations.*.detail_content.process_items'               => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.process_items.*.body'        => ['nullable', 'string', 'max:300'],
            'translations.*.detail_content.cta_title'                   => ['nullable', 'string', 'max:500'],
            // cta_body stores CKEditor HTML (replaces legacy body + cta_points).
            'translations.*.detail_content.cta_body'                    => ['nullable', 'string', 'max:5000'],
            // Legacy cta_points kept for backward-compat — admin form no longer edits.
            'translations.*.detail_content.cta_points'                  => ['nullable', 'array', 'max:6'],
            'translations.*.detail_content.cta_points.*'                => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function translationsPayload(): array
    {
        return $this->validated()['translations'] ?? [];
    }
}
