@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $routeBase = 'site.'.$locale.'.catalog.partners';
@endphp
<section class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-semibold mb-8">{{ __('catalog::public.partners.heading') }}</h1>

    <form method="GET" class="mb-6 flex flex-wrap gap-3 text-sm">
        <select name="filter[type]" class="rounded border-slate-300">
            <option value="">{{ __('catalog::public.filters.type') }} — {{ __('catalog::public.filters.all') }}</option>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(request('filter.type') === $type)>{{ __('catalog::catalog.types.'.$type) }}</option>
            @endforeach
        </select>
        <select name="filter[destination]" class="rounded border-slate-300">
            <option value="">{{ __('catalog::public.filters.destination') }} — {{ __('catalog::public.filters.all') }}</option>
            @foreach($destinations as $d)
                @php $tr = $d->translations->firstWhere('locale', $locale) ?? $d->translations->first(); @endphp
                <option value="{{ $tr?->slug }}" @selected(request('filter.destination') === $tr?->slug)>{{ $tr?->name }}</option>
            @endforeach
        </select>
        <select name="filter[specialty]" class="rounded border-slate-300">
            <option value="">{{ __('catalog::public.filters.specialty') }} — {{ __('catalog::public.filters.all') }}</option>
            @foreach($specialties as $sp)
                @php $tr = $sp->translations->firstWhere('locale', $locale) ?? $sp->translations->first(); @endphp
                <option value="{{ $tr?->slug }}" @selected(request('filter.specialty') === $tr?->slug)>{{ $tr?->name }}</option>
            @endforeach
        </select>
        <button class="rounded bg-blue-600 hover:bg-blue-700 text-white px-4 py-2">{{ __('catalog::public.filters.apply') }}</button>
        <a href="{{ route($routeBase.'.index') }}" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2">{{ __('catalog::public.filters.clear') }}</a>
    </form>

    @if($partners->isEmpty())
        <p class="text-slate-500">{{ __('catalog::public.partners.empty') }}</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($partners as $p)
                @php
                    $tr = $p->translations->firstWhere('locale', $locale) ?? $p->translations->first();
                    $dtr = $p->destination?->translations->firstWhere('locale', $locale) ?? $p->destination?->translations->first();
                @endphp
                <article class="rounded border border-slate-200 p-4 hover:shadow transition">
                    <span class="text-xs uppercase tracking-wide text-slate-500">{{ __('catalog::catalog.types.'.$p->type) }}</span>
                    <h2 class="mt-1 text-lg font-medium">
                        <a href="{{ route($routeBase.'.show', ['slug' => $tr?->slug]) }}" class="hover:underline">{{ $tr?->name }}</a>
                    </h2>
                    @if($dtr?->name)
                        <p class="text-xs text-slate-500 mt-1">📍 {{ $dtr->name }}</p>
                    @endif
                    @if($tr?->short_description)
                        <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $tr->short_description }}</p>
                    @endif
                    @if($p->website)
                        <a href="{{ $p->website }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-block text-xs text-blue-600 hover:underline">{{ __('catalog::public.partners.view_website') }} →</a>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $partners->links() }}</div>
    @endif
</section>
@endsection
