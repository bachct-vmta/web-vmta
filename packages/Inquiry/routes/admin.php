<?php

use Illuminate\Support\Facades\Route;
use Packages\Inquiry\Src\Http\Controllers\Admin\ContactSectionController;
use Packages\Inquiry\Src\Http\Controllers\Admin\InquiryController;

/*
| Mounted by InquiryServiceProvider inside the shared Core admin group (auth + permission middleware).
| Each route declares its required permission flag explicitly because the `permission` middleware
| derives the flag from the route NAME by default — and our route names (admin.inquiries.*) do not
| match the flag names (inquiry.*) declared in packages/Core/configs/permissions.php.
*/

Route::prefix('inquiries')->name('inquiries.')->group(function () {
    Route::get('/', [InquiryController::class, 'index'])
        ->middleware('permission:inquiry.index')
        ->name('index');

    Route::post('bulk-delete', [InquiryController::class, 'bulkDelete'])
        ->middleware('permission:inquiry.delete')
        ->name('bulk-delete');

    Route::get('{id}', [InquiryController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:inquiry.view')
        ->name('show');

    Route::post('{id}/assign', [InquiryController::class, 'assign'])
        ->whereNumber('id')
        ->middleware('permission:inquiry.assign')
        ->name('assign');

    Route::post('{id}/transition', [InquiryController::class, 'transition'])
        ->whereNumber('id')
        ->middleware('permission:inquiry.update')
        ->name('transition');

    Route::post('{id}/note', [InquiryController::class, 'note'])
        ->whereNumber('id')
        ->middleware('permission:inquiry.update')
        ->name('note');

    Route::delete('{id}', [InquiryController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('permission:inquiry.delete')
        ->name('destroy');
});

/*
| Contact page CMS — edit the public /lien-he static content (hero, offices, extras).
| Distinct from the inquiry records above; guarded by the contact.manage flag.
*/
Route::prefix('contact')->name('contact.')->group(function () {
    Route::get('/', [ContactSectionController::class, 'index'])
        ->middleware('permission:contact.manage')
        ->name('index');

    Route::put('{position}', [ContactSectionController::class, 'update'])
        ->where('position', '[a-z_]+')
        ->middleware('permission:contact.manage')
        ->name('update');
});
