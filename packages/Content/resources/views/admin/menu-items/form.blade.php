@extends('core::layouts.admin')

@php
    $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
    $action = $mode === 'create'
        ? route(admin_route_name('menus.items.store'), ['menu' => $menu->id])
        : route(admin_route_name('menus.items.update'), ['menu' => $menu->id, 'item' => $item->id]);
    $title = $mode === 'create' ? __('content::content.menu_item.create') : __('content::content.menu_item.edit');
@endphp

@section('title', $title)

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-4">
        <a href="{{ route(admin_route_name('menus.edit'), $menu->id) }}" class="text-sm text-blue-600 hover:underline">← {{ $menu->name }}</a>
    </div>

    <h1 class="text-2xl font-semibold mb-6">{{ $title }}</h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-6">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="rounded border border-slate-200 bg-white p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.fields.parent') }}</label>
                <select name="parent_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">{{ __('content::content.fields.no_parent') }}</option>
                    @foreach($parentOptions as $p)
                        @php $plabel = $p->translations->firstWhere('locale', app()->getLocale())?->label ?? $p->translations->first()?->label ?? "#{$p->id}"; @endphp
                        <option value="{{ $p->id }}" @selected((int) old('parent_id', $item->parent_id) === $p->id)>{{ $plabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.link_type') }}</label>
                <select name="link_type" class="w-full rounded border-slate-300 text-sm">
                    <option value="url" @selected(old('link_type', $item->link_type) === 'url')>{{ __('content::content.menu_item.link_url') }}</option>
                    <option value="morph" @selected(old('link_type', $item->link_type) === 'morph')>{{ __('content::content.menu_item.link_morph') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.fields.sort_order') }}</label>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $item->sort_order) }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.target_type') }}</label>
                <input name="target_type" maxlength="50" value="{{ old('target_type', $item->target_type) }}" placeholder="page / post …" class="w-full rounded border-slate-300 text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.target_id') }}</label>
                <input name="target_id" type="number" min="1" value="{{ old('target_id', $item->target_id) }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.icon') }}</label>
                <input name="icon" maxlength="60" value="{{ old('icon', $item->icon) }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.css_class') }}</label>
                <input name="css_class" maxlength="255" value="{{ old('css_class', $item->css_class) }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div class="flex items-center mt-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="open_new_tab" value="0">
                    <input type="checkbox" name="open_new_tab" value="1" @checked(old('open_new_tab', $item->open_new_tab))>
                    {{ __('content::content.menu_item.open_new_tab') }}
                </label>
            </div>
            <div class="flex items-center mt-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))>
                    {{ __('content::content.fields.is_active') }}
                </label>
            </div>
        </div>

        @foreach($locales as $idx => $locale)
            @php $tr = $item->translations->firstWhere('locale', $locale); @endphp
            <div class="rounded border border-slate-200 bg-white p-4">
                <h2 class="text-lg font-medium mb-3">{{ __('content::content.tabs.translation', ['locale' => strtoupper($locale)]) }}</h2>
                <input type="hidden" name="translations[{{ $idx }}][locale]" value="{{ $locale }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.label') }} *</label>
                        <input name="translations[{{ $idx }}][label]" required maxlength="255" value="{{ old("translations.{$idx}.label", $tr?->label) }}" class="w-full rounded border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('content::content.menu_item.url') }}</label>
                        <input name="translations[{{ $idx }}][url]" maxlength="500" value="{{ old("translations.{$idx}.url", $tr?->url) }}" class="w-full rounded border-slate-300 text-sm">
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-blue-600 text-white px-6 py-2 text-sm hover:bg-blue-700">{{ __('content::content.actions.save') }}</button>
            <a href="{{ route(admin_route_name('menus.edit'), $menu->id) }}" class="rounded bg-slate-200 text-slate-700 px-6 py-2 text-sm">{{ __('content::content.actions.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
