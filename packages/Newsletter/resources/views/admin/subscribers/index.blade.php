@extends('core::layouts.admin')

@section('title', __('newsletter::newsletter.admin.heading'))
@section('page-title', __('newsletter::newsletter.admin.heading'))

@section('content')
<div class="mb-4 flex justify-end">
    @permission('newsletter.export')
        <a href="{{ route('admin.newsletter.export', request()->only('status', 'locale', 'search')) }}"
           class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center gap-2">
            <span class="material-symbols-rounded text-base">download</span>
            {{ __('newsletter::newsletter.admin.export_button') }}
        </a>
    @endpermission
</div>

@if(session('status'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3">
        {{ session('status') }}
    </div>
@endif

{!! $table !!}
@endsection
