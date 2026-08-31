@php
    $items = $translation?->items ?? [];
    $marqueeActive = $section?->marquee_active ?? true;
    $bg = asset('images/home/hero/banner-bg.png');
@endphp
<section class="vmta-banner-hero relative overflow-hidden bg-white" data-home-section="hero">
    <div class="absolute inset-0">
        <img src="{{ $bg }}"
             class="vmta-bg-img w-full h-full object-cover"
             fetchpriority="high" decoding="async" alt="" aria-hidden="true">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 py-24 md:py-36 text-center">
        @if($translation?->title)
            <h1 class="font-sharp-bo fs-vmta-85 uppercase font-bold text-[#0b7f7c] mx-auto max-w-5xl">
                {{ $translation->title }}
            </h1>
        @endif
        @if($translation?->subtitle)
            <p class="mt-4 font-utm-helve fs-vmta-25 uppercase font-bold text-[#0b7f7c] max-w-3xl mx-auto" style="text-align: center;">
                {{ $translation->subtitle }}
            </p>
        @endif
        @if($translation?->body)
            <div class="mt-5 font-utm-helve fs-vmta-25 font-bold text-[#000] max-w-3xl mx-auto leading-relaxed text-justify" style="text-align-last: center;">
                {!! $translation->body !!}
            </div>
        @endif
        @if($translation?->cta_label && $translation?->cta_url)
            <a href="{{ $translation->cta_url }}"
               class="mt-8 inline-block rounded-md bg-[#d31e45] px-8 py-3 text-base font-bold text-white uppercase tracking-wide hover:bg-[#b01838] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d31e45]">
                {{ $translation->cta_label }}
            </a>
        @endif
    </div>

    {{-- Marquee strip (teal bg, white text) — hidden entirely when toggled off in admin --}}
    @if($marqueeActive && count($items) > 0)
        <div class="relative bg-[#0b7f7c] overflow-hidden vmta-marquee py-1">
            <div class="w-full max-w-7xl py-4 mx-auto border-t border-b border-t-[#ffffff] border-b-[#ffffff] overflow-hidden">
                <div class="vmta-marquee-track flex gap-16">
                @foreach(array_merge($items, $items) as $item)
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-utm-helve font-bold text-xl text-white leading-none">{{ $item['label'] ?? '' }}: </span>
                        <span class="font-utm-helve font-bold text-xl text-white leading-none">{{ $item['value'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    @endif
</section>
