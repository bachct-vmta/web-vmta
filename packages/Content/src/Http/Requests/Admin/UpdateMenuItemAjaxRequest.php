<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemAjaxRequest extends FormRequest
{
    /**
     * URL schemes / forms allowed for direct-URL menu items.
     * Permits: absolute http(s), mailto:, internal absolute paths (/...), and same-page anchors (#...).
     * `javascript:`, `tel:`, `data:` deliberately excluded per the locked product decision.
     */
    private const URL_SCHEME_PATTERN = '/^(https?:\/\/|mailto:|\/|#)/i';

    /**
     * Whitelisted morph aliases. Keeps `link_type=morph` from binding to
     * arbitrary classes (e.g. User) just because someone has content.edit.
     */
    private const ALLOWED_MORPH_TARGETS = ['page', 'post', 'category'];

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('content.edit') ?? false;
    }

    public function rules(): array
    {
        $locales = $this->supportedLocales();

        return [
            'link_type' => ['sometimes', Rule::in(['url', 'morph'])],
            'target_type' => ['sometimes', 'nullable', 'string', 'max:50', Rule::in(self::ALLOWED_MORPH_TARGETS)],
            'target_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:60'],
            'css_class' => ['sometimes', 'nullable', 'string', 'max:255'],
            'open_new_tab' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],

            'translations' => ['sometimes', 'array'],
            'translations.*.locale' => ['required_with:translations.*', 'string', Rule::in($locales)],
            'translations.*.label' => ['nullable', 'string', 'max:255'],
            'translations.*.url' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $this->validated();

            // link_type ↔ target_id consistency: when link_type=morph, target_id required.
            if (($data['link_type'] ?? null) === 'morph' && empty($data['target_id'])) {
                $v->errors()->add('target_id', __('content::content.menu_item.target_required'));
            }

            // URL scheme guard — applied to translation URLs when link_type is `url` (or omitted).
            $linkType = $data['link_type'] ?? null;
            if ($linkType === null || $linkType === 'url') {
                foreach ($data['translations'] ?? [] as $i => $row) {
                    $url = $row['url'] ?? null;
                    if ($url !== null && $url !== '' && ! preg_match(self::URL_SCHEME_PATTERN, $url)) {
                        $v->errors()->add("translations.$i.url", __('content::content.menu_item.url_scheme_invalid'));
                    }
                }
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function supportedLocales(): array
    {
        $configured = config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]);

        return array_keys($configured);
    }
}
