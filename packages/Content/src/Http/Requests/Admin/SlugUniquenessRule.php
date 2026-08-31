<?php

namespace Packages\Content\Src\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

/**
 * Per-locale slug uniqueness validator for translation tables.
 *
 * Returns a Closure suitable for `'translations.*.slug' => [..., $rule]`. Reads the
 * sibling `locale` field from the request payload so it works on indexed translation
 * arrays (translations.0.slug pairs with translations.0.locale).
 *
 * Why a closure and not Rule::unique: the unique rule cannot dereference a sibling
 * array field for the WHERE clause without verbose call-time wiring; this keeps the
 * rule self-contained per request.
 */
class SlugUniquenessRule
{
    /**
     * @param  FormRequest  $request  The owning request (used to look up the row's locale).
     * @param  string  $table  Translation table name (e.g. page_translations).
     * @param  string  $parentColumn  FK column pointing at the parent (e.g. page_id).
     * @param  int|null  $excludeParentId  Parent ID to exclude (set on Update; null on Store).
     */
    public static function make(
        FormRequest $request,
        string $table,
        string $parentColumn,
        ?int $excludeParentId,
    ): Closure {
        return function (string $attribute, $value, Closure $fail) use ($request, $table, $parentColumn, $excludeParentId) {
            $localeKey = str_replace('.slug', '.locale', $attribute);
            $locale = (string) $request->input($localeKey);
            if ($locale === '') {
                return;
            }

            $query = DB::table($table)
                ->where('locale', $locale)
                ->where('slug', $value);

            if ($excludeParentId !== null) {
                $query->where($parentColumn, '!=', $excludeParentId);
            }

            if ($query->exists()) {
                $fail(__('content::content.errors.slug_taken'));
            }
        };
    }
}
