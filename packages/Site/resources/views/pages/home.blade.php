@extends('site::layouts.public')

@section('content')
    <section class="max-w-6xl mx-auto px-4 py-16">
        <h1 class="text-3xl md:text-4xl font-semibold text-slate-900">
            {{ __('Vietnam Medical Tourism Alliance') }}
        </h1>
        <p class="mt-4 text-slate-600 max-w-2xl">
            {{ __('Phase 1 skeleton — content & catalog will land in upcoming phases.') }}
        </p>
        <div class="mt-6 text-sm text-slate-500">
            {{ __('Current locale') }}: <code class="px-2 py-0.5 bg-slate-100 rounded">{{ app()->getLocale() }}</code>
        </div>
    </section>
@endsection
