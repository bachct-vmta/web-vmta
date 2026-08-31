@php
    $current = app()->getLocale();
    // Switcher always lands on the target locale's homepage (simpler UX —
    // avoids 404s on slugs that don't exist in the other locale, and avoids
    // confusing users who expect a "switch language" reset to landing page).
@endphp
<div class="flex items-center gap-2 text-xs">
    <a href="{{ url('/vi') }}" aria-label="Tiếng Việt"
       class="{{ $current === 'vi' ? 'opacity-100' : 'opacity-50 hover:opacity-100' }}">
        <img src="https://flagcdn.com/w40/vn.png" alt="VI" class="h-4 w-6">
    </a>
    <span class="text-slate-400">|</span>
    <a href="{{ url('/en') }}" aria-label="English"
       class="{{ $current === 'en' ? 'opacity-100' : 'opacity-50 hover:opacity-100' }}">
        <img src="https://flagcdn.com/w40/us.png" alt="EN" class="h-4 w-6">
    </a>
</div>
