<?php

namespace Packages\Catalog\Src\Http\Requests\Admin;

/**
 * Converts a comma-separated `gallery_media_ids` string into an integer array before
 * validation runs. Lets us declare `gallery_media_ids.*` element rules (e.g.
 * `exists:media_files,id`) that fire uniformly whether the admin form submits
 * `gallery_media_ids=11,22,33` (current Blade) or `gallery_media_ids[]=…` (future
 * multi-select picker — Slice C).
 */
trait NormalisesGalleryInput
{
    protected function prepareForValidation(): void
    {
        $raw = $this->input('gallery_media_ids');

        if (is_string($raw) && $raw !== '') {
            $ids = array_values(array_filter(array_map('intval', explode(',', $raw))));
            $this->merge(['gallery_media_ids' => $ids]);
        } elseif ($raw === '' || $raw === null) {
            $this->merge(['gallery_media_ids' => null]);
        }
    }
}
