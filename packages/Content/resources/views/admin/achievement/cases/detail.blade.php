@extends('core::layouts.admin')

@php
    $action = route(admin_route_name('achievement.cases.detail.update'), $case);
    $title = __('content::achievement.cases.form.detail_page_title');
    $locales = ['vi', 'en'];

    $existing = [];
    foreach ($locales as $loc) {
        $tr = $case->translations?->firstWhere('locale', $loc);
        $existing[$loc] = [
            'detail_content' => old("translations.{$loc}.detail_content", $tr?->detail_content ?? []),
        ];
    }
@endphp

@section('title', $title)

@section('content')
<div class="p-6 max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $case->translations?->firstWhere('locale', 'vi')?->title ?? $case->slug }}
                <span class="text-slate-400 font-mono text-xs">({{ $case->slug }})</span>
            </p>
        </div>
        <a href="{{ route(admin_route_name('achievement.cases.edit'), $case) }}"
           class="text-sm text-slate-600 hover:underline">{{ __('content::achievement.cases.form.back_to_card') }}</a>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded bg-emerald-50 border border-emerald-200 p-3 text-emerald-800 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6 bg-white border border-slate-200 rounded p-5">
        @csrf
        @method('PUT')

        <div x-data="{ tab: 'vi' }">
            <nav class="flex gap-2 border-b border-slate-200 mb-4">
                @foreach($locales as $loc)
                    <button type="button"
                            @click="tab = '{{ $loc }}'"
                            :class="tab === '{{ $loc }}' ? 'border-blue-600 text-blue-700 font-semibold' : 'border-transparent text-slate-600'"
                            class="px-3 py-2 text-sm border-b-2 hover:text-slate-900">
                        {{ strtoupper($loc) }}
                    </button>
                @endforeach
            </nav>

            @foreach($locales as $loc)
                <div x-show="tab === '{{ $loc }}'" x-cloak>
                    @include('content::admin.achievement.cases._detail-fields', [
                        'loc' => $loc,
                        'detail' => is_array($existing[$loc]['detail_content']) ? $existing[$loc]['detail_content'] : [],
                    ])
                </div>
            @endforeach
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
            <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white text-sm font-semibold hover:bg-blue-700">
                {{ __('content::achievement.cases.form.save') }}
            </button>
        </div>
    </form>
</div>
@endsection
