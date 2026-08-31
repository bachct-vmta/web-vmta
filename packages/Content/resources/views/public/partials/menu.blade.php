{{--
    Public menu renderer — usage: @include('content::public.partials.menu', ['location' => 'main_navigation'])
    Resolves the Menu by location via MenuService (cached), then recurses through items.

    Vars in:
      - $location  string  Menu.location (one of Packages\Content\Src\Models\Menu::LOCATIONS keys —
                           'main_navigation', 'page_sidebar', 'footer_1_navigation', 'footer_2_navigation')
      - $cssClass  ?string Optional <nav> wrapper class override
      - $mode      ?string 'desktop' (dropdown nổi) | 'mobile' (accordion)
--}}
@php
    /** @var \Packages\Content\Src\Services\MenuService $menuService */
    $menuService = app(\Packages\Content\Src\Services\MenuService::class);
    // findByLocation eager-loads rootItems with translations + children.translations,
    // sorted by sort_order — no further N+1 hits in the recursive render below.
    $menu = $menuService->getMenu($location, app()->getLocale());
    $rootItems = $menu?->rootItems ?? collect();
@endphp

@if($menu && $rootItems->isNotEmpty())
    <nav class="{{ $cssClass ?? 'menu menu-'.$location }}" aria-label="{{ $menu->name }}">
        <ul class="{{ $ulClass ?? 'flex flex-wrap gap-4' }}">
            @foreach($rootItems as $item)
                @include('content::public.partials.menu-item', ['item' => $item, 'mode' => $mode ?? 'desktop', 'depth' => 0])
            @endforeach
        </ul>
    </nav>
@endif
