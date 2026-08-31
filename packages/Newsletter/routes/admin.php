<?php

use Illuminate\Support\Facades\Route;
use Packages\Newsletter\Src\Http\Controllers\Admin\SubscriberController;

/*
| Mounted by NewsletterServiceProvider inside the shared Core admin group
| (auth + permission middleware). Permissions tied to newsletter.* flags.
*/

Route::prefix('newsletter')->name('newsletter.')->group(function () {
    Route::get('/', [SubscriberController::class, 'index'])
        ->middleware('permission:newsletter.index')
        ->name('index');

    Route::get('export', [SubscriberController::class, 'export'])
        ->middleware('permission:newsletter.export')
        ->name('export');

    Route::post('bulk-delete', [SubscriberController::class, 'bulkDelete'])
        ->middleware('permission:newsletter.delete')
        ->name('bulk-delete');

    Route::delete('{id}', [SubscriberController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('permission:newsletter.delete')
        ->name('destroy');
});
