@php $hero = $heroSection ?? null; @endphp
{{-- Page banner matches the public news index hero. --}}
<section class="relative h-[300px] overflow-hidden bg-white">
    <img src="{{ asset('images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png') }}"
         class="absolute inset-0 h-full w-full object-cover opacity-20"
         fetchpriority="high" decoding="async" alt="">
    <div class="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 py-4">
        <h1 class="font-sharp-bo text-[46px] font-bold uppercase leading-none text-vmta-teal md:text-[50px]">
            {{ $hero?->title ?: __('inquiry::inquiry.page_title') }}
        </h1>
        <p class="mt-2 text-[16px] text-vmta-teal">
            <a href="{{ route('site.' . app()->getLocale() . '.content.home') }}"
               class="transition-colors hover:text-[#0b7f7c]">
                {{ __('inquiry::inquiry.breadcrumb_home') }}
            </a>
            / {{ __('inquiry::inquiry.breadcrumb_contact') }}
        </p>
    </div>
</section>

<section class="py-[40px] md:py-[60px] text-center bg-white">
    <div class="max-w-3xl mx-auto px-4 md:px-6">
        <h2 class="font-sharp-bo text-2xl md:text-3xl uppercase font-bold text-[#0b7f7c]">
            {{ $hero?->subtitle ?: __('inquiry::inquiry.contact_heading') }}
        </h2>
        <p class="font-utm-helve text-slate-600 mt-3">
            {{ $hero?->body ?: __('inquiry::inquiry.contact_subheading') }}
        </p>
        <p class="font-utm-helve text-slate-500 mt-1 text-sm">
            {{ $hero?->extra ?: __('inquiry::inquiry.contact_subheading2') }}
        </p>
    </div>
</section>
