@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $servicesRoute = 'site.'.$locale.'.catalog.services.show';
    $toursRoute = 'site.'.$locale.'.catalog.tours.show';
@endphp
<article class="max-w-4xl mx-auto px-4 py-12">
    <header class="mb-6">
        @if($combo->discount_percent)
            <span class="inline-block text-sm font-medium bg-red-100 text-red-700 px-3 py-1 rounded mb-3">{{ __('catalog::public.detail.discount') }} {{ (int) $combo->discount_percent }}%</span>
        @endif
        <h1 class="text-3xl font-semibold">{{ $translation->title }}</h1>
        @if($combo->price_from)
            <p class="mt-3 text-lg font-medium text-blue-700">{{ __('catalog::public.detail.price_from') }} {{ number_format((float) $combo->price_from, 0) }} {{ $combo->currency }}</p>
        @endif
    </header>

    @if($translation->excerpt)
        <p class="text-slate-700 mb-6">{{ $translation->excerpt }}</p>
    @endif

    @if($translation->body)
        {{-- Purified via mews/purifier — strips <script>, on* handlers, javascript: URLs. --}}
        <div class="prose max-w-none mb-8">{!! clean($translation->body) !!}</div>
    @endif

    @if($combo->services->isNotEmpty())
        <section class="mb-8">
            <h2 class="text-xl font-medium mb-3">{{ __('catalog::public.detail.includes_services') }}</h2>
            <ul class="list-disc pl-6 space-y-1 text-sm">
                @foreach($combo->services as $svc)
                    @php $tr = $svc->translations->firstWhere('locale', $locale) ?? $svc->translations->first(); @endphp
                    <li>
                        <a href="{{ route($servicesRoute, ['slug' => $tr?->slug]) }}" class="text-blue-600 hover:underline">{{ $tr?->title }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($combo->tourPackages->isNotEmpty())
        <section class="mb-8">
            <h2 class="text-xl font-medium mb-3">{{ __('catalog::public.detail.includes_tours') }}</h2>
            <ul class="list-disc pl-6 space-y-1 text-sm">
                @foreach($combo->tourPackages as $tp)
                    @php $tr = $tp->translations->firstWhere('locale', $locale) ?? $tp->translations->first(); @endphp
                    <li>
                        <a href="{{ route($toursRoute, ['slug' => $tr?->slug]) }}" class="text-blue-600 hover:underline">{{ $tr?->title }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="mt-8">
        @include('catalog::partials.cta-button', ['url' => $combo->cta_app_url, 'label' => __('catalog::public.cta.book_now')])
    </div>

    <div class="mt-10">
        @include('inquiry::public.quick-inquiry-form', ['refType' => 'combo', 'refId' => $combo->id])
    </div>
</article>
@endsection
