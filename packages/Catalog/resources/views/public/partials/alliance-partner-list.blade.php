@php
    $locale = app()->getLocale();
@endphp
@if(($partnerGroups ?? collect())->isNotEmpty())
<section id="section-alliance-partners" class="pt-[60px] md-fs:pt-[90px] pb-[60px] md-fs:pb-[90px] bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-center text-[#0b7f7c] vmta-letter-spacing-0 mb-10">
            {{ __('catalog::public.alliance_partners.heading') }}
        </h2>

        <div class="grid grid-cols-1 md-fs:grid-cols-2 gap-8 md-fs:gap-12">
            @foreach($partnerGroups as $type => $partners)
                <div>
                    <h3 class="font-utm-helve fs-vmta-30 uppercase font-bold leading-[1.35] text-[#0b7f7c]">
                        {{ __('catalog::public.alliance_partners.groups.'.$type) }}
                    </h3>
                    <div class="mt-5 grid grid-cols-3 md-fs:grid-cols-4 gap-3">
                        @foreach($partners as $partner)
                            @php
                                $tr = $partner->translations->firstWhere('locale', $locale) ?? $partner->translations->first();
                                $logo = media_permalink_url($partner->logoMedia->permalink);
                            @endphp
                            @if($partner->website)
                                <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer"
                                   class="aspect-video overflow-hidden rounded-lg ring-1 ring-slate-200 transition hover:ring-[#0b7f7c]">
                                    <img src="{{ $logo }}" alt="{{ $tr?->name }}" loading="lazy"
                                         class="w-full h-full object-cover">
                                </a>
                            @else
                                <div class="aspect-video overflow-hidden rounded-lg ring-1 ring-slate-200">
                                    <img src="{{ $logo }}" alt="{{ $tr?->name }}" loading="lazy"
                                         class="w-full h-full object-cover">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
