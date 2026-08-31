@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $dtr = $partner->destination?->translations->firstWhere('locale', $locale) ?? $partner->destination?->translations->first();
@endphp
<article class="max-w-4xl mx-auto px-4 py-12">
    <header class="mb-6">
        <span class="text-xs uppercase tracking-wide text-slate-500">{{ __('catalog::catalog.types.'.$partner->type) }}</span>
        <h1 class="mt-1 text-3xl font-semibold">{{ $translation->name }}</h1>
        @if($dtr?->name)
            <p class="mt-2 text-sm text-slate-500">📍 {{ $dtr->name }}</p>
        @endif
    </header>

    <div class="grid grid-cols-1 md:grid-cols-[1fr_280px] gap-8">
        <div>
            @if($translation->short_description)
                <p class="text-slate-700 mb-6">{{ $translation->short_description }}</p>
            @endif

            @if($translation->description)
                {{-- Purified via mews/purifier — strips <script>, on* handlers, javascript: URLs. --}}
                <div class="prose max-w-none mb-8">{!! clean($translation->description) !!}</div>
            @endif

            @if($partner->specialties->isNotEmpty())
                <section class="mb-8 text-sm">
                    <h2 class="font-medium mb-2">{{ __('catalog::public.detail.specialties') }}</h2>
                    <ul class="flex flex-wrap gap-2">
                        @foreach($partner->specialties as $sp)
                            @php $tr = $sp->translations->firstWhere('locale', $locale) ?? $sp->translations->first(); @endphp
                            <li class="rounded bg-slate-100 px-3 py-1">{{ $tr?->name }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="rounded border border-slate-200 bg-slate-50 p-4 text-sm space-y-3">
            @if($translation->address)
                <div>
                    <p class="font-medium">{{ __('catalog::public.detail.address') }}</p>
                    <p class="text-slate-600">{{ $translation->address }}</p>
                </div>
            @endif
            @if($partner->phone)
                <div>
                    <p class="font-medium">{{ __('catalog::public.detail.phone') }}</p>
                    <p class="text-slate-600">{{ $partner->phone }}</p>
                </div>
            @endif
            @if($partner->email)
                <div>
                    <p class="font-medium">{{ __('catalog::public.detail.email') }}</p>
                    <p class="text-slate-600">{{ $partner->email }}</p>
                </div>
            @endif
            @if($partner->website)
                <a href="{{ $partner->website }}" target="_blank" rel="noopener noreferrer" class="inline-block rounded bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm">{{ __('catalog::public.partners.view_website') }} →</a>
            @endif
        </aside>
    </div>
</article>
@endsection
