@props(['variant' => 'inline'])

@php
    /** @var \Packages\Localization\Src\View\Components\LanguageSwitcher $this */
    $items = $locales();
@endphp

<nav class="vmta-lang-switcher vmta-lang-switcher--{{ $variant }}" aria-label="{{ __('Language') }}">
    <ul class="flex items-center gap-2 text-sm">
        @foreach ($items as $loc)
            <li>
                <a href="{{ $loc['url'] }}"
                   hreflang="{{ $loc['code'] }}"
                   rel="alternate"
                   class="px-2 py-1 rounded {{ $loc['active'] ? 'font-semibold underline' : 'opacity-70 hover:opacity-100' }}">
                    {{ strtoupper($loc['code']) }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
