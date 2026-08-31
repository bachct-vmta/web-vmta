<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickAddMenuItemRequest extends FormRequest
{
    /**
     * Morph map values accepted by the quick-add panel.
     * Kept in sync with the picker tabs (page / post / category).
     */
    public const ALLOWED_TARGETS = ['page', 'post', 'category'];

    /** Same allowlist as UpdateMenuItemAjaxRequest::URL_SCHEME_PATTERN. */
    private const URL_SCHEME_PATTERN = '/^(https?:\/\/|mailto:|\/|#)/i';

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.create') ?? false;
    }

    public function rules(): array
    {
        $locales = $this->supportedLocales();

        return [
            'link_type' => ['required', Rule::in(['url', 'morph'])],
            'target_type' => ['nullable', 'string', Rule::in(self::ALLOWED_TARGETS), 'required_if:link_type,morph'],
            'target_id' => ['nullable', 'integer', 'min:1', 'required_if:link_type,morph'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.locale' => ['required', 'string', Rule::in($locales)],
            'translations.*.label' => ['required', 'string', 'max:255'],
            'translations.*.url' => ['nullable', 'string', 'max:500'],

            'icon' => ['nullable', 'string', 'max:60'],
            'open_new_tab' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $this->validated();

            if (($data['link_type'] ?? null) === 'url') {
                $hasAtLeastOneUrl = false;
                foreach ($data['translations'] ?? [] as $i => $row) {
                    $url = $row['url'] ?? null;
                    if ($url !== null && $url !== '') {
                        $hasAtLeastOneUrl = true;
                        if (! preg_match(self::URL_SCHEME_PATTERN, $url)) {
                            $v->errors()->add("translations.$i.url", __('content::content.menu_item.url_scheme_invalid'));
                        }
                    }
                }
                if (! $hasAtLeastOneUrl) {
                    $v->errors()->add('translations', __('content::content.menu_item.url_scheme_invalid'));
                }
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        return array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
    }
}
