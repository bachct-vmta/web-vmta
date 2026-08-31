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
