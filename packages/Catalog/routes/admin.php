<?php

use Illuminate\Support\Facades\Route;
use Packages\Catalog\Src\Http\Controllers\Admin\ComboController;
use Packages\Catalog\Src\Http\Controllers\Admin\PartnerController;
use Packages\Catalog\Src\Http\Controllers\Admin\PartnerLookupController;
use Packages\Catalog\Src\Http\Controllers\Admin\ServiceController;
use Packages\Catalog\Src\Http\Controllers\Admin\SpecialtyController;
use Packages\Catalog\Src\Http\Controllers\Admin\SpecialtyLeadAdminController;
use Packages\Catalog\Src\Http\Controllers\Admin\TourPackageController;

/*
| Mounted by CatalogServiceProvider inside the shared Core admin group (auth + permission middleware).
| Each route declares its required permission flag explicitly because the default `permission`
| middleware derives the flag from the route NAME — and our names (admin.{specialties,…}.*) do
| not match the flags in permissions.php (catalog.create/edit/delete/index are coarse for the
| whole Catalog domain).
*/

// Specialties
Route::prefix('specialties')->name('specialties.')->group(function () {
    Route::get('/', [SpecialtyController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('create', [SpecialtyController::class, 'create'])->middleware('permission:catalog.create')->name('create');
    Route::post('/', [SpecialtyController::class, 'store'])->middleware('permission:catalog.create')->name('store');
    Route::post('bulk-delete', [SpecialtyController::class, 'bulkDelete'])->middleware('permission:catalog.delete')->name('bulk-delete');
    Route::get('{specialty}/edit', [SpecialtyController::class, 'edit'])->whereNumber('specialty')->middleware('permission:catalog.edit')->name('edit');
    Route::put('{specialty}', [SpecialtyController::class, 'update'])->whereNumber('specialty')->middleware('permission:catalog.edit')->name('update');
    Route::delete('{specialty}', [SpecialtyController::class, 'destroy'])->whereNumber('specialty')->middleware('permission:catalog.delete')->name('destroy');
});

// Partners
Route::prefix('partners')->name('partners.')->group(function () {
    Route::get('/', [PartnerController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('create', [PartnerController::class, 'create'])->middleware('permission:catalog.create')->name('create');
    Route::post('/', [PartnerController::class, 'store'])->middleware('permission:catalog.create')->name('store');
    Route::post('bulk-delete', [PartnerController::class, 'bulkDelete'])->middleware('permission:catalog.delete')->name('bulk-delete');
    Route::get('{partner}/edit', [PartnerController::class, 'edit'])->whereNumber('partner')->middleware('permission:catalog.edit')->name('edit');
    Route::put('{partner}', [PartnerController::class, 'update'])->whereNumber('partner')->middleware('permission:catalog.edit')->name('update');
    Route::delete('{partner}', [PartnerController::class, 'destroy'])->whereNumber('partner')->middleware('permission:catalog.delete')->name('destroy');
    Route::get('{partner}/specialty-card-data', [PartnerLookupController::class, 'show'])
        ->whereNumber('partner')
        ->middleware('permission:catalog.edit')
        ->name('specialty_card_data');
});

// Specialty leads inbox (read-only)
Route::prefix('specialty-leads')->name('specialty_leads.')->group(function () {
    Route::get('/', [SpecialtyLeadAdminController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('{lead}', [SpecialtyLeadAdminController::class, 'show'])->whereNumber('lead')->middleware('permission:catalog.index')->name('show');
    Route::patch('{lead}/status', [SpecialtyLeadAdminController::class, 'updateStatus'])->whereNumber('lead')->middleware('permission:catalog.edit')->name('update_status');
});

// Services
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('create', [ServiceController::class, 'create'])->middleware('permission:catalog.create')->name('create');
    Route::post('/', [ServiceController::class, 'store'])->middleware('permission:catalog.create')->name('store');
    Route::post('bulk-delete', [ServiceController::class, 'bulkDelete'])->middleware('permission:catalog.delete')->name('bulk-delete');
    Route::get('{service}/edit', [ServiceController::class, 'edit'])->whereNumber('service')->middleware('permission:catalog.edit')->name('edit');
    Route::put('{service}', [ServiceController::class, 'update'])->whereNumber('service')->middleware('permission:catalog.edit')->name('update');
    Route::delete('{service}', [ServiceController::class, 'destroy'])->whereNumber('service')->middleware('permission:catalog.delete')->name('destroy');
});

// Tour packages (URL slug 'tours' for shorter admin paths)
Route::prefix('tours')->name('tours.')->group(function () {
    Route::get('/', [TourPackageController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('create', [TourPackageController::class, 'create'])->middleware('permission:catalog.create')->name('create');
    Route::post('/', [TourPackageController::class, 'store'])->middleware('permission:catalog.create')->name('store');
    Route::post('bulk-delete', [TourPackageController::class, 'bulkDelete'])->middleware('permission:catalog.delete')->name('bulk-delete');
    Route::get('{tour}/edit', [TourPackageController::class, 'edit'])->whereNumber('tour')->middleware('permission:catalog.edit')->name('edit');
    Route::put('{tour}', [TourPackageController::class, 'update'])->whereNumber('tour')->middleware('permission:catalog.edit')->name('update');
    Route::delete('{tour}', [TourPackageController::class, 'destroy'])->whereNumber('tour')->middleware('permission:catalog.delete')->name('destroy');
});

// Combos
Route::prefix('combos')->name('combos.')->group(function () {
    Route::get('/', [ComboController::class, 'index'])->middleware('permission:catalog.index')->name('index');
    Route::get('create', [ComboController::class, 'create'])->middleware('permission:catalog.create')->name('create');
    Route::post('/', [ComboController::class, 'store'])->middleware('permission:catalog.create')->name('store');
    Route::post('bulk-delete', [ComboController::class, 'bulkDelete'])->middleware('permission:catalog.delete')->name('bulk-delete');
    Route::get('{combo}/edit', [ComboController::class, 'edit'])->whereNumber('combo')->middleware('permission:catalog.edit')->name('edit');
    Route::put('{combo}', [ComboController::class, 'update'])->whereNumber('combo')->middleware('permission:catalog.edit')->name('update');
    Route::delete('{combo}', [ComboController::class, 'destroy'])->whereNumber('combo')->middleware('permission:catalog.delete')->name('destroy');
});
