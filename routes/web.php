<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Public routes live inside the locale prefix group (/vi, /en).
 * Admin/api/auth routes register from packages/Core and bypass the locale prefix.
 *
 * Real public routes (Site, Content, Catalog…) register inside their own
 * package service providers; this file only owns the bare-root redirect that
 * negotiates the locale from the Accept-Language header before sending the
 * browser into the locale-prefixed group.
 */

Route::get('/', function (Request $request) {
    $supported = array_keys(config('laravellocalization.supportedLocales', []));
    $default = config('app.locale', 'vi');

    $locale = $default;
    if (config('laravellocalization.useAcceptLanguageHeader', true)) {
        foreach ($request->getLanguages() as $lang) {
            $short = substr($lang, 0, 2);
            if (in_array($short, $supported, true)) {
                $locale = $short;
                break;
            }
        }
    }

    return redirect()->to('/' . $locale);
})->name('root.redirect');
