@php
    $localeLabels = ['vi' => 'Tiếng Việt', 'en' => 'English'];
    $inputClass = 'w-full bg-white border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-2';
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('dental::dental.sections.general') }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @include('dental::admin._publish-fields', ['model' => $category, 'showSchedule' => false])
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
        @php $tr = $category->translations->firstWhere('locale', $locale); @endphp
        <div x-show="activeLocale === '{{ $locale }}'" x-cloak class="space-y-4">
            <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

            @if($locale === 'en' && $viIdx !== false)
                @include('dental::admin._translate-button', [
                    'viIdx' => $viIdx, 'enIdx' => $enIdx,
                    'plain' => ['name'], 'rich' => [],
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
        </div>
    @endforeach
</div>

@include('dental::admin._locale-tabs-script')
