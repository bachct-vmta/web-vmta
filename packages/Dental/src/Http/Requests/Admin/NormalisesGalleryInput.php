<?php

namespace Packages\Dental\Src\Http\Requests\Admin;

/**
 * Chuyển chuỗi id ngăn cách dấu phẩy từ media picker thành mảng int trước khi validate.
 */
trait NormalisesGalleryInput
{
    protected function prepareForValidation(): void
    {
        $raw = $this->input('certificates_media_ids');

        if (is_string($raw) && $raw !== '') {
            $ids = array_values(array_filter(array_map('intval', explode(',', $raw))));
            $this->merge(['certificates_media_ids' => $ids]);
        } elseif ($raw === '' || $raw === null) {
            $this->merge(['certificates_media_ids' => null]);
        }
    }
}
