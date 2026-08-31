@php
    $locale = app()->getLocale();
    $supported = array_keys(config('laravellocalization.supportedLocales', []));
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

@php $faviconUrl = media_url((int) setting('site.favicon_media_id')); @endphp
@if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
@endif

{{-- SEO meta (title/description/og/twitter) owned by artesaos/seotools.
     Controllers set context via SEOTools::setTitle()/setDescription()/etc.
     Pages that do NOT call SEOTools fall back to defaults in config/seotools.php. --}}
{!! SEO::generate() !!}

@php
    $localeUrlService = app(\Packages\Site\Src\Services\LocaleUrlService::class);
@endphp
@foreach ($supported as $code)
    <link rel="alternate"
          hreflang="{{ $code }}"
          href="{{ $localeUrlService->urlFor($code) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

{{-- Text fonts (Bebas Neue, Oswald) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;600;700&display=swap"
      rel="stylesheet">
{{-- Material Symbols Rounded — separate link required for ligature icon system --}}
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
      rel="stylesheet">

@vite([
    'packages/Site/resources/css/app.css',
    'packages/Chatbot/resources/js/chatbot-widget.js',
    'packages/Site/resources/js/app.js',
])
