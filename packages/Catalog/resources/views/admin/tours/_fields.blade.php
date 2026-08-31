@php
    $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English'];
    $galleryStr = is_array($tour->gallery_media_ids ?? null) ? implode(',', $tour->gallery_media_ids) : '';
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';
@endphp

{{-- Card 1: General --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('catalog::catalog.sections.general') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.status') }} <span class="text-red-500">*</span></label>
            <select name="status" class="{{ $inputClass }} @error('status') border-red-500 @enderror">
                <option value="draft" @selected(old('status', $tour->status ?? 'draft') === 'draft')>{{ __('catalog::catalog.status.draft') }}</option>
                <option value="published" @selected(old('status', $tour->status ?? 'draft') === 'published')>{{ __('catalog::catalog.status.published') }}</option>
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.partner') }}</label>
            <select name="partner_id" class="{{ $inputClass }} @error('partner_id') border-red-500 @enderror">
                <option value="">—</option>
                @foreach($partners as $p)
                    @php $ptr = $p->translations->firstWhere('locale', app()->getLocale()) ?? $p->translations->first(); @endphp
                    <option value="{{ $p->id }}" @selected((int) old('partner_id', $tour->partner_id) === $p->id)>{{ $ptr?->name ?? '#'.$p->id }}</option>
                @endforeach
            </select>
            @error('partner_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.published_at') }}</label>
            <input name="published_at" type="datetime-local"
                   value="{{ old('published_at', $tour->published_at?->format('Y-m-d\\TH:i')) }}"
                   class="{{ $inputClass }} @error('published_at') border-red-500 @enderror">
            @error('published_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.duration_days') }}</label>
            <input name="duration_days" type="number" min="1" max="365"
                   value="{{ old('duration_days', $tour->duration_days) }}"
                   class="{{ $inputClass }} @error('duration_days') border-red-500 @enderror">
            @error('duration_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.price_from') }}</label>
            <input name="price_from" type="number" step="0.01" min="0"
                   value="{{ old('price_from', $tour->price_from) }}"
                   class="{{ $inputClass }} @error('price_from') border-red-500 @enderror">
            @error('price_from')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.currency') }}</label>
            <input name="currency" maxlength="5"
                   value="{{ old('currency', $tour->currency ?? 'VND') }}"
                   class="{{ $inputClass }} uppercase @error('currency') border-red-500 @enderror">
            @error('currency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.sort_order') }}</label>
            <input name="sort_order" type="number" min="0"
                   value="{{ old('sort_order', $tour->sort_order) }}"
                   class="{{ $inputClass }} @error('sort_order') border-red-500 @enderror">
            @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.cta_app_url') }}</label>
            <input name="cta_app_url" type="url" maxlength="500"
                   value="{{ old('cta_app_url', $tour->cta_app_url) }}"
                   class="{{ $inputClass }} @error('cta_app_url') border-red-500 @enderror">
            @error('cta_app_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 cursor-pointer mt-7">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1"
                       @checked(old('is_featured', $tour->is_featured))
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">{{ __('catalog::catalog.item_fields.is_featured') }}</span>
            </label>
        </div>
    </div>
</div>

{{-- Card 2: Multilingual content (tabs vi / en) --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6"
     x-data="{ activeLocale: '{{ $locales[0] ?? 'vi' }}' }">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('catalog::catalog.sections.content') }}</h2>
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

    @foreach($locales as $idx => $locale)
        @php $tr = $tour->translations->firstWhere('locale', $locale); @endphp
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak class="space-y-4">
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.title') }} <span class="text-red-500">*</span></label>
                    <input name="translations[{{ $idx }}][title]" required maxlength="255"
                           value="{{ old('translations.'.$idx.'.title', $tr?->title) }}"
                           class="{{ $inputClass }} @error('translations.'.$idx.'.title') border-red-500 @enderror">
                    @error('translations.'.$idx.'.title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.slug') }} <span class="text-red-500">*</span></label>
                    <input name="translations[{{ $idx }}][slug]" required maxlength="255" pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                           value="{{ old('translations.'.$idx.'.slug', $tr?->slug) }}"
                           class="{{ $inputClass }} @error('translations.'.$idx.'.slug') border-red-500 @enderror">
                    @error('translations.'.$idx.'.slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.excerpt') }}</label>
                    <textarea name="translations[{{ $idx }}][excerpt]" rows="2" maxlength="1000"
                              class="{{ $inputClass }} @error('translations.'.$idx.'.excerpt') border-red-500 @enderror">{{ old('translations.'.$idx.'.excerpt', $tr?->excerpt) }}</textarea>
                    @error('translations.'.$idx.'.excerpt')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-core::ckeditor :name="'translations['.$idx.'][body]'"
                                      :value="old('translations.'.$idx.'.body', $tr?->body ?? '')"
                                      :label="__('catalog::catalog.item_fields.body')"
                                      rows="10" />
                </div>

                <div>
                    <x-core::ckeditor :name="'translations['.$idx.'][itinerary]'"
                                      :value="old('translations.'.$idx.'.itinerary', $tr?->itinerary ?? '')"
                                      :label="__('catalog::catalog.item_fields.itinerary')"
                                      rows="8" />
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.seo_title') }}</label>
                    <input name="translations[{{ $idx }}][seo_title]" maxlength="255"
                           value="{{ old('translations.'.$idx.'.seo_title', $tr?->seo_title) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.seo_og_image') }}</label>
                    <input name="translations[{{ $idx }}][seo_og_image]" maxlength="500"
                           value="{{ old('translations.'.$idx.'.seo_og_image', $tr?->seo_og_image) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.seo_description') }}</label>
                    <textarea name="translations[{{ $idx }}][seo_description]" rows="2" maxlength="500"
                              class="{{ $inputClass }}">{{ old('translations.'.$idx.'.seo_description', $tr?->seo_description) }}</textarea>
                </div>
            </div>
        @endforeach
</div>

{{-- Card 3: Media --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('catalog::catalog.sections.media') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.cover_media') }}</label>
            <input name="cover_media_id" type="number" min="1"
                   value="{{ old('cover_media_id', $tour->cover_media_id) }}"
                   class="{{ $inputClass }} @error('cover_media_id') border-red-500 @enderror">
            @error('cover_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="{{ $labelClass }}">{{ __('catalog::catalog.item_fields.gallery') }}</label>
            <input name="gallery_media_ids"
                   value="{{ old('gallery_media_ids', $galleryStr) }}"
                   placeholder="e.g. 12,34,56"
                   class="{{ $inputClass }} font-mono">
            @error('gallery_media_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
