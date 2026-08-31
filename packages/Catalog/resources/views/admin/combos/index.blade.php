@extends('core::layouts.admin')

@section('title', __('catalog::catalog.combo.index'))
@section('page-title', __('catalog::catalog.combo.index'))

@section('content')
{{-- Header Actions --}}
<div class="mb-4 flex justify-end">
    @permission('catalog.create')
    <a href="{{ route(admin_route_name('combos.create')) }}"
       class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('catalog::catalog.combo.create') }}
    </a>
    @endpermission
</div>

{{-- Table Builder Output --}}
{!! $table !!}
@endsection
