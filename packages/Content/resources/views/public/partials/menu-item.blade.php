{{--
    Recursive menu item renderer.
    Vars in:
      - $item  \Packages\Content\Src\Models\MenuItem
      - $mode  ?string  'desktop' (dropdown nổi) | 'mobile' (accordion)
      - $depth ?int     0 cho cấp gốc
--}}
@php
    $locale = app()->getLocale();
    $tr = $item->translations->firstWhere('locale', $locale) ?? $item->translations->first();
    $rawHref = match (true) {
        $item->link_type === 'url' && ! empty($tr?->url) => $tr->url,
        // Morph target resolution is deferred to a later slice (morphMap whitelist).
        $item->link_type === 'morph' => '#',
        default => '#',
    };
    // Scheme guard: allow relative (/, #, ?) and http/https/mailto/tel only.
    // Anything else — particularly `javascript:`, `data:`, `file:` — collapses to '#'.
    $hrefLower = strtolower(ltrim((string) $rawHref));
    $isSafe = $hrefLower === ''
        || str_starts_with($hrefLower, '/')
        || str_starts_with($hrefLower, '#')
        || str_starts_with($hrefLower, '?')
        || str_starts_with($hrefLower, 'http://')
        || str_starts_with($hrefLower, 'https://')
        || str_starts_with($hrefLower, 'mailto:')
        || str_starts_with($hrefLower, 'tel:');
    $href = $isSafe ? $rawHref : '#';
    $hasChildren = isset($item->children) && $item->children->isNotEmpty();
    $hrefPath = trim(parse_url((string) $href, PHP_URL_PATH) ?? '', '/');
    $currentPath = trim(request()->path(), '/');
    $isActive = $hrefPath !== '' && $hrefPath === $currentPath;

    $mode = $mode ?? 'desktop';
    $depth = $depth ?? 0;
    $isDesktop = $mode === 'desktop';
    // Cấp 1 đổ xuống dưới, cấp sâu hơn bay sang phải
    $panelPosition = $depth === 0 ? 'top-full left-0 pt-2' : 'top-0 left-full pl-2';
@endphp
<li class="menu-item{{ $hasChildren ? ' menu-item-has-children relative' : '' }} {{ $item->css_class }}"
    @if($hasChildren)
        x-data="{ open: false }"
        @if($isDesktop)
            @mouseenter="open = true"
            @mouseleave="open = false"
            @focusin="open = true"
            @focusout="if (! $el.contains($event.relatedTarget)) open = false"
            @keydown.escape.stop="open = false"
        @endif
    @endif>
    <div class="flex items-center{{ $hasChildren && ! $isDesktop ? ' justify-between pr-3' : '' }}">
        <a href="{{ $href }}"
           @if($item->open_new_tab) target="_blank" rel="noopener" @endif
           @if($isActive) style="color: #d31e45;" @endif
           class="block px-3 py-2 w-full {{ $isActive ? 'text-[#d31e45]' : '' }}">
            @if($item->icon)
                <span class="material-symbols-rounded text-[18px] align-middle mr-1">{{ $item->icon }}</span>
            @endif
            {{ $tr?->label ?? '—' }}
        </a>

        @if($hasChildren)
            @if($isDesktop)
                <svg class="h-3 w-3 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                </svg>
            @else
                <button type="button"
                        @click.prevent="open = ! open"
                        :aria-expanded="open ? 'true' : 'false'"
                        class="shrink-0 p-2 text-current"
                        aria-label="{{ $tr?->label ?? '' }}">
                    <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            @endif
        @endif
    </div>

    @if($hasChildren)
        @if($isDesktop)
            <div x-show="open"
                 x-cloak
                 x-transition.opacity.duration.150ms
                 class="absolute {{ $panelPosition }} z-50 min-w-[200px]">
                <ul class="rounded-md border border-slate-100 bg-white py-2 shadow-lg [&_a]:whitespace-nowrap [&_a]:px-4 [&_a]:py-2.5 [&_a:hover]:bg-slate-50">
                    @foreach($item->children as $child)
                        @include('content::public.partials.menu-item', ['item' => $child, 'mode' => $mode, 'depth' => $depth + 1])
                    @endforeach
                </ul>
            </div>
        @else
            <ul x-show="open" x-cloak x-transition.opacity.duration.150ms class="pl-4 bg-slate-50/60">
                @foreach($item->children as $child)
                    @include('content::public.partials.menu-item', ['item' => $child, 'mode' => $mode, 'depth' => $depth + 1])
                @endforeach
            </ul>
        @endif
    @endif
</li>
