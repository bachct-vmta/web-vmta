@php
    /** @var \Packages\Content\Src\Models\MenuItem $item */
    $label = $item->translations->firstWhere('locale', app()->getLocale())?->label
        ?? $item->translations->first()?->label
        ?? '#'.$item->id;
    $pad = $depth * 24;
@endphp
<li class="flex items-center justify-between px-4 py-2 hover:bg-slate-50" style="padding-left: {{ 16 + $pad }}px">
    <div class="flex items-center gap-3">
        @if($depth > 0)
            <span class="text-slate-300">↳</span>
        @endif
        <span class="font-medium">{{ $label }}</span>
        <span class="text-xs text-slate-400 font-mono">
            @if($item->link_type === 'url')
                {{ $item->translations->firstWhere('locale', app()->getLocale())?->url ?? '—' }}
            @else
                {{ $item->target_type }}#{{ $item->target_id }}
            @endif
        </span>
        @unless($item->is_active)
            <span class="text-xs rounded bg-slate-100 text-slate-500 px-1.5 py-0.5">inactive</span>
        @endunless
    </div>
    <div class="flex items-center gap-2">
        @if(auth()->user()?->hasPermission('content.edit'))
            <a href="{{ route(admin_route_name('menus.items.edit'), ['menu' => $menu->id, 'item' => $item->id]) }}"
               class="text-blue-600 text-xs">{{ __('content::content.actions.edit') }}</a>
        @endif
        @if(auth()->user()?->hasPermission('content.delete'))
            <form method="POST"
                  action="{{ route(admin_route_name('menus.items.destroy'), ['menu' => $menu->id, 'item' => $item->id]) }}"
                  class="inline"
                  onsubmit="return confirm('{{ __('content::content.actions.delete_confirm') }}')">
                @csrf @method('DELETE')
                <button class="text-red-600 text-xs">{{ __('content::content.actions.delete') }}</button>
            </form>
        @endif
    </div>
</li>
