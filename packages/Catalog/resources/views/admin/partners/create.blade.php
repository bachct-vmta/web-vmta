@extends('core::layouts.admin')

@section('title', __('catalog::catalog.partner.create'))
@section('page-title', __('catalog::catalog.partner.create'))

@section('content')
<div class="max-w-5xl mx-auto">
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route(admin_route_name('partners.store')) }}" class="space-y-6">
        @csrf
        @include('catalog::admin.partners._fields')

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg font-medium">
                {{ __('catalog::catalog.actions.create') }}
            </button>
            <a href="{{ route(admin_route_name('partners.index')) }}" class="text-gray-500 hover:text-gray-700">
                {{ __('catalog::catalog.actions.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
