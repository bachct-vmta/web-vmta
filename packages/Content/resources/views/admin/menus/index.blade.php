@extends('core::layouts.admin')

@section('title', __('content::content.menu.index'))

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ __('content::content.menu.index') }}</h1>
        @if(auth()->user()?->hasPermission('content.create'))
            <a href="{{ route(admin_route_name('menus.create')) }}" class="rounded bg-blue-600 text-white px-4 py-2 text-sm hover:bg-blue-700">+ {{ __('content::content.menu.create') }}</a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 p-3 text-green-800">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto rounded border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">{{ __('content::content.fields.name') }}</th>
                    <th class="px-4 py-2">{{ __('content::content.menu.location') }}</th>
                    <th class="px-4 py-2">{{ __('content::content.fields.is_active') }}</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $row)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-500">{{ $row->id }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route(admin_route_name('menus.edit'), $row->id) }}" class="text-blue-600 hover:underline">{{ $row->name }}</a>
                        </td>
                        <td class="px-4 py-2 text-slate-600 text-xs">
                            {{ \Illuminate\Support\Arr::get(\Packages\Content\Src\Models\Menu::LOCATIONS, $row->location)
                                ? __('content::content.menu.locations.'.\Packages\Content\Src\Models\Menu::LOCATIONS[$row->location])
                                : $row->location }}
                        </td>
                        <td class="px-4 py-2">{{ $row->is_active ? '✓' : '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            @if(auth()->user()?->hasPermission('content.edit'))
                                <a href="{{ route(admin_route_name('menus.edit'), $row->id) }}" class="text-blue-600 text-xs mr-2">{{ __('content::content.actions.edit') }}</a>
                            @endif
                            @if(auth()->user()?->hasPermission('content.delete'))
                                <form method="POST" action="{{ route(admin_route_name('menus.destroy'), $row->id) }}" class="inline" onsubmit="return confirm('{{ __('content::content.actions.delete_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs">{{ __('content::content.actions.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('content::content.menu.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $menus->links() }}</div>
</div>
@endsection
