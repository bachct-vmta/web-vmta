<?php

namespace Packages\Localization\Src\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Set application locale from URL prefix (/vi, /en).
 *
 * Falls back to Accept-Language → default locale.
 * Sits in the public route group; admin/api routes bypass via urlsIgnored config.
 */
class SetLocaleFromRoute
{
    public function handle(Request $request, Closure $next)
    {
        $segment = $request->segment(1);
        $supported = array_keys(config('laravellocalization.supportedLocales', []));

        if ($segment && in_array($segment, $supported, true)) {
            $locale = $segment;
        } else {
            $locale = $this->detectFromHeader($request, $supported)
                ?? config('app.fallback_locale', 'vi');
        }

        App::setLocale($locale);
        LaravelLocalization::setLocale($locale);

        return $next($request);
    }

    protected function detectFromHeader(Request $request, array $supported): ?string
    {
        if (! config('laravellocalization.useAcceptLanguageHeader', true)) {
            return null;
        }

        foreach ($request->getLanguages() as $lang) {
            $short = substr($lang, 0, 2);
            if (in_array($short, $supported, true)) {
                return $short;
            }
        }

        return null;
    }
}
