@php
    $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English'];
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';
@endphp

{{-- Card 1: General --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('content::content.sections.general') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('content::content.fields.parent') }}</label>
            <select name="parent_id" class="{{ $inputClass }} @error('parent_id') border-red-500 @enderror">
                <option value="">{{ __('content::content.fields.no_parent') }}</option>
                @foreach($parentOptions as $p)
                    @php $ptr = $p->translations->firstWhere('locale', app()->getLocale()) ?? $p->translations->first(); @endphp
                    <option value="{{ $p->id }}" @selected((int) old('parent_id', $category->parent_id) === $p->id)>{{ $ptr?->name ?? '#'.$p->id }}</option>
                @endforeach
            </select>
            @error('parent_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('content::content.fields.sort_order') }}</label>
            <input name="sort_order" type="number" min="0"
                   value="{{ old('sort_order', $category->sort_order) }}"
                   class="{{ $inputClass }} @error('sort_order') border-red-500 @enderror">
            @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 cursor-pointer mt-7">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $category->is_active))
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">{{ __('content::content.fields.is_active') }}</span>
            </label>
        </div>
    </div>
</div>

{{-- Card 2: Multilingual content (tabs vi / en) --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6"
     x-data="{ activeLocale: '{{ $locales[0] ?? 'vi' }}' }">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('content::content.sections.content') }}</h2>
        <nav class="flex gap-1 bg-gray-100 rounded-lg p-1">
            @foreach($locales as $locale)
                <button type="button"
                        @click="activeLocale = '{{ $locale }}'"
                        :class="activeLocale === '{{ $locale }}' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors">
                    {{ $localeLabels[$locale] ?? strtoupper($locale) }}
                </button>
            @endforeach
        </nav>
    </div>

    @php $viIdx = array_search('vi', $locales); $enIdx = array_search('en', $locales); @endphp
    @foreach($locales as $idx => $locale)
        @php $tr = $category->translations->firstWhere('locale', $locale); @endphp
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak class="space-y-4"
             @if($locale === 'en')
             x-data="{
                 translating: false,
                 translateError: '',
                 slugify(s) {
                     return (s || '')
                         .normalize('NFD').replace(/[̀-ͯ]/g, '')
                         .replace(/đ/g, 'd').replace(/Đ/g, 'D')
                         .toLowerCase()
                         .replace(/[^a-z0-9\s-]/g, '')
                         .trim()
                         .replace(/[\s_-]+/g, '-')
                         .replace(/^-+|-+$/g, '');
                 },
                 async doTranslate() {
                     this.translating = true;
                     this.translateError = '';
                     const get = n => document.getElementsByName(n)[0];
                     const viName = get('translations[{{ $viIdx }}][name]')?.value || '';
                     const viDesc = get('translations[{{ $viIdx }}][description]')?.value || '';
                     const csrf   = document.querySelector('[name=_token]')?.value || '';
                     const resp = await fetch('/admin/translate', {
                         method: 'POST',
                         headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                         body: JSON.stringify({fields: [viName, viDesc]}),
                     }).catch(() => null);
                     if (!resp?.ok) {
                         this.translateError = 'Dịch thất bại. Thử lại sau.';
                         this.translating = false;
                         return;
                     }
                     const {translated} = await resp.json();
                     if (translated[0]) {
                         const nameEl = get('translations[{{ $enIdx }}][name]');
                         if (nameEl) nameEl.value = translated[0];
                         const slugEl = get('translations[{{ $enIdx }}][slug]');
                         if (slugEl) slugEl.value = this.slugify(translated[0]);
                     }
                     const descEl = get('translations[{{ $enIdx }}][description]');
                     if (descEl && translated[1]) descEl.value = translated[1];
                     this.translating = false;
                 },
             }"
             @endif>
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">{{ __('content::content.fields.name') }} <span class="text-red-500">*</span></label>
                        @if($locale === 'en')
                        <button type="button" @click="doTranslate()" :disabled="translating"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span x-show="!translating">🌐 Dịch từ Tiếng Việt</span>
                            <span x-show="translating" x-cloak class="flex items-center gap-1">
                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Đang dịch...
                            </span>
                        </button>
                        @endif
                    </div>
                    @if($locale === 'en')
                    <p x-show="translateError" x-text="translateError" class="text-xs text-red-600 -mt-1 mb-1" x-cloak></p>
                    @endif
                    <input name="translations[{{ $idx }}][name]" required maxlength="255"
                           value="{{ old('translations.'.$idx.'.name', $tr?->name) }}"
                           class="{{ $inputClass }} @error('translations.'.$idx.'.name') border-red-500 @enderror">
                    @error('translations.'.$idx.'.name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('content::content.fields.slug') }} <span class="text-red-500">*</span></label>
                    <input name="translations[{{ $idx }}][slug]" required maxlength="255" pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                           value="{{ old('translations.'.$idx.'.slug', $tr?->slug) }}"
                           class="{{ $inputClass }} @error('translations.'.$idx.'.slug') border-red-500 @enderror">
                    @error('translations.'.$idx.'.slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('content::content.fields.description') }}</label>
                    <textarea name="translations[{{ $idx }}][description]" rows="2" maxlength="1000"
                              class="{{ $inputClass }}">{{ old('translations.'.$idx.'.description', $tr?->description) }}</textarea>
                </div>
            </div>
        @endforeach
</div>
