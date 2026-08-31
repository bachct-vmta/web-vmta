<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('site::partials.head-meta')
    {{-- Typography reset for CKEditor HTML output (Tailwind preflight strips defaults). --}}
    <style>
        .cms-body p { margin: 0 0 0.85em; }
        .cms-body p:last-child { margin-bottom: 0; }
        .cms-body ul, .cms-body ol { padding-left: 1.5em; margin: 0.5em 0; }
        .cms-body ul { list-style: disc outside; }
        .cms-body ol { list-style: decimal outside; }
        .cms-body li { display: list-item; margin: 0.25em 0; }
        .cms-body strong, .cms-body b { font-weight: 700; }
        .cms-body em, .cms-body i { font-style: italic; }
        .cms-body a { color: #0b7f7c; text-decoration: underline; }
        .cms-body h1, .cms-body h2, .cms-body h3, .cms-body h4 { font-weight: 600; margin: 1em 0 0.5em; color: #0f172a; }
        .cms-body h2 { font-size: 1.4em; }
        .cms-body h3 { font-size: 1.2em; }
        .cms-body blockquote { border-left: 3px solid #0b7f7c; padding: 0.25em 0 0.25em 1em; margin: 0.75em 0; color: #475569; font-style: italic; }
        .cms-body img { max-width: 100%; height: auto; }
        .cms-body table { border-collapse: collapse; margin: 0.5em 0; }
        .cms-body table td, .cms-body table th { border: 1px solid #cbd5e1; padding: 6px 10px; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
    @include('site::partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('site::partials.footer')

    @include('chatbot::widget')

    @stack('scripts')
</body>
</html>
