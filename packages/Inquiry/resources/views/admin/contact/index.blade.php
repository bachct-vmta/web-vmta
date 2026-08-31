@extends('core::layouts.admin')

@php
    $activeTab = old('_active_tab', session('_old_input._active_tab', 'hero'));
@endphp

@section('title', __('inquiry::inquiry.admin.contact.heading'))

@section('content')
<div class="p-6 max-w-6xl mx-auto" x-data="{ tab: '{{ $activeTab }}' }">
    <h1 class="text-2xl font-semibold mb-4">{{ __('inquiry::inquiry.admin.contact.heading') }}</h1>

    @if(session('status'))
        <div class="mb-4 rounded bg-emerald-50 border border-emerald-200 p-3 text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <nav class="flex flex-wrap gap-2 border-b border-slate-200 mb-6" role="tablist">
        @foreach($positions as $p)
            <button type="button" role="tab"
                    :aria-selected="tab === '{{ $p->value }}'"
                    @click="tab = '{{ $p->value }}'"
                    :class="tab === '{{ $p->value }}' ? 'border-blue-600 text-blue-700 font-semibold' : 'border-transparent text-slate-600'"
                    class="px-3 py-2 text-sm border-b-2 hover:text-slate-900">
                {{ __('inquiry::inquiry.admin.contact.positions.' . $p->value) }}
            </button>
        @endforeach
    </nav>

    @foreach($positions as $p)
        @php $section = $sections->firstWhere('position', $p); @endphp
        <div x-show="tab === '{{ $p->value }}'" x-cloak>
            @include('inquiry::admin.contact._fieldset-' . $p->value, ['position' => $p, 'section' => $section])
        </div>
    @endforeach
</div>
@endsection
