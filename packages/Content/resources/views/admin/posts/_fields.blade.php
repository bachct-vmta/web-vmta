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
            <label class="{{ $labelClass }}">{{ __('content::content.fields.status') }} <span class="text-red-500">*</span></label>
            <select name="status" class="{{ $inputClass }} @error('status') border-red-500 @enderror">
                <option value="draft" @selected(old('status', $post->status) === 'draft')>{{ __('content::content.status.draft') }}</option>
                <option value="published" @selected(old('status', $post->status) === 'published')>{{ __('content::content.status.published') }}</option>
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('content::content.fields.category') }}</label>
            <select name="category_id" class="{{ $inputClass }} @error('category_id') border-red-500 @enderror">
                <option value="">{{ __('content::content.fields.no_parent') }}</option>
                @foreach($categories as $c)
                    @php $ctr = $c->translations->firstWhere('locale', app()->getLocale()) ?? $c->translations->first(); @endphp
                    <option value="{{ $c->id }}" @selected((int) old('category_id', $post->category_id) === $c->id)>{{ $ctr?->name ?? '#'.$c->id }}</option>
                @endforeach
            </select>
            @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('content::content.fields.published_at') }}</label>
            <input name="published_at" type="datetime-local"
                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\\TH:i')) }}"
                   class="{{ $inputClass }} @error('published_at') border-red-500 @enderror">
            @error('published_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-core::media-picker
                name="cover_media_id"
                :value="$post->cover_media_id"
                :preview-url="$post->coverMedia?->permalink"
                :label="__('content::content.fields.cover')"
                store="id"
            />
            @error('cover_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2 flex items-center">
            <label class="flex items-center gap-2 cursor-pointer mt-7">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1"
                       @checked(old('is_featured', $post->is_featured))
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">{{ __('content::content.fields.is_featured') }}</span>
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
        @php $tr = $post->translations->firstWhere('locale', $locale); @endphp
        {{-- Per-locale auto-slug: typing the title auto-rewrites the slug, UNTIL
             the admin manually edits the slug field — then it locks for the rest
             of the session. Works on both Create and Edit screens (on Edit the
             initial slug is treated as the default value, not as a lock signal).
             Each locale tab has its own scope so VI and EN don't bleed. --}}
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak
             x-data="{
                 slugLocked: false,
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
                 onTitleInput($event) {
                     if (this.slugLocked) return;
                     this.$refs.slugInput.value = this.slugify($event.target.value);
                 },
                 @if($locale === 'en')
                 translating: false,
                 translateError: '',
                 async doTranslate() {
                     this.translating = true;
                     this.translateError = '';
                     const get = n => document.getElementsByName(n)[0];
                     const viTitle   = get('translations[{{ $viIdx }}][title]')?.value   || '';
                     const viExcerpt = get('translations[{{ $viIdx }}][excerpt]')?.value || '';
                     const viBody    = get('translations[{{ $viIdx }}][body]')?._ckEditor?.getData() || '';
                     const csrf      = document.querySelector('[name=_token]')?.value || '';
                     const resp = await fetch('/admin/translate', {
                         method: 'POST',
                         headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                         body: JSON.stringify({fields: [viTitle, viExcerpt, viBody]}),
                     }).catch(() => null);
                     if (!resp?.ok) {
                         this.translateError = 'Dịch thất bại. Thử lại sau.';
                         this.translating = false;
                         return;
                     }
                     const {translated} = await resp.json();
                     const enTitle   = get('translations[{{ $enIdx }}][title]');
                     const enExcerpt = get('translations[{{ $enIdx }}][excerpt]');
                     const enBody    = get('translations[{{ $enIdx }}][body]');
                     if (enTitle && translated[0]) {
                         enTitle.value = translated[0];
                         this.slugLocked = false;
                         this.$refs.slugInput.value = this.slugify(translated[0]);
                         this.slugLocked = true;
                     }
                     if (enExcerpt && translated[1]) enExcerpt.value = translated[1];
                     if (enBody?._ckEditor && translated[2]) enBody._ckEditor.setData(translated[2]);
                     this.translating = false;
                 },
                 @endif
             }"
             class="space-y-4">
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">{{ __('content::content.fields.title') }} <span class="text-red-500">*</span></label>
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
                <input name="translations[{{ $idx }}][title]" required maxlength="255"
                       value="{{ old('translations.'.$idx.'.title', $tr?->title) }}"
                       @input="onTitleInput($event)"
                       class="{{ $inputClass }} @error('translations.'.$idx.'.title') border-red-500 @enderror">
                @error('translations.'.$idx.'.title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('content::content.fields.slug') }} <span class="text-red-500">*</span></label>
                <input name="translations[{{ $idx }}][slug]" required maxlength="255" pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                       x-ref="slugInput"
                       value="{{ old('translations.'.$idx.'.slug', $tr?->slug) }}"
                       @input="slugLocked = true"
                       class="{{ $inputClass }} @error('translations.'.$idx.'.slug') border-red-500 @enderror">
                @error('translations.'.$idx.'.slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('content::content.fields.excerpt') }}</label>
                <textarea name="translations[{{ $idx }}][excerpt]" rows="2" maxlength="1000"
                          class="{{ $inputClass }}">{{ old('translations.'.$idx.'.excerpt', $tr?->excerpt) }}</textarea>
            </div>

            <div>
                <x-core::ckeditor :name="'translations['.$idx.'][body]'"
                                  :value="old('translations.'.$idx.'.body', $tr?->body ?? '')"
                                  :label="__('content::content.fields.body')"
                                  rows="15" />
            </div>
        </div>
    @endforeach
</div>
