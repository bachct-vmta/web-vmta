@extends('core::layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Page Heading --}}
<div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-text-main dark:text-white tracking-tight">Dashboard Overview</h2>
        <p class="text-text-muted dark:text-slate-400 text-sm mt-1">
            Xin chào {{ auth()->user()->name }}, đây là tổng quan nhanh về hệ thống.
        </p>
    </div>
</div>

@php
    $widgets = app(\Packages\Core\Src\Services\WidgetService::class)->getVisibleWidgets();
@endphp

@if(count($widgets) > 0)
{{-- Stats Cards Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @foreach($widgets as $widget)
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700 flex flex-col justify-between h-32 hover:-translate-y-1 hover:shadow-soft-xl transition-all duration-300 group">
        <div class="flex justify-between items-start">
            <div class="flex flex-col">
                <span class="text-text-muted dark:text-slate-400 text-sm font-medium">{{ $widget->name }}</span>
                <span class="text-2xl font-bold text-text-main dark:text-white mt-1">{{ $widget->getValue() }}</span>
            </div>
            <div class="p-2.5 {{ $widget->bgColor ?? 'bg-primary/10' }} rounded-lg group-hover:scale-110 transition-transform duration-300">
                @if(isset($widget->materialIcon))
                    <span class="material-symbols-rounded {{ $widget->iconColor ?? 'text-primary' }}">{{ $widget->materialIcon }}</span>
                @else
                    <span class="{{ $widget->iconColor ?? 'text-primary' }}">{!! $widget->icon !!}</span>
                @endif
            </div>
        </div>
        
        {{-- Optional: Trend Indicator --}}
        @if(isset($widget->trend))
        <div class="flex items-center gap-1 text-xs font-medium {{ $widget->trendPositive ?? true ? 'text-emerald-600' : 'text-red-600' }}">
            <span class="material-symbols-rounded text-[16px]">{{ $widget->trendPositive ?? true ? 'trending_up' : 'trending_down' }}</span>
            <span>{{ $widget->trend }}</span>
        </div>
        @endif
    </div>
    @endforeach
</div>
@else
{{-- Empty State --}}
<div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-gray-100 dark:border-slate-700 p-12 text-center">
    <div class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-rounded text-3xl text-text-muted dark:text-slate-400">widgets</span>
    </div>
    <h3 class="text-lg font-semibold text-text-main dark:text-white mb-2">Chưa có widget nào</h3>
    <p class="text-text-muted dark:text-slate-400 text-sm max-w-md mx-auto">
        Các package khác có thể đăng ký widgets để hiển thị thống kê tại đây.
    </p>
</div>
@endif

{{-- Quick Links Section --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @permission('users.view')
    <a href="{{ route('admin.users.index') }}" 
       class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700 hover:border-primary/30 hover:shadow-soft-xl transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                <span class="material-symbols-rounded text-blue-600 dark:text-blue-400 text-2xl">group</span>
            </div>
            <div>
                <h3 class="font-semibold text-text-main dark:text-white group-hover:text-primary transition-colors">Quản lý người dùng</h3>
                <p class="text-sm text-text-muted dark:text-slate-400">Xem và quản lý tài khoản</p>
            </div>
        </div>
    </a>
    @endpermission
    
    @permission('roles.view')
    <a href="{{ route('admin.roles.index') }}" 
       class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700 hover:border-primary/30 hover:shadow-soft-xl transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl group-hover:bg-purple-100 dark:group-hover:bg-purple-900/30 transition-colors">
                <span class="material-symbols-rounded text-purple-600 dark:text-purple-400 text-2xl">shield_person</span>
            </div>
            <div>
                <h3 class="font-semibold text-text-main dark:text-white group-hover:text-primary transition-colors">Quản lý vai trò</h3>
                <p class="text-sm text-text-muted dark:text-slate-400">Phân quyền người dùng</p>
            </div>
        </div>
    </a>
    @endpermission
    
    @permission('settings.view')
    <a href="#" 
       class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-soft border border-gray-50 dark:border-slate-700 hover:border-primary/30 hover:shadow-soft-xl transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-xl group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
                <span class="material-symbols-rounded text-slate-600 dark:text-slate-300 text-2xl">settings</span>
            </div>
            <div>
                <h3 class="font-semibold text-text-main dark:text-white group-hover:text-primary transition-colors">Cài đặt hệ thống</h3>
                <p class="text-sm text-text-muted dark:text-slate-400">Cấu hình ứng dụng</p>
            </div>
        </div>
    </a>
    @endpermission
</div>
@endsection
