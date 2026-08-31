{{--
    Figma 23:309 y148. Cấp cuối teal đậm, cấp trước #d9d9d9.
    Mục không có url render thành text trơn — đó là cách cấp "Sản phẩm" hoạt động.
--}}
@php($breadcrumbs = $breadcrumbs ?? [])

<nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-x-[8px] gap-y-1 text-[14px] leading-[18px]">
    @foreach($breadcrumbs as $i => $crumb)
        @php($isLast = $i === array_key_last($breadcrumbs))

        @if(! empty($crumb['url']) && ! $isLast)
            <a href="{{ $crumb['url'] }}"
               class="text-[#d9d9d9] transition-colors hover:text-vmta-teal">{{ $crumb['label'] }}</a>
        @else
            <span @class([
                'text-[#d9d9d9]' => ! $isLast,
                'font-bold text-vmta-teal' => $isLast,
            ]) @if($isLast) aria-current="page" @endif>{{ $crumb['label'] }}</span>
        @endif

        @unless($isLast)
            <svg class="h-[19px] w-[19px] shrink-0 text-[#d9d9d9]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endunless
    @endforeach
</nav>
