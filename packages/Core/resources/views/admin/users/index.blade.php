@extends('core::layouts.admin')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')

@section('content')
{{-- Header Actions (Add New Button) --}}
<div class="mb-4 flex justify-end">
    @permission('users.create')
    <a href="{{ route('admin.users.create') }}" 
       class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Thêm mới
    </a>
    @endpermission
</div>

{{-- Table Builder Output --}}
{!! $table !!}
@endsection
