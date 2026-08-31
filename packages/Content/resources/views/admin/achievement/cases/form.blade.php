@extends('core::layouts.admin')

@php
    $isEdit = $isEdit ?? false;
    $action = $isEdit
        ? route(admin_route_name('achievement.cases.update'), $case)
        : route(admin_route_name('achievement.cases.store'));
    $title = $isEdit
        ? __('content::achievement.cases.form.edit_heading')
        : __('content::achievement.cases.form.create_heading');
    $locales = ['vi', 'en'];

    $existing = [];
    foreach ($locales as $loc) {
        $tr = $case->translations?->firstWhere('locale', $loc);
        $existing[$loc] = [
            'slug'       => old("translations.{$loc}.slug", $tr?->slug ?? ($loc === 'vi' ? $case->slug : null)),
            'title'      => old("translations.{$loc}.title", $tr?->title),
            'subtitle'   => old("translations.{$loc}.subtitle", $tr?->subtitle),
            'intro'      => old("translations.{$loc}.intro", $tr?->intro),
            'col1_items' => old("translations.{$loc}.col1_items", $tr?->col1_items ?? []),
            'col2_items' => old("translations.{$loc}.col2_items", $tr?->col2_items ?? []),
            'col3_body'  => old("translations.{$loc}.col3_body", $tr?->col3_body),
        ];
    }
@endphp

@section('title', $title)

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ $title }}</h1>
        <a href="{{ route(admin_route_name('achievement.index')) }}"
           class="text-sm text-slate-600 hover:underline">← {{ __('content::achievement.cases.form.cancel') }}</a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6 bg-white border border-slate-200 rounded p-5">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Base fields (slug per-locale moved into translation tabs below) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-core::media-picker
                    name="image_media_id"
                    :value="$case->image_media_id"
                    :preview-url="$case->image?->permalink"
                    :label="__('content::achievement.cases.form.image')"
                    store="id"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ __('content::achievement.cases.form.sort_order') }}
                </label>
                <input type="number" min="0" max="65535" name="sort_order"
                       value="{{ old('sort_order', $case->sort_order ?? 0) }}"
                       class="w-full md:w-1/3 rounded border-slate-300 text-sm">
            </div>
            <div class="flex flex-col gap-2 pt-6">
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="reverse" value="0">
                    <input type="checkbox" name="reverse" value="1"
                           {{ old('reverse', $case->reverse) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    <span class="text-sm">{{ __('content::achievement.cases.form.reverse') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $isEdit ? $case->is_active : true) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    <span class="text-sm">{{ __('content::achievement.cases.form.is_active') }}</span>
                </label>
            </div>
        </div>

        {{-- Per-locale translations --}}
        <div x-data="achievementCaseForm()" x-init="initSlugs()">
            <nav class="flex gap-2 border-b border-slate-200 mb-4">
                @foreach($locales as $loc)
                    <button type="button"
                            @click="tab = '{{ $loc }}'"
                            :class="tab === '{{ $loc }}' ? 'border-blue-600 text-blue-700 font-semibold' : 'border-transparent text-slate-600'"
                            class="px-3 py-2 text-sm border-b-2 hover:text-slate-900">
                        {{ strtoupper($loc) }}
                    </button>
                @endforeach
            </nav>

            @foreach($locales as $loc)
                <div x-show="tab === '{{ $loc }}'" x-cloak class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('content::achievement.cases.form.title') }}
                        </label>
                        <input type="text" name="translations[{{ $loc }}][title]"
                               value="{{ $existing[$loc]['title'] }}"
                               @input="onTitleInput($event, '{{ $loc }}')"
                               required class="w-full rounded border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('content::achievement.cases.form.slug') }} ({{ strtoupper($loc) }})
                        </label>
                        <input type="text" name="translations[{{ $loc }}][slug]"
                               value="{{ $existing[$loc]['slug'] }}"
                               required pattern="[A-Za-z0-9_\-]+"
                               x-ref="slug_{{ $loc }}"
                               @input="onSlugManualEdit('{{ $loc }}', $event)"
                               class="w-full rounded border-slate-300 text-sm font-mono">
                        <p class="text-xs text-slate-500 mt-1">
                            URL: /{{ $loc }}/<span class="font-mono">{{ $existing[$loc]['slug'] ?: 'slug-here' }}</span>
                            <span class="text-slate-400">— tự sinh từ Tiêu đề, xoá để khôi phục auto-gen</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('content::achievement.cases.form.subtitle') }}
                        </label>
                        <textarea name="translations[{{ $loc }}][subtitle]" rows="2"
                                  class="w-full rounded border-slate-300 text-sm">{{ $existing[$loc]['subtitle'] }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('content::achievement.cases.form.intro') }}
                        </label>
                        <textarea name="translations[{{ $loc }}][intro]" rows="3"
                                  class="w-full rounded border-slate-300 text-sm">{{ $existing[$loc]['intro'] }}</textarea>
                    </div>

                    {{-- col1_items dynamic list --}}
                    @php
                        $col1 = is_array($existing[$loc]['col1_items']) ? $existing[$loc]['col1_items'] : [];
                        $col2 = is_array($existing[$loc]['col2_items']) ? $existing[$loc]['col2_items'] : [];
                    @endphp
                    <div x-data="{ items: @js(array_values($col1) ?: ['']) }">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('content::achievement.cases.form.col1_items') }}
                        </label>
                        <div class="space-y-2">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div class="flex gap-2">
                                    <input type="text"
                                           :name="`translations[{{ $loc }}][col1_items][${idx}]`"
                                           :value="item"
                                           @input="items[idx] = $event.target.value"
                                           class="flex-1 rounded border-slate-300 text-sm">
                                    <button type="button" @click="items.splice(idx, 1)"
                                            class="px-2 rounded bg-red-100 text-red-700 hover:bg-red-200">×</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="items.push('')"
                                class="mt-2 rounded bg-teal-600 px-3 py-1 text-white text-xs hover:bg-teal-700">
                            {{ __('content::achievement.cases.form.add_item') }}
                        </button>
                    </div>

                    {{-- col2_items dynamic list --}}
                    <div x-data="{ items: @js(array_values($col2) ?: ['']) }">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            {{ __('content::achievement.cases.form.col2_items') }}
                        </label>
                        <div class="space-y-2">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div class="flex gap-2">
                                    <input type="text"
                                           :name="`translations[{{ $loc }}][col2_items][${idx}]`"
                                           :value="item"
                                           @input="items[idx] = $event.target.value"
                                           class="flex-1 rounded border-slate-300 text-sm">
                                    <button type="button" @click="items.splice(idx, 1)"
                                            class="px-2 rounded bg-red-100 text-red-700 hover:bg-red-200">×</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="items.push('')"
                                class="mt-2 rounded bg-teal-600 px-3 py-1 text-white text-xs hover:bg-teal-700">
                            {{ __('content::achievement.cases.form.add_item') }}
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ __('content::achievement.cases.form.col3_body') }}
                        </label>
                        <textarea name="translations[{{ $loc }}][col3_body]" rows="3"
                                  class="w-full rounded border-slate-300 text-sm">{{ $existing[$loc]['col3_body'] }}</textarea>
                    </div>

                </div>
            @endforeach
        </div>

        @if($isEdit)
            <div class="rounded-md bg-teal-50 border border-teal-200 px-4 py-3 flex items-center justify-between">
                <span class="text-sm text-teal-800">{{ __('content::achievement.cases.form.detail_page_title') }}</span>
                <a href="{{ route(admin_route_name('achievement.cases.detail.edit'), $case) }}"
                   class="text-sm font-medium text-teal-700 hover:text-teal-900 hover:underline">
                    {{ __('content::achievement.cases.form.edit_detail_link') }}
                </a>
            </div>
        @endif

        <div class="flex justify-end pt-2 border-t border-slate-100">
            <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
                {{ __('content::achievement.cases.form.save') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
window.achievementCaseForm = function () {
    return {
        tab: 'vi',
        slugManuallyEdited: { vi: false, en: false },

        initSlugs() {
            ['vi', 'en'].forEach(loc => {
                const ref = this.$refs['slug_' + loc];
                if (ref && ref.value.trim() !== '') {
                    this.slugManuallyEdited[loc] = true;
                }
            });
        },

        slugify(input) {
            if (! input) return '';
            const vietMap = {
                'à':'a','á':'a','ạ':'a','ả':'a','ã':'a','â':'a','ầ':'a','ấ':'a','ậ':'a','ẩ':'a','ẫ':'a','ă':'a','ằ':'a','ắ':'a','ặ':'a','ẳ':'a','ẵ':'a',
                'è':'e','é':'e','ẹ':'e','ẻ':'e','ẽ':'e','ê':'e','ề':'e','ế':'e','ệ':'e','ể':'e','ễ':'e',
                'ì':'i','í':'i','ị':'i','ỉ':'i','ĩ':'i',
                'ò':'o','ó':'o','ọ':'o','ỏ':'o','õ':'o','ô':'o','ồ':'o','ố':'o','ộ':'o','ổ':'o','ỗ':'o','ơ':'o','ờ':'o','ớ':'o','ợ':'o','ở':'o','ỡ':'o',
                'ù':'u','ú':'u','ụ':'u','ủ':'u','ũ':'u','ư':'u','ừ':'u','ứ':'u','ự':'u','ử':'u','ữ':'u',
                'ỳ':'y','ý':'y','ỵ':'y','ỷ':'y','ỹ':'y',
                'đ':'d',
            };
            return input.toLowerCase().split('').map(c => vietMap[c] ?? c).join('')
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },

        onTitleInput(e, locale) {
            if (this.slugManuallyEdited[locale]) return;
            const ref = this.$refs['slug_' + locale];
            if (ref) ref.value = this.slugify(e.target.value);
        },

        onSlugManualEdit(locale, e) {
            // Empty slug → resume auto-gen from title; non-empty → user owns the value.
            this.slugManuallyEdited[locale] = (e?.target?.value ?? '').trim() !== '';
        },
    };
};
</script>
@endpush
@endsection
