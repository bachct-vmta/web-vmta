@php
    $locale = app()->getLocale();
    $leadAction = route('site.'.$locale.'.catalog.specialties.lead.store', [
        'slug' => optional($specialty->translate($locale) ?? $specialty->translations->first())->slug,
    ]);
    $isDentalSource = in_array((string) ($translation->slug ?? ''), ['nha-khoa', 'dentistry'], true);
    $leadBackgroundUrl = $isDentalSource ? asset('images/specialties/nha-khoa/hero-bg.png') : null;
    $shellClass = 'max-w-7xl px-4 mx-auto';
    $headingClass = 'm-0 font-sharp-bo text-[clamp(1.25rem,2.5vw,1.875rem)] font-bold uppercase leading-[1.35] tracking-[0.05em] text-vmta-teal';
    $copyClass = 'mt-4 text-[#4a4a4a] leading-[1.75] [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:mt-3 [&_ul]:list-outside [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-[0.45rem] [&_li::after]:hidden [&_li::after]:[content:none] [&_li::marker]:text-[0.8em] [&_li::marker]:text-vmta-teal';
    $inputClass = 'w-full rounded-none border-0 bg-vmta-teal px-[1.125rem] py-4 text-base text-white outline-2 outline-transparent outline-offset-2 placeholder:text-white/85 focus:outline-vmta-teal/35';
@endphp

<section id="lead-form" class="vmta-specialty-lead-section relative overflow-hidden bg-white py-[110px] max-[640px]:py-16">
    @if($leadBackgroundUrl)
        <img class="pointer-events-none absolute inset-0 h-full w-full scale-150 object-cover opacity-20" src="{{ $leadBackgroundUrl }}" alt="" aria-hidden="true" loading="lazy">
    @endif
    <div class="vmta-specialty-shell {{ $shellClass }} relative grid grid-cols-[minmax(0,5fr)_minmax(320px,7fr)] items-center gap-[clamp(2rem,7vw,7rem)] max-[850px]:grid-cols-1">
        <div>
            <h2 class="{{ $headingClass }}">
                {{ $translation->lead_h2_line1 ?: __('catalog::public.specialties.lead.submit_default') }}
                @if($translation->lead_h2_line2)<span>{{ $translation->lead_h2_line2 }}</span>@endif
            </h2>
            @if($translation->lead_subtitle)<p class="mt-4 text-base italic text-[#4a4a4a]">{{ $translation->lead_subtitle }}</p>@endif
            @if($translation->lead_body_html)
                <div class="vmta-specialty-copy {{ $copyClass }} text-base">{!! clean($translation->lead_body_html) !!}</div>
            @endif
        </div>

        <form class="grid gap-4" method="POST" action="{{ $leadAction }}">
            @csrf

            <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                <label>Website (do not fill)
                    <input type="text" name="hp_field" tabindex="-1" autocomplete="off" value="">
                </label>
            </div>

            @if(session('specialty_lead_success'))
                <div class="rounded-md bg-emerald-50 px-4 py-3.5 text-[0.95rem] text-emerald-700">{{ session('specialty_lead_success') }}</div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-rose-50 px-4 py-3.5 text-[0.95rem] text-rose-700">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 max-[640px]:grid-cols-1">
                <label class="sr-only" for="lead-name">{{ __('catalog::public.specialties.lead.name') }}</label>
                <input class="{{ $inputClass }}" id="lead-name" type="text" name="name" required maxlength="120"
                       value="{{ old('name') }}" placeholder="{{ __('catalog::public.specialties.lead.name') }}">
                <label class="sr-only" for="lead-email">{{ __('catalog::public.specialties.lead.email') }}</label>
                <input class="{{ $inputClass }}" id="lead-email" type="email" name="email" maxlength="190"
                       value="{{ old('email') }}" placeholder="{{ __('catalog::public.specialties.lead.email') }}">
            </div>

            <label class="sr-only" for="lead-phone">{{ __('catalog::public.specialties.lead.phone') }}</label>
            <input class="{{ $inputClass }}" id="lead-phone" type="tel" name="phone" required maxlength="30"
                   value="{{ old('phone') }}" placeholder="{{ __('catalog::public.specialties.lead.phone') }}">

            <label class="sr-only" for="lead-demand">{{ __('catalog::public.specialties.lead.demand') }}</label>
            <input class="{{ $inputClass }}" id="lead-demand" type="text" name="demand" maxlength="190"
                   value="{{ old('demand') }}" placeholder="{{ $translation->lead_demand_placeholder }}">

            <label class="sr-only" for="lead-message">{{ __('catalog::public.specialties.lead.message') }}</label>
            <textarea class="{{ $inputClass }} min-h-[10.5rem] resize-y" id="lead-message" name="message" rows="5" maxlength="2000"
                      placeholder="{{ __('catalog::public.specialties.lead.message') }}">{{ old('message') }}</textarea>

            <input type="hidden" name="consent" value="1">

            <button class="min-h-14 w-full justify-self-start rounded-md bg-vmta-teal px-8 font-bold uppercase tracking-[0.03em] text-white sm:w-auto sm:min-w-[17rem]" type="submit">
                {{ $translation->lead_submit_label ?: __('catalog::public.specialties.lead.submit_default') }}
            </button>
        </form>
    </div>
</section>
