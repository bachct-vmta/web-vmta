<?php

use Illuminate\Support\Facades\Route;
use Packages\Dental\Src\Http\Controllers\Public\FacilityController;
use Packages\Dental\Src\Http\Controllers\Public\FacilityDirectoryController;
use Packages\Dental\Src\Http\Controllers\Public\ServiceController;

/*
| Mounted per locale by DentalServiceProvider. $slug comes from config dental.url_slug
| so the URL segment can differ per locale without touching the route names.
*/

Route::get($slug, [FacilityDirectoryController::class, 'index'])->name('index');
Route::get($slug.'/{facility}', [FacilityController::class, 'show'])->name('facility');
Route::get($slug.'/{facility}/{service}', [ServiceController::class, 'show'])->name('service');
