<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Packages\Catalog\Src\Models\Partner;

/**
 * Lightweight JSON endpoint to auto-fill hospital repeater rows when an admin
 * selects an existing Partner inside the Specialty form's Hospitals tab.
 *
 * Response shape is intentionally tiny — only the fields the repeater consumes.
 */
class PartnerLookupController extends Controller
{
    public function show(int $partner): JsonResponse
    {
        $locale = app()->getLocale();

        $model = Partner::with('translations')->findOrFail($partner);
        $translation = $model->translate($locale) ?? $model->translations->first();

        return response()->json([
            'id' => $model->id,
            'name' => $translation?->name,
            'image_media_id' => $model->logo_media_id ?? $model->cover_media_id ?? null,
        ]);
    }
}
