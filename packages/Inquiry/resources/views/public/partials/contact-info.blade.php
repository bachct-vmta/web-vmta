@php
    $offices     = $officesSection ?? null;
    $officeItems = is_array($offices?->items) ? array_values($offices->items) : [];
    $sectionImg  = media_url($offices?->image_1_media_id) ?: asset('images/contact/section-image.jpg');
    $mapEmbed    = $offices?->map_embed;
@endphp
{{-- Direct contact information section --}}
<section class="py-[60px] md:py-[88px] bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="font-sharp-bo text-[36px] md:text-[48px] uppercase font-bold text-center text-[#0b7f7c] mb-12">
            {{ $offices?->title ?: __('inquiry::inquiry.contact_direct_heading') }}
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start">
            <div class="space-y-8">
                @if(count($officeItems))
                    @foreach($officeItems as $o)
                        <div>
                            <h3 class="font-sharp-bo uppercase font-bold text-[#4b4b4b] text-base md:text-xl mb-4">
                                {{ $o['name'] ?? '' }}
                            </h3>
                            @if(! empty($o['note']))
                                <p class="text-slate-500 text-sm mb-2">{{ $o['note'] }}</p>
                            @endif
                            <ul class="font-utm-helve text-slate-700 text-base md:text-lg leading-relaxed list-disc pl-5">
                                @if(! empty($o['address']))
                                    <li>{{ __('inquiry::inquiry.office_address_label') }}: {{ $o['address'] }}</li>
                                @endif
                                @if(! empty($o['phone']))
                                    <li>
                                        <a href="tel:{{ $o['phone'] }}" class="text-slate-700 hover:text-[#0b7f7c]">{{ $o['phone'] }}</a>
                                    </li>
                                @endif
                                @if(! empty($o['email']))
                                    <li>
                                        {{ __('inquiry::inquiry.office_email_label') }}:
                                        <a href="mailto:{{ $o['email'] }}" class="uppercase text-slate-700 hover:text-[#0b7f7c]">{{ $o['email'] }}</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    @endforeach
                @else
                    {{-- Fallback to env-config offices when the section has no items yet --}}
                    <div>
                        <h3 class="font-sharp-bo uppercase font-bold text-[#4b4b4b] text-base md:text-xl mb-4">
                            {{ __('inquiry::inquiry.office_hq_title') }}:
                        </h3>
                        <ul class="font-utm-helve text-slate-700 text-base md:text-lg leading-relaxed list-disc pl-5">
                            <li>{{ __('inquiry::inquiry.office_address_label') }}: {{ config('inquiry.offices.hq.address') }}</li>
                            @if(config('inquiry.offices.hq.email'))
                                <li>{{ __('inquiry::inquiry.office_email_label') }}:
                                    <a href="mailto:{{ config('inquiry.offices.hq.email') }}" class="uppercase text-slate-700 hover:text-[#0b7f7c]">{{ config('inquiry.offices.hq.email') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

            </div>

            <div class="space-y-6">
                <img src="{{ $sectionImg }}"
                     class="w-full rounded-[28px] object-cover"
                     loading="lazy" decoding="async" alt="">
                @if(filled($mapEmbed))
                    <div class="overflow-hidden rounded-[28px] [&_iframe]:w-full [&_iframe]:h-[320px] [&_iframe]:border-0">
                        {!! $mapEmbed !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
