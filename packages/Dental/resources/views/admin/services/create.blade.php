@extends('core::layouts.admin')

@section('title', __('dental::dental.service.create'))
@section('page-title', __('dental::dental.service.create'))

@section('content')
<div class="max-w-5xl mx-auto">
    @include('dental::admin._errors')

    <form method="POST" action="{{ route(admin_route_name('dental_services.store')) }}" class="space-y-6">
        @csrf
        @include('dental::admin.services._fields')

        @include('dental::admin._form-actions', [
            'submitLabel' => __('dental::dental.actions.create'),
            'cancelRoute' => route(admin_route_name('dental_services.index')),
        ])
    </form>
</div>
@endsection
