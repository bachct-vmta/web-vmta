@extends('site::layouts.public')

@section('content')
<div id="content" role="main">
    @include('inquiry::public.partials.contact-hero')

    <section class="relative overflow-hidden py-[56px] md:py-[80px]">
        <div class="absolute inset-0">
            <img src="{{ asset('images/contact/forms-bg.png') }}"
                 class="h-full w-full object-cover object-center opacity-25"
                 loading="lazy" decoding="async" alt="">
        </div>
        <div class="absolute inset-0 bg-white/55"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 gap-10 md:gap-0 lg:grid-cols-2">
                @include('inquiry::public.partials.contact-patient-form')
                @include('inquiry::public.partials.contact-partner-form')
            </div>
        </div>
    </section>

    @include('inquiry::public.partials.contact-info')
</div>
@endsection
