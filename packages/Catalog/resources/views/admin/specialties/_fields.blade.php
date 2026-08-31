@php
    $locales = ['vi', 'en'];
    $action = $mode === 'create'
        ? route(admin_route_name('specialties.store'))
        : route(admin_route_name('specialties.update'), $specialty->id);

    $tabs = [
        ['key' => 'general',   'label' => __('catalog::catalog.specialty.tab.general')],
        ['key' => 'basic',     'label' => __('catalog::catalog.specialty.tab.basic')],
        ['key' => 'intro',     'label' => __('catalog::catalog.specialty.tab.intro')],
        ['key' => 'strengths', 'label' => __('catalog::catalog.specialty.tab.strengths')],
        ['key' => 'hospitals', 'label' => __('catalog::catalog.specialty.tab.hospitals')],
    ];

    $translationByLocale = [];
    foreach ($locales as $loc) {
        $translationByLocale[$loc] = $specialty->translations->firstWhere('locale', $loc);
    }
@endphp

<div
    class="max-w-6xl mx-auto"
    x-data="{ tab: (location.hash || '#tab-general').replace('#tab-','') }"
    @hashchange.window="tab = (location.hash || '#tab-general').replace('#tab-','')"
>
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <nav class="flex flex-wrap gap-1 border-b border-gray-200 mb-4">
        @foreach($tabs as $t)
            <button type="button"
                    @click="tab='{{ $t['key'] }}'; location.hash='#tab-{{ $t['key'] }}'"
                    :class="tab==='{{ $t['key'] }}' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-2 text-sm rounded-t whitespace-nowrap font-medium">
                {{ $t['label'] }}
            </button>
        @endforeach
    </nav>

    <form method="POST" action="{{ $action }}" class="space-y-6" x-data="specialtyForm()">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- ==================== Tab: General ==================== --}}
        <section x-show="tab==='general'" class="space-y-4">
            <div class="rounded border border-slate-200 bg-white p-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-core::media-picker
                        name="icon"
                        :value="$specialty->icon"
                        :preview-url="$specialty->icon"
                        :label="__('catalog::catalog.fields.icon')"
                        store="permalink"
                    />
                </div>

                <div>
                    <x-core::media-picker
                        name="intro_image_media_id"
                        :value="$specialty->intro_image_media_id"
                        :preview-url="$specialty->introImage?->permalink"
                        :label="__('catalog::catalog.specialty.fields.intro_image')"
                        store="id"
                    />
                </div>

                <div class="flex flex-col gap-3 pt-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $specialty->is_active))>
                        {{ __('catalog::catalog.fields.is_active') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="show_lead_form" value="0">
                        <input type="checkbox" name="show_lead_form" value="1"
                               @checked(old('show_lead_form', $specialty->show_lead_form ?? true))>
                        {{ __('catalog::catalog.specialty.fields.show_lead_form') }}
                    </label>
                </div>
            </div>
        </section>

        {{-- Locale hidden inputs (always submitted regardless of active tab) --}}
        @foreach($locales as $idx => $locale)
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">
        @endforeach

        {{-- ==================== Tab: Basic (VI + EN side-by-side) ==================== --}}
        <section x-show="tab==='basic'" class="space-y-4">
            <div class="rounded border border-slate-200 bg-white p-4 space-y-5">
                @foreach([
                    'name'             => ['label' => __('catalog::catalog.fields.name').' *',                       'maxlength' => 255, 'required' => true,  'bind' => 'name'],
                    'slug'             => ['label' => __('catalog::catalog.fields.slug').' *',                       'maxlength' => 255, 'required' => true,  'bind' => 'slug', 'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$', 'hint' => __('catalog::catalog.specialty.hints.slug_auto')],
                    'breadcrumb_label' => ['label' => __('catalog::catalog.specialty.fields.breadcrumb_label'),      'maxlength' => 120, 'required' => false, 'bind' => null],
                    'hero_h1'          => ['label' => __('catalog::catalog.specialty.fields.hero_h1'),               'maxlength' => 255, 'required' => false, 'bind' => null],
                    'description'      => ['label' => __('catalog::catalog.fields.description'),                     'maxlength' => 1000,'required' => false, 'bind' => null, 'textarea' => true],
                ] as $field => $cfg)
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ $cfg['label'] }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($locales as $idx => $locale)
                                @php $tr = $translationByLocale[$locale]; @endphp
                                <div>
                                    <span class="block text-xs uppercase text-slate-500 mb-1">{{ strtoupper($locale) }}</span>
                                    @if(! empty($cfg['textarea']))
                                        <textarea name="translations[{{ $idx }}][{{ $field }}]" rows="3"
                                                  maxlength="{{ $cfg['maxlength'] }}"
                                                  class="w-full rounded border-slate-300 text-sm">{{ old("translations.{$idx}.{$field}", $tr?->{$field}) }}</textarea>
                                    @else
                                        <input type="text" name="translations[{{ $idx }}][{{ $field }}]"
                                               maxlength="{{ $cfg['maxlength'] }}"
                                               value="{{ old("translations.{$idx}.{$field}", $tr?->{$field}) }}"
                                               {{ ! empty($cfg['required']) ? 'required' : '' }}
                                               @if(! empty($cfg['pattern'])) pattern="{{ $cfg['pattern'] }}" @endif
                                               data-locale="{{ $locale }}"
                                               data-bind="{{ $cfg['bind'] ?? '' }}"
                                               @if($field === 'name') @input="onNameInput($event, '{{ $locale }}')" @endif
                                               @if($field === 'slug') x-ref="slug_{{ $locale }}" @input="onSlugManualEdit('{{ $locale }}')" @endif
                                               class="w-full rounded border-slate-300 text-sm font-mono">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if(! empty($cfg['hint']))
                            <p class="text-xs text-slate-500 mt-1">{{ $cfg['hint'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ==================== Tab: Intro (VI + EN side-by-side) ==================== --}}
        <section x-show="tab==='intro'" class="space-y-4">
            <div class="rounded border border-slate-200 bg-white p-4 space-y-5">
                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('catalog::catalog.specialty.fields.intro_h2') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($locales as $idx => $locale)
                            @php $tr = $translationByLocale[$locale]; @endphp
                            <div>
                                <span class="block text-xs uppercase text-slate-500 mb-1">{{ strtoupper($locale) }}</span>
                                <input type="text" name="translations[{{ $idx }}][intro_h2]" maxlength="500"
                                       value="{{ old("translations.{$idx}.intro_h2", $tr?->intro_h2) }}"
                                       class="w-full rounded border-slate-300 text-sm">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('catalog::catalog.specialty.fields.intro_lead') }}</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($locales as $idx => $locale)
                            @php $tr = $translationByLocale[$locale]; @endphp
                            <div>
                                <span class="block text-xs uppercase text-slate-500 mb-1">{{ strtoupper($locale) }}</span>
                                <input type="text" name="translations[{{ $idx }}][intro_lead]" maxlength="500"
                                       value="{{ old("translations.{$idx}.intro_lead", $tr?->intro_lead) }}"
                                       class="w-full rounded border-slate-300 text-sm">
                            </div>
                        @endforeach
                    </div>
                </div>

                @foreach($locales as $idx => $locale)
                    @php $tr = $translationByLocale[$locale]; @endphp
                    <div>
                        <x-core::ckeditor
                            :name="'translations['.$idx.'][intro_body_html]'"
                            :value="old('translations.'.$idx.'.intro_body_html', $tr?->intro_body_html ?? '')"
                            :label="__('catalog::catalog.specialty.fields.intro_body_html').' ('.strtoupper($locale).')'"
                            :rows="12"
                        />
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ==================== Tab: Strengths ==================== --}}
        <section x-show="tab==='strengths'" class="space-y-4">
            <div class="rounded border border-slate-200 bg-white p-4 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($locales as $idx => $locale)
                        @php $tr = $translationByLocale[$locale]; @endphp
                        <div class="space-y-2">
                            <span class="block text-xs uppercase text-slate-500">{{ strtoupper($locale) }} — {{ __('catalog::catalog.specialty.fields.strengths_h2_line1') }}</span>
                            <input type="text" name="translations[{{ $idx }}][strengths_h2_line1]" maxlength="255"
                                   value="{{ old("translations.{$idx}.strengths_h2_line1", $tr?->strengths_h2_line1) }}"
                                   class="w-full rounded border-slate-300 text-sm">
                            <span class="block text-xs uppercase text-slate-500">{{ __('catalog::catalog.specialty.fields.strengths_h2_line2') }}</span>
                            <input type="text" name="translations[{{ $idx }}][strengths_h2_line2]" maxlength="255"
                                   value="{{ old("translations.{$idx}.strengths_h2_line2", $tr?->strengths_h2_line2) }}"
                                   class="w-full rounded border-slate-300 text-sm">
                        </div>
                    @endforeach
                </div>

                @foreach($locales as $idx => $locale)
                    @php
                        $tr = $translationByLocale[$locale];
                        $itemsValue = old("translations.{$idx}.strengths_json", $tr?->strengths_json ?? []);
                    @endphp
                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-sm text-slate-700 mb-3">
                            {{ __('catalog::catalog.specialty.actions.add_strength') }} ({{ strtoupper($locale) }})
                        </h3>
                        <div x-data="strengthsRepeater(@js(is_array($itemsValue) ? $itemsValue : []), '{{ $idx }}')" class="space-y-3">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="border border-slate-200 rounded p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <strong class="text-sm" x-text="`${i + 1}. ${item.title || '—'}`"></strong>
                                        <button type="button" class="text-red-600 text-xs" @click="items.splice(i,1)">
                                            {{ __('catalog::catalog.actions.remove') }}
                                        </button>
                                    </div>
                                    <input type="text"
                                           :name="`translations[{{ $idx }}][strengths_json][${i}][title]`"
                                           x-model="item.title" maxlength="255"
                                           placeholder="{{ __('catalog::catalog.specialty.fields.title') }}"
                                           class="w-full rounded border-slate-300 text-sm">
                                    <input type="hidden"
                                           :name="`translations[{{ $idx }}][strengths_json][${i}][image_media_id]`"
                                           :value="item.image_media_id || ''">
                                    <div class="flex items-center gap-3">
                                        <div class="w-20 h-20 flex-shrink-0 rounded border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                                            <template x-if="item.image_url"><img :src="item.image_url" class="w-full h-full object-cover"></template>
                                            <template x-if="! item.image_url"><span class="text-xs text-slate-400">No image</span></template>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" class="rounded bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5"
                                                    @click="pickMedia(i)">{{ __('Chọn ảnh') }}</button>
                                            <button type="button" x-show="item.image_media_id"
                                                    class="rounded bg-slate-200 text-slate-700 text-xs px-3 py-1.5"
                                                    @click="item.image_media_id = null; item.image_url = ''">{{ __('Bỏ') }}</button>
                                        </div>
                                    </div>
                                    <input type="number" min="0"
                                           :name="`translations[{{ $idx }}][strengths_json][${i}][sort_order]`"
                                           x-model.number="item.sort_order"
                                           placeholder="{{ __('catalog::catalog.fields.sort_order') }}"
                                           class="w-full rounded border-slate-300 text-sm">
                                    <div>
                                        <p class="text-xs font-medium mb-1">{{ __('catalog::catalog.specialty.fields.bullets') }}</p>
                                        <template x-for="(b, bi) in item.bullets" :key="bi">
                                            <div class="flex gap-2 mb-1">
                                                <input type="text"
                                                       :name="`translations[{{ $idx }}][strengths_json][${i}][bullets][${bi}]`"
                                                       x-model="item.bullets[bi]" maxlength="255"
                                                       class="flex-1 rounded border-slate-300 text-sm">
                                                <button type="button" class="text-red-600 text-xs" @click="item.bullets.splice(bi,1)">×</button>
                                            </div>
                                        </template>
                                        <button type="button" class="text-xs text-blue-600 hover:underline"
                                                @click="item.bullets.push('')">
                                            + {{ __('catalog::catalog.specialty.actions.add_bullet') }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <button type="button"
                                    class="rounded border border-blue-600 text-blue-600 px-3 py-2 text-sm hover:bg-blue-50"
                                    @click="addItem()">
                                + {{ __('catalog::catalog.specialty.actions.add_strength') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ==================== Tab: Hospitals ==================== --}}
        <section x-show="tab==='hospitals'" class="space-y-4">
            <div class="rounded border border-slate-200 bg-white p-4 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($locales as $idx => $locale)
                        @php $tr = $translationByLocale[$locale]; @endphp
                        <div class="space-y-2">
                            <span class="block text-xs uppercase text-slate-500">{{ strtoupper($locale) }} — {{ __('catalog::catalog.specialty.fields.hospitals_h2_line1') }}</span>
                            <input type="text" name="translations[{{ $idx }}][hospitals_h2_line1]" maxlength="255"
                                   value="{{ old("translations.{$idx}.hospitals_h2_line1", $tr?->hospitals_h2_line1) }}"
                                   class="w-full rounded border-slate-300 text-sm">
                            <span class="block text-xs uppercase text-slate-500">{{ __('catalog::catalog.specialty.fields.hospitals_h2_line2') }}</span>
                            <input type="text" name="translations[{{ $idx }}][hospitals_h2_line2]" maxlength="255"
                                   value="{{ old("translations.{$idx}.hospitals_h2_line2", $tr?->hospitals_h2_line2) }}"
                                   class="w-full rounded border-slate-300 text-sm">
                            <span class="block text-xs uppercase text-slate-500">{{ __('catalog::catalog.specialty.fields.hospitals_subtitle') }}</span>
                            <input type="text" name="translations[{{ $idx }}][hospitals_subtitle]" maxlength="500"
                                   value="{{ old("translations.{$idx}.hospitals_subtitle", $tr?->hospitals_subtitle) }}"
                                   class="w-full rounded border-slate-300 text-sm">
                        </div>
                    @endforeach
                </div>

                @foreach($locales as $idx => $locale)
                    @php
                        $tr = $translationByLocale[$locale];
                        $itemsValue = old("translations.{$idx}.hospitals_json", $tr?->hospitals_json ?? []);
                    @endphp
                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-sm text-slate-700 mb-3">
                            {{ __('catalog::catalog.specialty.actions.add_hospital') }} ({{ strtoupper($locale) }})
                        </h3>
                        <div x-data="hospitalsRepeater(@js(is_array($itemsValue) ? $itemsValue : []), '{{ $idx }}')" class="space-y-3">
                            <template x-for="(item, i) in items" :key="i">
                                <div class="border border-slate-200 rounded p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <strong class="text-sm" x-text="`${i + 1}. ${item.name || '—'}`"></strong>
                                        <button type="button" class="text-red-600 text-xs" @click="items.splice(i,1)">
                                            {{ __('catalog::catalog.actions.remove') }}
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <input type="text"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][name]`"
                                               x-model="item.name" maxlength="255"
                                               placeholder="{{ __('catalog::catalog.specialty.fields.hospital_name') }}"
                                               class="rounded border-slate-300 text-sm">
                                        <input type="number" min="1"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][partner_id]`"
                                               x-model.number="item.partner_id"
                                               placeholder="{{ __('catalog::catalog.specialty.fields.partner_id') }}"
                                               class="rounded border-slate-300 text-sm">
                                    </div>
                                    <input type="hidden"
                                           :name="`translations[{{ $idx }}][hospitals_json][${i}][image_media_id]`"
                                           :value="item.image_media_id || ''">
                                    <div class="flex items-center gap-3">
                                        <div class="w-20 h-20 flex-shrink-0 rounded border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center">
                                            <template x-if="item.image_url"><img :src="item.image_url" class="w-full h-full object-cover"></template>
                                            <template x-if="! item.image_url"><span class="text-xs text-slate-400">No image</span></template>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" class="rounded bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5"
                                                    @click="pickMedia(i)">{{ __('Chọn ảnh') }}</button>
                                            <button type="button" x-show="item.image_media_id"
                                                    class="rounded bg-slate-200 text-slate-700 text-xs px-3 py-1.5"
                                                    @click="item.image_media_id = null; item.image_url = ''">{{ __('Bỏ') }}</button>
                                        </div>
                                    </div>
                                    <input type="number" min="0"
                                           :name="`translations[{{ $idx }}][hospitals_json][${i}][sort_order]`"
                                           x-model.number="item.sort_order"
                                           placeholder="{{ __('catalog::catalog.fields.sort_order') }}"
                                           class="w-full rounded border-slate-300 text-sm">
                                    <div>
                                        <p class="text-xs font-medium mb-1">{{ __('catalog::catalog.specialty.fields.bullets') }}</p>
                                        <template x-for="(b, bi) in item.bullets" :key="bi">
                                            <div class="flex gap-2 mb-1">
                                                <input type="text"
                                                       :name="`translations[{{ $idx }}][hospitals_json][${i}][bullets][${bi}]`"
                                                       x-model="item.bullets[bi]" maxlength="255"
                                                       class="flex-1 rounded border-slate-300 text-sm">
                                                <button type="button" class="text-red-600 text-xs" @click="item.bullets.splice(bi,1)">×</button>
                                            </div>
                                        </template>
                                        <button type="button" class="text-xs text-blue-600 hover:underline"
                                                @click="item.bullets.push('')">
                                            + {{ __('catalog::catalog.specialty.actions.add_bullet') }}
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                                        <input type="text"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][cta_primary][label]`"
                                               x-model="item.cta_primary.label" maxlength="120"
                                               placeholder="CTA 1 — {{ __('catalog::catalog.specialty.fields.cta_label') }}"
                                               class="rounded border-slate-300 text-sm">
                                        <input type="text"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][cta_primary][url]`"
                                               x-model="item.cta_primary.url" maxlength="500"
                                               placeholder="CTA 1 — URL"
                                               class="rounded border-slate-300 text-sm">
                                        <input type="text"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][cta_secondary][label]`"
                                               x-model="item.cta_secondary.label" maxlength="120"
                                               placeholder="CTA 2 — {{ __('catalog::catalog.specialty.fields.cta_label') }}"
                                               class="rounded border-slate-300 text-sm">
                                        <input type="text"
                                               :name="`translations[{{ $idx }}][hospitals_json][${i}][cta_secondary][url]`"
                                               x-model="item.cta_secondary.url" maxlength="500"
                                               placeholder="CTA 2 — URL"
                                               class="rounded border-slate-300 text-sm">
                                    </div>
                                </div>
                            </template>
                            <button type="button"
                                    class="rounded border border-blue-600 text-blue-600 px-3 py-2 text-sm hover:bg-blue-50"
                                    @click="addItem()">
                                + {{ __('catalog::catalog.specialty.actions.add_hospital') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium">
                {{ $mode === 'create' ? __('catalog::catalog.actions.create') : __('catalog::catalog.actions.update') }}
            </button>
            <a href="{{ route(admin_route_name('specialties.index')) }}" class="text-gray-500 hover:text-gray-700">
                {{ __('catalog::catalog.actions.cancel') }}
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
window.specialtyForm = function () {
    return {
        slugManuallyEdited: { vi: false, en: false },

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

        onNameInput(e, locale) {
            if (this.slugManuallyEdited[locale]) return;
            const ref = this.$refs['slug_' + locale];
            if (ref) ref.value = this.slugify(e.target.value);
        },

        onSlugManualEdit(locale) {
            this.slugManuallyEdited[locale] = true;
        },
    };
};

function normaliseStrength(it) {
    return {
        title: it?.title ?? '',
        image_media_id: it?.image_media_id ?? null,
        image_url: it?.image_url ?? '',
        sort_order: it?.sort_order ?? 0,
        bullets: Array.isArray(it?.bullets) ? [...it.bullets] : [],
    };
}

function normaliseHospital(it) {
    return {
        name: it?.name ?? '',
        partner_id: it?.partner_id ?? null,
        image_media_id: it?.image_media_id ?? null,
        image_url: it?.image_url ?? '',
        sort_order: it?.sort_order ?? 0,
        bullets: Array.isArray(it?.bullets) ? [...it.bullets] : [],
        cta_primary:   { label: it?.cta_primary?.label   ?? '', url: it?.cta_primary?.url   ?? '' },
        cta_secondary: { label: it?.cta_secondary?.label ?? '', url: it?.cta_secondary?.url ?? '' },
    };
}

function openMediaPicker(callback) {
    const url = @json(route('admin.media.index') . '?popup=1&for=specialty-picker');
    window.open(url, 'media_picker_' + Math.random().toString(36).slice(2),
        'width=1100,height=720,resizable=yes,scrollbars=yes');
    const handler = function (e) {
        if (!e.data || e.data.type !== 'media_selected') return;
        callback({ id: e.data.id, url: e.data.url });
        window.removeEventListener('message', handler);
    };
    window.addEventListener('message', handler);
}

window.strengthsRepeater = function (initial, idx) {
    return {
        items: (initial || []).map(normaliseStrength),
        addItem() { this.items.push(normaliseStrength({ bullets: [''] })); },
        pickMedia(i) {
            openMediaPicker(({ id, url }) => {
                this.items[i].image_media_id = id;
                this.items[i].image_url = url;
            });
        },
    };
};

window.hospitalsRepeater = function (initial, idx) {
    return {
        items: (initial || []).map(normaliseHospital),
        addItem() { this.items.push(normaliseHospital({ bullets: [''] })); },
        pickMedia(i) {
            openMediaPicker(({ id, url }) => {
                this.items[i].image_media_id = id;
                this.items[i].image_url = url;
            });
        },
    };
};
</script>
@endpush
