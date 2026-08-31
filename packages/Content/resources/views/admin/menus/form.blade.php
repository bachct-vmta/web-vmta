@extends('core::layouts.admin')

@php
    $action = $mode === 'create'
        ? route(admin_route_name('menus.store'))
        : route(admin_route_name('menus.update'), $menu->id);
    $title = $mode === 'create' ? __('content::content.menu.create') : __('content::content.menu.edit');
@endphp

@section('title', $title)

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold mb-6 text-text-main dark:text-white">{{ $title }}</h1>

    <form method="POST" action="{{ $action }}" class="space-y-6">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.fields.name') }} *</label>
                <input name="name" required maxlength="120" value="{{ old('name', $menu->name) }}" class="w-full rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu.location') }} *</label>
                <select name="location" required
                        class="w-full rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm">
                    @php $currentLocation = old('location', $menu->location); @endphp
                    <option value="" disabled @selected(empty($currentLocation))>—</option>
                    @foreach(\Packages\Content\Src\Models\Menu::LOCATIONS as $slug => $key)
                        <option value="{{ $slug }}" @selected($currentLocation === $slug)>
                            {{ __('content::content.menu.locations.'.$key) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center mt-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $menu->is_active))>
                    {{ __('content::content.fields.is_active') }}
                </label>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-md bg-primary text-white px-6 py-2 text-sm hover:bg-primary-700">{{ __('content::content.actions.save') }}</button>
            <a href="{{ route(admin_route_name('menus.index')) }}" class="rounded-md bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-6 py-2 text-sm">{{ __('content::content.actions.cancel') }}</a>
        </div>
    </form>

    {{-- Menu items panel (edit only — needs a persisted menu id). --}}
    @if($mode === 'edit')
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left: nestable tree --}}
            <div class="lg:col-span-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-surface-light dark:bg-surface-dark">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h2 class="text-lg font-medium text-text-main dark:text-white">{{ __('content::content.menu.items_heading') }}</h2>
                    <div class="flex items-center gap-3 text-xs text-slate-400 dark:text-slate-500" x-data="{ count: 0 }" x-init="window.addEventListener('menu-builder-inflight', e => count = e.detail.count)">
                        <span x-show="count > 0" x-cloak>⟳ {{ __('content::content.menu.reorder.saving') }}</span>
                    </div>
                </div>

                @if($rootItems->isEmpty())
                    <p class="px-4 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">{{ __('content::content.menu_item.empty') }}</p>
                @else
                    <div class="p-4">
                        <div class="dd menu-builder-tree"
                             data-menu-id="{{ $menu->id }}"
                             data-reorder-url="{{ route(admin_route_name('menus.reorder'), $menu->id) }}"
                             data-patch-url-template="{{ route(admin_route_name('menu-items.update'), ['item' => '__ID__']) }}"
                             data-delete-url-template="{{ route(admin_route_name('menu-items.destroy-ajax'), ['item' => '__ID__']) }}">
                            <ol class="dd-list">
                                @foreach($rootItems as $root)
                                    @include('content::admin.menus._dd-item', ['item' => $root])
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right: add-link panel --}}
            <aside class="lg:col-span-4">
                @include('content::admin.menus._add-link-panel', ['menu' => $menu])
            </aside>
        </div>

        @include('content::admin.menus._toast')
        @include('content::admin.menus._confirm-modal')
        @include('content::admin.menus._init-nestable')
    @endif
</div>
@endsection
