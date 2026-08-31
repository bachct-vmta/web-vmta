@extends('core::layouts.admin')

@section('title', __('dental::dental.category.edit'))
@section('page-title', __('dental::dental.category.edit'))

@section('content')
<div class="max-w-5xl mx-auto">
    @include('dental::admin._errors')

    <form method="POST" action="{{ route(admin_route_name('dental_categories.update'), $category->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('dental::admin.categories._fields')

        @include('dental::admin._form-actions', [
            'submitLabel' => __('dental::dental.actions.update'),
            'cancelRoute' => route(admin_route_name('dental_categories.index')),
        ])
    </form>
</div>
@endsection
