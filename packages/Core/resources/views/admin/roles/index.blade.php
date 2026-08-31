@extends('core::layouts.admin')

@section('title', 'Quản lý vai trò')
@section('page-title', 'Quản lý vai trò')

@section('content')
{{-- Header Actions (Add New Button) --}}
<div class="mb-4 flex justify-end">
    @permission('roles.create')
    <a href="{{ route('admin.roles.create') }}" 
       class="bg-primary hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl font-medium shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 active:scale-95">
        <span class="material-symbols-rounded text-[20px]">add</span>
        Thêm mới
    </a>
    @endpermission
</div>

{{-- Table Builder Output --}}
{!! $table !!}
@endsection
