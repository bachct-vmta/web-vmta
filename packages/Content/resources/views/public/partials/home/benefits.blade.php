@php $items = $translation?->items ?? []; @endphp
<section class="vmta-bg-filter-20 relative py-[60px] md-fs:py-[80px] overflow-hidden bg-white" data-home-section="benefits">
    <div class="absolute inset-0">
        <img src="{{ asset('images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png') }}"
             class="vmta-bg-img w-full h-full object-cove scale-[2.5]"
             alt="" loading="lazy" aria-hidden="true">
    </div>
    <div class="relative max-w-7xl mx-auto px-4">
        @if($translation?->title)
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-center text-[#0b7f7c] mb-10 md-fs:mb-14">
                {{ $translation->title }}
            </h2>
        @endif

        <div class="space-y-12 md-fs:space-y-16">
            @foreach($items as $i => $item)
                @php $isEven = $i % 2 === 0; @endphp
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div class="{{ $isEven ? 'lg:order-1' : 'lg:order-2' }}">
                        <img src="{{ asset('images/home/' . ($item['image_url'] ?? 'benefits/row-' . ($i + 1) . '.jpg')) }}"
                             alt="{{ $item['audience'] ?? '' }}"
                             class="w-full rounded-[30px] shadow-md object-cover aspect-video"
                             loading="lazy">
                    </div>
                    <div class="{{ $isEven ? 'lg:order-2' : 'lg:order-1' }}">
                        @if(!empty($item['audience']))
                            <h3 class="font-utm-helve fs-vmta-25 font-bold text-[#d31e45] uppercase leading-snug">
                                {{ $item['audience'] }}
                            </h3>
                        @endif
                        @if(!empty($item['subtitle']))
                            <p class="mt-2 font-utm-helve">{{ $item['subtitle'] }}</p>
                        @endif
                        @if(!empty($item['bullets']))
                            <ul class="mt-5 space-y-3">
                                @foreach($item['bullets'] as $bullet)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-[#0b7f7c] shrink-0" aria-hidden="true"></span>
                                        <span class="font-utm-helve text-slate-700">{{ $bullet }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
