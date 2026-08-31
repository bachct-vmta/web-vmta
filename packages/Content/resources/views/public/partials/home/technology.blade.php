@php $items = $translation?->items ?? []; @endphp
<section class="relative overflow-hidden" data-home-section="technology">
    {{-- Photo banner: image full opacity covers full section like original VMTA --}}
    <div class="absolute inset-0">
        <img src="{{ asset('images/home/technology/bg.jpg') }}"
             class="w-full h-full object-cover"
             alt="" loading="lazy" aria-hidden="true">
    </div>

    <div class="relative max-w-7xl mx-auto px-4 py-[100px] md-fs:py-[140px]">
        @if($translation?->title)
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] mb-8 md-fs:mb-12 max-w-3xl">
                {{ $translation->title }}
            </h2>
        @endif

        <div class="space-y-6 md-fs:space-y-8 max-w-3xl">
            @foreach($items as $item)
                <div>
                    @if(!empty($item['title']))
                        <h3 class="font-utm-helve fs-vmta-25 font-bold text-[#d31e45] mb-3 uppercase">{{ $item['title'] }}</h3>
                    @endif
                    @if(!empty($item['bullets']))
                        <ul class="space-y-2">
                            @foreach($item['bullets'] as $bullet)
                                <li class="flex items-start gap-3">
                                    <span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#0b7f7c] shrink-0" aria-hidden="true"></span>
                                    <span class="font-utm-helve text-slate-800">{{ $bullet }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
