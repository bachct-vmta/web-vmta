@php
    $locales = $locales ?? ['vi', 'en'];
    $type = $type ?? 'text';
    $rows = $rows ?? 4;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($locales as $locale)
        @php
            $tr = $section?->translations?->firstWhere('locale', $locale);
            $current = old("translations.{$locale}.{$field}", $tr?->{$field});
        @endphp
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $label }} <span class="text-xs text-slate-500">({{ strtoupper($locale) }})</span>
            </label>
            @if($type === 'textarea')
                <textarea name="translations[{{ $locale }}][{{ $field }}]" rows="{{ $rows }}"
                          class="w-full rounded border-slate-300 text-sm">{{ $current }}</textarea>
            @else
                <input type="{{ $type }}" name="translations[{{ $locale }}][{{ $field }}]"
                       value="{{ $current }}" class="w-full rounded border-slate-300 text-sm">
            @endif
        </div>
    @endforeach
</div>
