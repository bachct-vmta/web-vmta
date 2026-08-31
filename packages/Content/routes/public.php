<?php

use Illuminate\Support\Facades\Route;
use Packages\Content\Src\Http\Controllers\Public\AboutController;
use Packages\Content\Src\Http\Controllers\Public\HomeController;
use Packages\Content\Src\Http\Controllers\Public\MedicalAchievementController;
use Packages\Content\Src\Http\Controllers\Public\PageController;
use Packages\Content\Src\Http\Controllers\Public\PostController;

/*
| Mounted by ContentServiceProvider inside the locale prefix group (/vi, /en).
| Slug values are passed in via the parent group's `$slugs` array — see
| ContentServiceProvider::registerPublicRoutes() for the locale → slug map.
|
| Post list lives at /{locale}/{tin-tuc|news}; detail at /{locale}/{tin-tuc|news}/{slug}.
| Generic Page catch-all is registered LAST so it does not shadow the post routes.
*/

/** @var array<string, string> $slugs — injected by parent ServiceProvider group */
$slugs = $slugs ?? [
    'about' => 'about',
    'medical' => 'medical-achievements',
    'heartLung' => 'simultaneous-heart-lung-transplant',
    'posts' => 'news',
    'alliance' => 'alliance-network',
];

Route::get('/', HomeController::class)->name('home');

Route::get($slugs['about'], AboutController::class)->name('about');

Route::get($slugs['medical'], MedicalAchievementController::class)->name('medical-achievements');

Route::get($slugs['heartLung'], [MedicalAchievementController::class, 'showHeartLung'])
    ->name('medical-achievements.heart-lung');

Route::prefix($slugs['posts'])->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('{slug}', [PostController::class, 'show'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->name('show');
});

// Exclude slugs owned by other packages (Inquiry: lien-he/contact, khan-cap) and
// the locale-aware Content slugs themselves (else /{locale}/news would be caught
// as a Page lookup before hitting the explicit posts.index route).
$reserved = [
    'lien-he', 'contact', 'khan-cap',
    $slugs['alliance'], $slugs['about'], $slugs['medical'], $slugs['heartLung'], $slugs['posts'],
];
$reservedRegex = implode('|', array_map('preg_quote', array_unique($reserved)));

Route::get('{slug}', [PageController::class, 'show'])
    ->where('slug', '(?!(?:'.$reservedRegex.')$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('page.show');
