@php
    $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English'];
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';

    // Giữ thứ tự admin đã sắp; sau lỗi validate thì lấy lại từ old() thay vì từ bản ghi
    $certificateIds = old('certificates_media_ids', $facility->certificates_media_ids ?? []);
    $certificateIds = is_string($certificateIds)
        ? array_filter(array_map('intval', explode(',', $certificateIds)))
        : array_map('intval', (array) $certificateIds);

    $certificateItems = collect($certificateIds)
        ->map(fn (int $id) => ['id' => $id, 'media' => \Packages\Core\Src\Models\MediaFile::find($id)])
        ->filter(fn (array $row) => $row['media'] !== null)
        ->map(fn (array $row) => ['id' => $row['id'], 'url' => media_permalink_url($row['media']->permalink)])
        ->values()
        ->all();
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dental::dental.sections.general') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            @include('dental::admin._searchable-select', [
                'name' => 'dental_category_id',
                'options' => $categories,
                'selected' => old('dental_category_id', $facility->dental_category_id),
                'label' => __('dental::dental.fields.category'),
                'required' => true,
            ])
            @error('dental_category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        @include('dental::admin._publish-fields', ['model' => $facility, 'showSchedule' => false])

        <div class="md:col-span-2 flex items-center">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_operating" value="0">
                <input type="checkbox" name="is_operating" value="1"
                       @checked(old('is_operating', $facility->exists ? $facility->is_operating : true))
                       class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm text-gray-700">{{ __('dental::dental.fields.is_operating') }}</span>
            </label>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dental::dental.sections.media') }}</h2>

    <div class="space-y-4">
        <div>
            <x-core::media-picker
                name="cover_media_id"
                :value="$facility->cover_media_id"
                :preview-url="$facility->coverMedia?->permalink"
                :label="__('dental::dental.fields.cover')"
                store="id"
            />
            @error('cover_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            @include('dental::admin._gallery-picker', [
                'name' => 'certificates_media_ids',
                'label' => __('dental::dental.fields.certificates'),
                'items' => $certificateItems,
            ])
            @error('certificates_media_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
        @php $tr = $facility->translations->firstWhere('locale', $locale); @endphp
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak class="space-y-4">
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

            @if($locale === 'en' && $viIdx !== false)
                @include('dental::admin._translate-button', [
                    'viIdx' => $viIdx, 'enIdx' => $enIdx,
                    'plain' => ['name', 'address'], 'rich' => [],
                ])
            @endif

            <div>
                <label class="{{ $labelClass }}">{{ __('dental::dental.fields.name') }} <span class="text-red-500">*</span></label>
                <input name="translations[{{ $idx }}][name]" required maxlength="255"
                       @input="syncSlug({{ $idx }}, '{{ $locale }}', $event.target.value)"
                       value="{{ old('translations.'.$idx.'.name', $tr?->name) }}"
                       class="{{ $inputClass }} @error('translations.'.$idx.'.name') border-red-500 @enderror">
                @error('translations.'.$idx.'.name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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

            <div>
                <label class="{{ $labelClass }}">{{ __('dental::dental.fields.address') }}</label>
                <input name="translations[{{ $idx }}][address]" maxlength="255"
                       value="{{ old('translations.'.$idx.'.address', $tr?->address) }}"
                       class="{{ $inputClass }}">
            </div>

        </div>
    @endforeach
</div>

@include('dental::admin._locale-tabs-script')
