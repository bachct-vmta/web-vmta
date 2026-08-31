@php
    $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English'];
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dental::dental.sections.general') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            @include('dental::admin._searchable-select', [
                'name' => 'dental_facility_id',
                'options' => $facilities,
                'selected' => old('dental_facility_id', $service->dental_facility_id),
                'label' => __('dental::dental.fields.facility'),
                'required' => true,
            ])
            @error('dental_facility_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @include('dental::admin._publish-fields', ['model' => $service, 'showSchedule' => false])
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dental::dental.sections.media') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-core::media-picker
                name="icon_media_id"
                :value="$service->icon_media_id"
                :preview-url="$service->iconMedia?->permalink"
                :label="__('dental::dental.fields.icon')"
                store="id"
            />
            @error('icon_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-core::media-picker
                name="video_poster_media_id"
                :value="$service->video_poster_media_id"
                :preview-url="$service->videoPosterMedia?->permalink"
                :label="__('dental::dental.fields.video_poster')"
                store="id"
            />
            @error('video_poster_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2" x-data="dentalVideoUrlPicker(@js(route('admin.media.index').'?popup=1&for=media-picker'))">
            <label class="{{ $labelClass }}">{{ __('dental::dental.fields.video_url') }}</label>
            <div class="flex gap-2">
                <input name="video_url" type="url" maxlength="255" x-ref="input"
                       value="{{ old('video_url', $service->video_url) }}"
                       placeholder="https://youtu.be/... {{ __('dental::dental.fields.or_pick_file') }}"
                       class="{{ $inputClass }} @error('video_url') border-red-500 @enderror">
                <button type="button" @click="pick()"
                        class="shrink-0 rounded-lg bg-blue-600 px-3 text-xs text-white hover:bg-blue-700">
                    {{ __('dental::dental.actions.pick_from_library') }}
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ __('dental::dental.fields.video_url_hint') }}</p>
            @error('video_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6"
     x-data="dentalLocaleTabs('{{ $locales[0] ?? 'vi' }}')">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('dental::dental.sections.content') }}</h2>
        @include('dental::admin._locale-nav', ['locales' => $locales, 'localeLabels' => $localeLabels])
    </div>

    @php $viIdx = array_search('vi', $locales); $enIdx = array_search('en', $locales); @endphp
    @foreach($locales as $idx => $locale)
        @php $tr = $service->translations->firstWhere('locale', $locale); @endphp
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak class="space-y-4">
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

            @if($locale === 'en' && $viIdx !== false)
                @include('dental::admin._translate-button', [
                    'viIdx' => $viIdx,
                    'enIdx' => $enIdx,
                    'plain' => ['title', 'hero_h1', 'video_caption'],
                    'rich' => ['body', 'comparison_html', 'price_table_html'],
                ])
            @endif

            <div>
                <label class="{{ $labelClass }}">{{ __('dental::dental.fields.title') }} <span class="text-red-500">*</span></label>
                <input name="translations[{{ $idx }}][title]" required maxlength="255"
                       @input="syncSlug({{ $idx }}, '{{ $locale }}', $event.target.value)"
                       value="{{ old('translations.'.$idx.'.title', $tr?->title) }}"
                       class="{{ $inputClass }} @error('translations.'.$idx.'.title') border-red-500 @enderror">
                @error('translations.'.$idx.'.title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="{{ $labelClass }}">{{ __('dental::dental.fields.slug') }} <span class="text-red-500">*</span></label>
                <input name="translations[{{ $idx }}][slug]" required maxlength="255" pattern="^[a-z0-9]+(?:-[a-z0-9]+)*$"
                       x-init="if ($el.value.trim()) slugTouched['{{ $locale }}'] = true"
                       @input="slugTouched['{{ $locale }}'] = true"
                       value="{{ old('translations.'.$idx.'.slug', $tr?->slug) }}"
                       class="{{ $inputClass }} @error('translations.'.$idx.'.slug') border-red-500 @enderror">
                @error('translations.'.$idx.'.slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelClass }}">{{ __('dental::dental.fields.hero_h1') }}</label>
                    <input name="translations[{{ $idx }}][hero_h1]" maxlength="255"
                           value="{{ old('translations.'.$idx.'.hero_h1', $tr?->hero_h1) }}"
                           class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="{{ $labelClass }}">{{ __('dental::dental.fields.video_caption') }}</label>
                    <input name="translations[{{ $idx }}][video_caption]" maxlength="255"
                           value="{{ old('translations.'.$idx.'.video_caption', $tr?->video_caption) }}"
                           class="{{ $inputClass }}">
                </div>
            </div>

            <div>
                <x-core::ckeditor :name="'translations['.$idx.'][body]'"
                                  :value="old('translations.'.$idx.'.body', $tr?->body ?? '')"
                                  :label="__('dental::dental.fields.body')"
                                  rows="15" />
            </div>

            <div>
                <x-core::ckeditor :name="'translations['.$idx.'][comparison_html]'"
                                  :value="old('translations.'.$idx.'.comparison_html', $tr?->comparison_html ?? '')"
                                  :label="__('dental::dental.fields.comparison_html')"
                                  rows="10" />
            </div>

            <div>
                <x-core::ckeditor :name="'translations['.$idx.'][price_table_html]'"
                                  :value="old('translations.'.$idx.'.price_table_html', $tr?->price_table_html ?? '')"
                                  :label="__('dental::dental.fields.price_table_html')"
                                  rows="10" />
            </div>
        </div>
    @endforeach
</div>

@include('dental::admin._locale-tabs-script')

@once
    @push('scripts')
        <script>
            // Chọn file video đã upload rồi ghi URL thẳng vào ô video_url
            window.dentalVideoUrlPicker = function (mediaUrl) {
                return {
                    pick() {
                        const handler = (e) => {
                            if (!e.data || e.data.type !== 'media_selected') return;
                            window.removeEventListener('message', handler);
                            if (e.data.url) this.$refs.input.value = e.data.url;
                        };
                        window.addEventListener('message', handler);

                        window.open(
                            mediaUrl,
                            'dental_video_' + Math.random().toString(36).slice(2),
                            'width=1100,height=720,resizable=yes,scrollbars=yes'
                        );
                    },
                };
            };
        </script>
    @endpush
@endonce
