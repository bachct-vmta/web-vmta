<?php

use Illuminate\Support\Facades\Route;
use Packages\Catalog\Src\Http\Controllers\Public\ComboController;
use Packages\Catalog\Src\Http\Controllers\Public\PartnerController;
use Packages\Catalog\Src\Http\Controllers\Public\ServiceController;
use Packages\Catalog\Src\Http\Controllers\Public\SpecialtyController;
use Packages\Catalog\Src\Http\Controllers\Public\SpecialtyLeadController;
use Packages\Catalog\Src\Http\Controllers\Public\TourPackageController;

/*
| Mounted by CatalogServiceProvider inside the per-locale prefix group (/vi, /en).
| Route NAMES are namespaced as `site.{locale}.catalog.{entity}.{action}` so they coexist with
| Content's `site.{locale}.content.*` and any future Site-package routes.
|
| Per-locale URL slugs are injected via the parent group's `$slugs` array
| (see CatalogServiceProvider::registerPublicRoutes()). Defaults preserve VN slugs.
*/

$slugPattern = '[a-z0-9]+(?:-[a-z0-9]+)*';

/** @var array<string, string> $slugs — injected by parent ServiceProvider group */
$slugs = $slugs ?? [
    'services' => 'dich-vu', 'tours' => 'tour', 'combos' => 'combo',
    'partners' => 'mang-luoi', 'specialties' => 'chuyen-khoa',
];

// TODO: re-enable when ready (Phase N)
// // Services (Dịch vụ / Gói khám)
// Route::prefix($slugs['services'])->name('services.')->group(function () use ($slugPattern) {
//     Route::get('/', [ServiceController::class, 'index'])->name('index');
//     Route::get('{slug}', [ServiceController::class, 'show'])->where('slug', $slugPattern)->name('show');
// });

// // Tour packages (Tour du lịch)
// Route::prefix($slugs['tours'])->name('tours.')->group(function () use ($slugPattern) {
//     Route::get('/', [TourPackageController::class, 'index'])->name('index');
//     Route::get('{slug}', [TourPackageController::class, 'show'])->where('slug', $slugPattern)->name('show');
// });

// // Combos
// Route::prefix($slugs['combos'])->name('combos.')->group(function () use ($slugPattern) {
//     Route::get('/', [ComboController::class, 'index'])->name('index');
//     Route::get('{slug}', [ComboController::class, 'show'])->where('slug', $slugPattern)->name('show');
// });

// // Partner network (Mạng lưới Liên Minh)
// Route::prefix($slugs['partners'])->name('partners.')->group(function () use ($slugPattern) {
//     Route::get('/', [PartnerController::class, 'index'])->name('index');
//     Route::get('{slug}', [PartnerController::class, 'show'])->where('slug', $slugPattern)->name('show');
// });

// Specialties (Chuyên khoa / specialties) — hub + detail + lead form POST
Route::prefix($slugs['specialties'])->name('specialties.')->group(function () use ($slugPattern) {
    Route::get('/', [SpecialtyController::class, 'index'])->name('index');
    Route::get('{slug}', [SpecialtyController::class, 'show'])->where('slug', $slugPattern)->name('show');
    Route::post('{slug}/lead', [SpecialtyLeadController::class, 'store'])
        ->where('slug', $slugPattern)
        // 30 requests/min/IP — honeypot + form validation handle bot spam, so
        // this just guards against burst floods. 6/min was too tight for legit
        // AJAX submissions + testing.
        ->middleware('throttle:30,1')
        ->name('lead.store');
});
