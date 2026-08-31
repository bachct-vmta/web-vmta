<?php

namespace Packages\Dental\Src\Http\Requests\Admin;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

/**
 * Kiểm tra slug trùng theo từng locale, đọc locale từ ô cùng chỉ số trong mảng translations.
 */
class SlugUniquenessRule
{
    /**
     * @param  string  $table  Bảng dịch, ví dụ dental_facility_translations
     * @param  string  $parentColumn  Cột khoá ngoại trỏ về bản ghi cha
     * @param  int|null  $excludeParentId  Bản ghi cha cần bỏ qua khi cập nhật
     * @param  array<int, int>|null  $limitToParentIds  Giới hạn phạm vi kiểm tra, null là toàn bảng
     */
    public static function make(
        FormRequest $request,
        string $table,
        string $parentColumn,
        ?int $excludeParentId,
        ?array $limitToParentIds = null,
    ): Closure {
        return function (string $attribute, $value, Closure $fail) use ($request, $table, $parentColumn, $excludeParentId, $limitToParentIds) {
            $locale = (string) $request->input(str_replace('.slug', '.locale', $attribute));

            if ($locale === '') {
                return;
            }

            $query = DB::table($table)
                ->where('locale', $locale)
                ->where('slug', $value);

            if ($limitToParentIds !== null) {
                $query->whereIn($parentColumn, $limitToParentIds);
            }

            if ($excludeParentId !== null) {
                $query->where($parentColumn, '!=', $excludeParentId);
            }

            if ($query->exists()) {
                $fail(__('dental::dental.errors.slug_taken'));
            }
        };
    }

    /**
     * Slug dịch vụ chỉ cần duy nhất trong phạm vi một cơ sở.
     */
    public static function forService(FormRequest $request, ?int $facilityId, ?int $excludeServiceId): Closure
    {
        $siblingIds = $facilityId === null
            ? []
            : DB::table('dental_services')->where('dental_facility_id', $facilityId)->pluck('id')->all();

        return self::make($request, 'dental_service_translations', 'dental_service_id', $excludeServiceId, $siblingIds);
    }
}
