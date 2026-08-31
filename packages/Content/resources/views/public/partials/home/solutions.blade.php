@php $items = $translation?->items ?? []; @endphp
<section class="vmta-bg-filter-20 relative py-[60px] md-fs:py-[90px] overflow-hidden bg-white" data-home-section="solutions">
    <div class="absolute inset-0">
        <img src="{{ asset('images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png') }}"
             class="vmta-bg-img w-full h-full object-cover"
             alt="" loading="lazy" aria-hidden="true">
    </div>
    <div class="relative max-w-7xl mx-auto px-4">
        @if($translation?->title)
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-center text-[#0b7f7c] font-bold">
                {{ $translation->title }}
            </h2>
        @endif
        @if($translation?->subtitle)
            <p class="mt-3 font-utm-helve fs-vmta-25 font-bold uppercase text-center text-[#0b7f7c] max-w-3xl mx-auto">{{ $translation->subtitle }}</p>
        @endif
        <div class="mt-10 md-fs:mt-[40px] grid grid-cols-1 md:grid-cols-3 gap-8 md-fs:gap-12">
            @foreach($items as $i => $item)
                {{-- icon-box matching .ss-giai-phap .icon-box (vmta.vn) --}}
                <div class="text-center p-[3.125rem] rounded-[1.25rem] bg-[linear-gradient(180deg,#14acab_0%,#0b7f7c_100%)]">
                    <img src="{{ asset('images/home/solutions/' . ($item['icon'] ?? 'icon-' . ($i + 1) . '.png')) }}"
                         alt="{{ $item['title'] ?? '' }}"
                         class="mx-auto h-[60px] w-auto object-contain"
                         loading="lazy">
                    <h3 class="mt-5 font-utm-helve fs-vmta-20 font-bold uppercase" style="text-align: center;">
                        <span class="text-white">{{ $item['title'] ?? '' }}</span>
                    </h3>
                    <p class="mt-3 font-utm-helve text-white leading-relaxed text-justify" style="text-align-last: center;">{{ $item['body'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
