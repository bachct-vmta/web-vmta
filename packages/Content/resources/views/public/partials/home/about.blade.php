@php $items = $translation?->items ?? []; @endphp
<section class="vmta-bg-filter-20 relative pt-8 md-fs:pt-[40px] pb-[60px] md-fs:pb-[80px] overflow-hidden bg-white" data-home-section="about">
    <div class="absolute inset-0">
        <img src="{{ asset('images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png') }}"
             class="vmta-bg-img w-full h-full object-cover"
             alt="" loading="lazy" aria-hidden="true" style="object-position: 50% 60%;">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        {{-- Text column --}}
        <div class="lg:col-span-7">
            @if($translation?->subtitle)
                <p class="font-utm-helve text-sm uppercase tracking-widest text-[#0b7f7c] font-bold">{{ $translation->subtitle }}</p>
            @endif
            @if($translation?->title)
                <h2 class="mt-2 font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c]">
                    {{ $translation->title }}
                </h2>
            @endif
            @if($translation?->body)
                <div class="mt-5 font-utm-helve text-slate-700 leading-relaxed space-y-2 text-justify">{!! $translation->body !!}</div>
            @endif
            @if(count($items) > 0)
                <ul class="mt-6 space-y-3">
                    @foreach($items as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-[#d31e45] shrink-0" aria-hidden="true"></span>
                            <span class="font-utm-helve text-slate-700">{{ $item['bullet'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if($translation?->cta_label && $translation?->cta_url)
                <a href="{{ $translation->cta_url }}"
                   class="mt-8 inline-block rounded-md bg-[#d31e45] text-white px-6 py-2.5 font-bold uppercase hover:bg-[#b01838] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d31e45]">
                    {{ $translation->cta_label }}
                </a>
            @endif
        </div>
        {{-- Image column --}}
        <div class="lg:col-span-5">
            <img src="{{ asset('images/home/about/image-right.jpg') }}"
                 alt="{{ $translation?->title ?? 'VMTA' }}"
                 class="w-full rounded-[35px] shadow-lg object-cover aspect-[4/5]"
                 loading="lazy">
        </div>
    </div>
</section>
