@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $ptr = $service->partner?->translations->firstWhere('locale', $locale) ?? $service->partner?->translations->first();
@endphp
<article class="max-w-4xl mx-auto px-4 py-12">
    <header class="mb-6">
        <h1 class="text-3xl font-semibold">{{ $translation->title }}</h1>
        @if($ptr?->name)
            <p class="mt-2 text-sm text-slate-500">{{ __('catalog::public.detail.partner') }}: {{ $ptr->name }}</p>
        @endif
        @if($service->price_from)
            <p class="mt-3 text-lg font-medium text-blue-700">{{ __('catalog::public.detail.price_from') }} {{ number_format((float) $service->price_from, 0) }} {{ $service->currency }}</p>
        @endif
    </header>

    @if($translation->excerpt)
        <p class="text-slate-700 mb-6">{{ $translation->excerpt }}</p>
    @endif

    @if($translation->body)
        {{-- Purified via mews/purifier — strips <script>, on* handlers, javascript: URLs. --}}
        <div class="prose max-w-none mb-8">{!! clean($translation->body) !!}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8 text-sm">
        @if($service->specialties->isNotEmpty())
            <div>
                <h2 class="font-medium mb-2">{{ __('catalog::public.detail.specialties') }}</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach($service->specialties as $sp)
                        @php $tr = $sp->translations->firstWhere('locale', $locale) ?? $sp->translations->first(); @endphp
                        <li class="rounded bg-slate-100 px-3 py-1">{{ $tr?->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($service->destinations->isNotEmpty())
            <div>
                <h2 class="font-medium mb-2">{{ __('catalog::public.detail.destinations') }}</h2>
                <ul class="flex flex-wrap gap-2">
                    @foreach($service->destinations as $d)
                        @php $tr = $d->translations->firstWhere('locale', $locale) ?? $d->translations->first(); @endphp
                        <li class="rounded bg-slate-100 px-3 py-1">{{ $tr?->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="mt-8">
        @include('catalog::partials.cta-button', ['url' => $service->cta_app_url, 'label' => __('catalog::public.cta.book_now')])
    </div>

    <div class="mt-10">
        @include('inquiry::public.quick-inquiry-form', ['refType' => 'service', 'refId' => $service->id])
    </div>
</article>
@endsection
