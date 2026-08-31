<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Media Manager')</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Material Symbols (both Rounded + Outlined) --}}
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
                        'background-light': '#f9fafb',
                        'background-dark': '#1d2839',
                        'surface-light': '#ffffff',
                        'surface-dark': '#1e293b',
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
                        'card': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
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
        .fill-1 {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        .dark ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>

    {{-- Third-party CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">

    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-sans text-slate-800 dark:text-slate-100 h-screen overflow-hidden selection:bg-primary selection:text-white">

    {{-- Popup header — exposes upload + dropdown that the parent admin layout normally renders --}}
    <header class="flex items-center justify-between gap-4 px-6 py-3 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <h1 class="text-base font-semibold text-slate-800 dark:text-slate-100">
            @yield('title', 'Media Manager')
        </h1>
        <div class="flex items-center gap-2">
            @yield('header-action')
        </div>
    </header>

    {{-- Main Container --}}
    <div id="media-app" class="flex-1 flex flex-col overflow-hidden">
        @yield('content')
    </div>

    <script>
        // Dark mode toggle helper
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }

        // Initialize dark mode from localStorage
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>

    @stack('scripts')
</body>
</html>
