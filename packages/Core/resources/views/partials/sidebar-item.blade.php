{{-- Sidebar Item with Nested Menu Support --}}
@php
    /** @var \Packages\Core\Src\Services\SidebarItem $item */
    $hasChildren = $item->hasChildren();
    $isActive = $item->isActive();
    $isAnyChildActive = $item->isAnyChildActive();
    $isOpen = $isActive || $isAnyChildActive;
@endphp

@if($hasChildren)
    {{-- Parent item with children --}}
    <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }">
        <button @click="open = !open" 
                type="button"
                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors
                       {{ $isAnyChildActive 
                           ? 'bg-primary/10 text-primary' 
                           : 'text-text-muted dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-text-main dark:hover:text-white' }}">
            <div class="flex items-center gap-3">
                <span class="material-symbols-rounded flex-shrink-0">{{ $item->materialIcon ?? 'folder' }}</span>
                <span x-show="sidebarOpen" class="text-sm font-medium">{{ $item->name }}</span>
            </div>
            <span x-show="sidebarOpen" 
                  class="material-symbols-rounded text-[18px] transition-transform duration-200"
                  :class="open ? 'rotate-180' : ''">expand_more</span>
        </button>
        
        {{-- Children submenu --}}
        <div x-show="open && sidebarOpen" 
             x-collapse
             x-cloak
             class="ml-4 pl-3 border-l border-gray-200 dark:border-slate-700 mt-1 space-y-1">
            @foreach($item->children as $child)
                @if($child->hasChildren())
                    @include('core::partials.sidebar-item', ['item' => $child])
                @else
                    <a href="{{ $child->route ? route($child->route) : '#' }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                              {{ $child->isActive() 
                                  ? 'bg-primary/10 text-primary' 
                                  : 'text-text-muted dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-text-main dark:hover:text-white' }}">
                        <span class="material-symbols-rounded flex-shrink-0 text-[20px]">{{ $child->materialIcon ?? 'circle' }}</span>
                        <span class="text-sm font-medium">{{ $child->name }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@else
    {{-- Simple item without children --}}
    <a href="{{ $item->route ? route($item->route) : '#' }}" 
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors
              {{ $isActive 
                  ? 'bg-primary/10 text-primary' 
                  : 'text-text-muted dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-text-main dark:hover:text-white' }}">
        <span class="material-symbols-rounded flex-shrink-0">{{ $item->materialIcon ?? 'circle' }}</span>
        <span x-show="sidebarOpen" class="text-sm font-medium">{{ $item->name }}</span>
    </a>
@endif
