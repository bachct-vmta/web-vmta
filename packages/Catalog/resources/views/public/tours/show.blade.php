@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $ptr = $tour->partner?->translations->firstWhere('locale', $locale) ?? $tour->partner?->translations->first();
@endphp
<article class="max-w-4xl mx-auto px-4 py-12">
    <header class="mb-6">
        <h1 class="text-3xl font-semibold">{{ $translation->title }}</h1>
        @if($ptr?->name)
            <p class="mt-2 text-sm text-slate-500">{{ __('catalog::public.detail.partner') }}: {{ $ptr->name }}</p>
        @endif
        <div class="mt-3 flex flex-wrap gap-4 text-sm">
            @if($tour->duration_days)
                <span class="text-slate-700"><strong>{{ __('catalog::public.detail.duration') }}:</strong> {{ $tour->duration_days }} {{ __('catalog::public.detail.days') }}</span>
            @endif
            @if($tour->price_from)
                <span class="font-medium text-blue-700">{{ __('catalog::public.detail.price_from') }} {{ number_format((float) $tour->price_from, 0) }} {{ $tour->currency }}</span>
            @endif
        </div>
    </header>

    @if($translation->excerpt)
        <p class="text-slate-700 mb-6">{{ $translation->excerpt }}</p>
    @endif

    @if($translation->body)
        {{-- Purified via mews/purifier — strips <script>, on* handlers, javascript: URLs. --}}
        <div class="prose max-w-none mb-8">{!! clean($translation->body) !!}</div>
    @endif

    @if($translation->itinerary)
        <section class="mb-8">
            <h2 class="text-xl font-medium mb-3">{{ __('catalog::public.detail.itinerary') }}</h2>
            <pre class="whitespace-pre-wrap text-sm text-slate-700 font-sans bg-slate-50 rounded p-4">{{ $translation->itinerary }}</pre>
        </section>
    @endif

    @if($tour->destinations->isNotEmpty())
        <section class="mb-8 text-sm">
            <h2 class="font-medium mb-2">{{ __('catalog::public.detail.destinations') }}</h2>
            <ul class="flex flex-wrap gap-2">
                @foreach($tour->destinations as $d)
                    @php $tr = $d->translations->firstWhere('locale', $locale) ?? $d->translations->first(); @endphp
                    <li class="rounded bg-slate-100 px-3 py-1">{{ $tr?->name }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="mt-8">
        @include('catalog::partials.cta-button', ['url' => $tour->cta_app_url, 'label' => __('catalog::public.cta.book_now')])
    </div>

    <div class="mt-10">
        @include('inquiry::public.quick-inquiry-form', ['refType' => 'tour_package', 'refId' => $tour->id])
    </div>
</article>
@endsection
