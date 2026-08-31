<?php

use Illuminate\Support\Facades\Route;
use Packages\Dental\Src\Http\Controllers\Admin\DentalCategoryController;
use Packages\Dental\Src\Http\Controllers\Admin\DentalFacilityController;
use Packages\Dental\Src\Http\Controllers\Admin\DentalServiceController;

/*
| Mounted by DentalServiceProvider inside the shared Core admin group (auth + permission middleware).
| Each route declares its permission flag explicitly because the default `permission` middleware
| derives the flag from the route NAME, which does not match the dental.* flags used here.
*/

Route::prefix('dental-categories')->name('dental_categories.')->group(function () {
    Route::get('/', [DentalCategoryController::class, 'index'])->middleware('permission:dental.index')->name('index');
    Route::get('create', [DentalCategoryController::class, 'create'])->middleware('permission:dental.create')->name('create');
    Route::post('/', [DentalCategoryController::class, 'store'])->middleware('permission:dental.create')->name('store');
    Route::post('bulk-delete', [DentalCategoryController::class, 'bulkDelete'])->middleware('permission:dental.delete')->name('bulk-delete');
    Route::get('{category}/edit', [DentalCategoryController::class, 'edit'])->whereNumber('category')->middleware('permission:dental.edit')->name('edit');
    Route::put('{category}', [DentalCategoryController::class, 'update'])->whereNumber('category')->middleware('permission:dental.edit')->name('update');
    Route::delete('{category}', [DentalCategoryController::class, 'destroy'])->whereNumber('category')->middleware('permission:dental.delete')->name('destroy');
});

Route::prefix('dental-facilities')->name('dental_facilities.')->group(function () {
    Route::get('/', [DentalFacilityController::class, 'index'])->middleware('permission:dental.index')->name('index');
    Route::get('create', [DentalFacilityController::class, 'create'])->middleware('permission:dental.create')->name('create');
    Route::post('/', [DentalFacilityController::class, 'store'])->middleware('permission:dental.create')->name('store');
    Route::post('bulk-delete', [DentalFacilityController::class, 'bulkDelete'])->middleware('permission:dental.delete')->name('bulk-delete');
    Route::get('{facility}/edit', [DentalFacilityController::class, 'edit'])->whereNumber('facility')->middleware('permission:dental.edit')->name('edit');
    Route::put('{facility}', [DentalFacilityController::class, 'update'])->whereNumber('facility')->middleware('permission:dental.edit')->name('update');
    Route::delete('{facility}', [DentalFacilityController::class, 'destroy'])->whereNumber('facility')->middleware('permission:dental.delete')->name('destroy');
});

Route::prefix('dental-services')->name('dental_services.')->group(function () {
    Route::get('/', [DentalServiceController::class, 'index'])->middleware('permission:dental.index')->name('index');
    Route::get('create', [DentalServiceController::class, 'create'])->middleware('permission:dental.create')->name('create');
    Route::post('/', [DentalServiceController::class, 'store'])->middleware('permission:dental.create')->name('store');
    Route::post('bulk-delete', [DentalServiceController::class, 'bulkDelete'])->middleware('permission:dental.delete')->name('bulk-delete');
    Route::get('{service}/edit', [DentalServiceController::class, 'edit'])->whereNumber('service')->middleware('permission:dental.edit')->name('edit');
    Route::put('{service}', [DentalServiceController::class, 'update'])->whereNumber('service')->middleware('permission:dental.edit')->name('update');
    Route::delete('{service}', [DentalServiceController::class, 'destroy'])->whereNumber('service')->middleware('permission:dental.delete')->name('destroy');
});
