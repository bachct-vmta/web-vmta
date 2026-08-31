@php
    $cv = $coreValuesSection?->translate(app()->getLocale());
    $items = $cv?->items ?? [];
    $aboutItems = $aboutTranslation?->items ?? [];
@endphp
<section class="vmta-bg-filter-20 vmta-bg-scale-15 relative pt-[60px] md-fs:pt-[80px] pb-[60px] md-fs:pb-[80px] bg-white overflow-hidden" data-home-section="values">
    <div class="absolute inset-0">
        <img src="{{ asset('images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png') }}"
             class="vmta-bg-img w-full h-full object-cover"
             alt="" loading="lazy" aria-hidden="true">
    </div>
    <div class="relative max-w-7xl mx-auto px-4">
        {{-- ROW 1: GIÁ TRỊ CỐT LÕI --}}
        @if($cv?->title)
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-center text-[#0b7f7c] font-bold">
                {{ $cv->title }}
            </h2>
        @endif
        @if($cv?->subtitle)
            <p class="mt-3 font-utm-helve fs-vmta-25 font-bold uppercase text-center text-[#0b7f7c] max-w-3xl mx-auto">{{ $cv->subtitle }}</p>
        @endif
        @php $isCarousel = count($items) > 3; @endphp
        <div class="mt-10 md-fs:mt-[30px]"></div>
        {{-- Grid when <=3 items; Swiper carousel when >3, mirroring the About page --}}
        <div @class([
                'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[1.875rem] items-stretch' => ! $isCarousel,
                'vmta-core-values-swiper swiper relative' => $isCarousel,
             ])
             @if($isCarousel) data-vmta-swiper data-per-view="3" @endif>
            <div @class(['swiper-wrapper' => $isCarousel, 'contents' => ! $isCarousel])>
                @foreach($items as $i => $item)
                    <div @class(['text-center px-4', 'swiper-slide !h-auto' => $isCarousel])>
                        <img src="{{ asset('images/home/values/' . ($item['icon'] ?? 'icon-' . (($i % 3) + 1) . '.png')) }}"
                             alt="{{ $item['title'] ?? '' }}"
                             class="mx-auto h-20 w-20 object-contain mb-4"
                             loading="lazy">
                        <h3 class="font-utm-helve fs-vmta-20 font-bold uppercase" style="text-align: center;">
                            <span class="text-[#d31e45]">{{ $item['title'] ?? '' }}</span>
                        </h3>
                        <p class="mt-3 font-utm-helve text-[#4a4a4a] leading-relaxed text-justify" style="text-align-last: center;">{{ $item['body'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
            @if($isCarousel)
                <div class="flex items-center justify-center pt-[20px]">
                    <div class="swiper-pagination !static !w-auto [--swiper-pagination-color:#0b7f7c]"></div>
                </div>
            @endif
        </div>

        {{-- ROW 2: VỀ VMTA (merged into same section as original vmta.vn) --}}
        @if($aboutSection)
            <div data-home-section="about" class="mt-12 md-fs:mt-16 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-7">
                    @if($aboutTranslation?->subtitle)
                        <p class="font-utm-helve text-sm uppercase tracking-widest text-[#0b7f7c] font-bold">{{ $aboutTranslation->subtitle }}</p>
                    @endif
                    @if($aboutTranslation?->title)
                        <h2 class="mt-2 font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c]">
                            {{ $aboutTranslation->title }}
                        </h2>
                    @endif
                    @if($aboutTranslation?->body)
                        <div class="mt-5 font-utm-helve text-slate-700 leading-relaxed space-y-2 text-justify">{!! $aboutTranslation->body !!}</div>
                    @endif
                    @if(count($aboutItems) > 0)
                        <ul class="mt-6 space-y-3">
                            @foreach($aboutItems as $item)
                                <li class="flex items-start gap-3">
                                    <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-[#d31e45] shrink-0" aria-hidden="true"></span>
                                    <span class="font-utm-helve text-slate-700">{{ $item['bullet'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if($aboutTranslation?->cta_label && $aboutTranslation?->cta_url)
                        <a href="{{ $aboutTranslation->cta_url }}"
                           class="mt-8 inline-block rounded-md bg-[#d31e45] text-white px-6 py-2.5 font-bold uppercase hover:bg-[#b01838] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d31e45]">
                            {{ $aboutTranslation->cta_label }}
                        </a>
                    @endif
                </div>
                <div class="lg:col-span-5">
                    <img src="{{ asset('images/home/about/image-right.jpg') }}"
                         alt="{{ $aboutTranslation?->title ?? 'VMTA' }}"
                         class="w-full rounded-[35px] shadow-lg object-cover aspect-[4/5]"
                         loading="lazy">
                </div>
            </div>
        @endif
    </div>
</section>
