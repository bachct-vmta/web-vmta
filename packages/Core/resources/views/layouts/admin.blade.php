<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Core CMS') }}</title>
    
    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Material Symbols --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    
    {{-- Alpine.js with Collapse plugin --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // New primary color matching design
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#464ee7',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                            DEFAULT: '#464ee7'
                        },
                        // Background colors
                        'background-light': '#f9fafb',
                        'background-dark': '#1d2839',
                        // Surface colors
                        'surface-light': '#ffffff',
                        'surface-dark': '#1e293b',
                        // Text colors
                        'text-main': '#212121',
                        'text-muted': '#757575',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'display': ['Inter', 'sans-serif']
                    },
                    borderRadius: {
                        DEFAULT: '0.375rem',
                        'lg': '0.5rem',
                        'xl': '0.75rem',
                        '2xl': '1rem',
                    },
                    boxShadow: {
                        'soft': '0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02)',
                        'soft-xl': '0 10px 15px -3px rgba(0, 0, 0, 0.03), 0 4px 6px -2px rgba(0, 0, 0, 0.02)',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        /* Material Symbols settings */
        .material-symbols-rounded,
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white min-h-screen font-sans antialiased" 
      x-data="{ sidebarOpen: true, darkMode: localStorage.getItem('theme') === 'dark' }" 
      :class="{ 'dark': darkMode }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))">
    
    <div class="flex h-screen w-full overflow-hidden">
        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" 
               class="flex-shrink-0 bg-surface-light dark:bg-surface-dark border-r border-gray-100 dark:border-slate-700 flex flex-col justify-between h-full transition-all duration-300 z-20">
            
            <div class="    overflow-auto">
                {{-- Logo Area — pulls from Settings (site.logo_media_id + site.name).
                     Falls back to icon + CoreCMS text when settings are empty. --}}
                @php
                    $logoMediaId = \Packages\Core\Src\Models\Setting::get('site.logo_media_id');
                    $logoMedia = $logoMediaId ? \Packages\Core\Src\Models\MediaFile::find($logoMediaId) : null;
                    $logoUrl = $logoMedia?->permalink ? media_permalink_url($logoMedia->permalink) : null;
                    $siteName = \Packages\Core\Src\Models\Setting::get('site.name')
                        ?: config('app.name', 'CoreCMS');
                @endphp
                <div class=" flex items-center justify-center px-6 border-b border-gray-50 dark:border-slate-700">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}"
                                 class="w-auto max-w-[160px] object-contain mx-auto flex-shrink-0">
                        @else
                            <div class="size-8 rounded-lg bg-primary flex items-center justify-center text-white flex-shrink-0">
                                <span class="material-symbols-rounded text-[20px]">dataset</span>
                            </div>
                            <h1 x-show="sidebarOpen" x-cloak class="text-text-main dark:text-white text-lg font-bold tracking-tight">
                                {{ $siteName }}
                            </h1>
                        @endif
                    </a>
                </div>
                
                {{-- Navigation --}}
                <nav class="flex flex-col gap-1 p-4">
                    @php
                        $sidebarItems = app(\Packages\Core\Src\Services\SidebarService::class)->getVisibleItems();
                        $mainItems = collect($sidebarItems)->filter(fn($item) => !in_array($item->slug ?? '', ['settings', 'roles']));
                        $systemItems = collect($sidebarItems)->filter(fn($item) => in_array($item->slug ?? '', ['settings', 'roles']));
                    @endphp
                    
                    {{-- Main Menu --}}
                    <p x-show="sidebarOpen" class="px-3 text-xs font-semibold text-text-muted dark:text-slate-500 uppercase tracking-wider mb-2 mt-2">
                        Main Menu
                    </p>
                    
                    @foreach($mainItems as $item)
                        @include('core::partials.sidebar-item', ['item' => $item])
                    @endforeach
                    
                    @if($systemItems->count() > 0)
                    {{-- System --}}
                    <p x-show="sidebarOpen" class="px-3 text-xs font-semibold text-text-muted dark:text-slate-500 uppercase tracking-wider mb-2 mt-6">
                        System
                    </p>
                    
                    @foreach($systemItems as $item)
                        @include('core::partials.sidebar-item', ['item' => $item])
                    @endforeach
                    @endif
                </nav>
            </div>
            
            {{-- User Mini Profile --}}
            <div class="p-4 border-t border-gray-50 dark:border-slate-700">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 cursor-pointer transition-colors">
                        <div class="size-9 rounded-full bg-gradient-to-br from-primary to-indigo-500 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div x-show="sidebarOpen" class="flex-1 min-w-0 text-left">
                            <p class="text-sm font-medium text-text-main dark:text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-text-muted dark:text-slate-400 truncate">{{ optional(auth()->user()->roles)->first()?->name ?? 'Admin' }}</p>
                        </div>
                        <span x-show="sidebarOpen" class="material-symbols-rounded text-text-muted dark:text-slate-400 text-[18px]">more_vert</span>
                    </button>
                    
                    {{-- Dropdown Menu --}}
                    <div x-show="open" @click.outside="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute bottom-full left-0 mb-2 w-full bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-lg shadow-xl py-1 z-50">
                        @if(class_exists(\Packages\Customer\Src\Providers\CustomerServiceProvider::class))
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-text-main dark:text-white hover:bg-gray-50 dark:hover:bg-slate-700">
                            <span class="material-symbols-rounded text-[18px]">storefront</span>
                            Customer Panel
                        </a>
                        <hr class="border-gray-100 dark:border-slate-700 my-1">
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-slate-700">
                                <span class="material-symbols-rounded text-[18px]">logout</span>
                                Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
        
        {{-- Main Content --}}
        <main class="flex-1 flex flex-col h-full overflow-hidden bg-background-light dark:bg-background-dark">
            {{-- Top Header --}}
            <header class="h-16 flex items-center justify-between px-6 lg:px-8 bg-surface-light dark:bg-surface-dark border-b border-gray-100 dark:border-slate-700 flex-shrink-0 z-10 sticky top-0 shadow-sm">
                {{-- Left: Toggle + Search --}}
                <div class="flex items-center gap-4 flex-1">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-text-muted hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-text-main dark:hover:text-white transition-colors">
                        <span class="material-symbols-rounded">menu</span>
                    </button>
                    
                    {{-- Search Bar --}}
                    <div class="hidden md:block flex-1 max-w-md">
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400 group-focus-within:text-primary transition-colors">search</span>
                            </div>
                            <input type="text" 
                                   placeholder="Tìm kiếm..." 
                                   class="block w-full pl-10 pr-4 py-2 border-none rounded-full leading-5 bg-gray-100 dark:bg-slate-700 text-text-main dark:text-white placeholder-gray-500 dark:placeholder-slate-400 focus:outline-none focus:bg-white dark:focus:bg-slate-600 focus:ring-1 focus:ring-primary/20 sm:text-sm transition-all shadow-inner">
                        </div>
                    </div>
                </div>
                
                {{-- Right: Actions --}}
                <!-- <div class="flex items-center gap-3">
                    {{-- Notification Bell --}}
                    <button class="relative p-2 rounded-full text-text-muted hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-primary transition-colors">
                        <span class="material-symbols-rounded">notifications</span>
                        <span class="absolute top-2 right-2 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-800"></span>
                    </button>
                    
                    {{-- Dark Mode Toggle --}}
                    <button @click="darkMode = !darkMode" class="p-2 rounded-full text-text-muted hover:bg-gray-100 dark:hover:bg-slate-700 hover:text-primary transition-colors">
                        <span x-show="!darkMode" class="material-symbols-rounded">dark_mode</span>
                        <span x-show="darkMode" x-cloak class="material-symbols-rounded">light_mode</span>
                    </button>
                    
                    {{-- Primary Action Button --}}
                    @hasSection('header-action')
                        @yield('header-action')
                    @endif
                </div> -->
            </header>
            
            {{-- Scrollable Content Area --}}
            <div class="flex-1 overflow-y-auto p-6 lg:p-8">
                <div class="max-w-7xl mx-auto">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 flex items-center gap-3">
                        <span class="material-symbols-rounded">check_circle</span>
                        {{ session('success') }}
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 flex items-center gap-3">
                        <span class="material-symbols-rounded">error</span>
                        {{ session('error') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-rounded">error</span>
                            <span class="font-medium">Vui lòng kiểm tra lại thông tin:</span>
                        </div>
                        <ul class="list-disc list-inside ml-8 space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>
