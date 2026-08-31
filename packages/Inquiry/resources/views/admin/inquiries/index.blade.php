@extends('core::layouts.admin')

@section('title', __('inquiry::inquiry.admin.index_heading'))
@section('page-title', __('inquiry::inquiry.admin.index_heading'))

@section('content')
{{-- Header Actions: scope toggle (mine vs all) --}}
<div class="mb-4 flex justify-end">
    <div class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl ring-1 ring-slate-200 dark:ring-slate-700 text-sm">
        <a href="{{ request()->fullUrlWithQuery(['mine' => 0]) }}"
           class="px-4 py-1.5 rounded-lg font-medium transition-colors {{ $mine ? 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' : 'bg-white dark:bg-slate-900 text-primary shadow-sm' }}">
            {{ __('inquiry::inquiry.admin.all_filter') }}
        </a>
        <a href="{{ request()->fullUrlWithQuery(['mine' => 1]) }}"
           class="px-4 py-1.5 rounded-lg font-medium transition-colors {{ $mine ? 'bg-white dark:bg-slate-900 text-primary shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700' }}">
            {{ __('inquiry::inquiry.admin.mine_filter') }}
        </a>
    </div>
</div>

@if(session('status'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
        {{ session('status') }}
    </div>
@endif

{!! $table !!}

{{-- Persist `mine` across the Table's search/filter GET form (which otherwise drops it). --}}
<script>
    (function () {
        const params = new URLSearchParams(window.location.search);
        if (!params.has('mine')) return;
        document.querySelectorAll('.core-table-wrapper form').forEach(function (form) {
            const method = (form.getAttribute('method') || 'get').toUpperCase();
            if (method !== 'GET') return;
            if (form.querySelector('input[name="mine"]')) return;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'mine';
            hidden.value = params.get('mine');
            form.appendChild(hidden);
        });
    })();
</script>
@endsection
