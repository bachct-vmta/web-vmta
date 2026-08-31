@php $items = $translation?->items ?? []; @endphp
<section class="vmta-bg-filter-15 vmta-bg-scale-15 relative py-[60px] md-fs:py-[80px] bg-white overflow-hidden" data-home-section="why_vn">
    <div class="absolute inset-0">
        <img src="{{ asset('images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png') }}"
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
        <div class="mt-10 md-fs:mt-[30px]"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[1.875rem] items-stretch">
            @foreach($items as $i => $item)
                <div class="text-center px-4">
                    <img src="{{ asset('images/home/why-vn/' . ($item['icon'] ?? 'icon-' . ($i + 1) . '.png')) }}"
                         alt="{{ $item['title'] ?? '' }}"
                         class="mx-auto h-20 w-20 object-contain mb-4"
                         loading="lazy">
                    <h3 class="font-utm-helve fs-vmta-20 font-bold uppercase" style="text-align: center;">
                        <span class="text-[#d31e45]">{{ $item['title'] ?? '' }}</span>
                    </h3>
                    <p class="mt-3 font-utm-helve text-[#4a4a4a] leading-relaxed text-justify" style="text-align-last:center;">{{ $item['body'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
