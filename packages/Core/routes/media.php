<?php

use Illuminate\Support\Facades\Route;
use Packages\Core\Src\Http\Controllers\Admin\CKEditorController;
use Packages\Core\Src\Http\Controllers\Admin\GoogleDriveController;
use Packages\Core\Src\Http\Controllers\Admin\MediaController;
use Packages\Core\Src\Http\Controllers\Admin\MediaFileController;
use Packages\Core\Src\Http\Controllers\Admin\MediaFolderController;
use Packages\Core\Src\Http\Controllers\Admin\MediaSettingsController;

Route::group(
    [
        'middleware' => ['web', 'auth', 'permission'],
        'prefix' => admin_route_prefix('media'),
        'as' => admin_route_name('media.'),
    ], function () {

        // Main Media Controller
        Route::controller(MediaController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('load-media', 'loadMedia')->name('loadMedia');
            Route::post('update-options', 'updateName')->name('update');
            Route::delete('remove-trash', 'removeTrash')->name('destroy');
            Route::put('restore-trash', 'restoreTrash')->name('restore');
            Route::delete('empty-trash', 'removeNotRestore')->name('destroyFinally');
            Route::delete('empty-all-trash', 'emptyAllTrash')->name('emptyAllTrash');
        });

        // Folder Controller
        Route::controller(MediaFolderController::class)->group(function () {
            Route::post('create-folder', 'saveFolder')->name('folder.create');
        });

        // File Controller
        Route::controller(MediaFileController::class)->group(function () {
            Route::post('upload-from-url', 'uploadFileUrl')->name('file.uploadMultiple');
            Route::post('upload-file', 'uploadFile')->name('file.uploadFile');
            Route::post('upload-chunk', 'uploadChunk')->name('file.uploadChunk');
            Route::post('update-crop-image', 'saveCropImage')->name('file.updateCropImage');
            Route::put('update-data', 'updateData')->name('file.updateData');
        });

        // Settings Controller
        Route::controller(MediaSettingsController::class)->group(function () {
            Route::get('settings', 'index')->name('settings.index');
            Route::put('settings', 'update')->name('settings.update');
        });

        // Google Drive Integration
        Route::controller(GoogleDriveController::class)->group(function () {
            Route::get('google-drive/connect', 'redirect')->name('google-drive.redirect');
            Route::get('google-drive/callback', 'callback')->name('google-drive.callback');
            Route::post('google-drive/disconnect', 'disconnect')->name('google-drive.disconnect');
            Route::get('google-drive/status', 'status')->name('google-drive.status');
        });

        // CKEditor Integration
        Route::group([
            'prefix' => 'ckeditor',
            'as' => 'ckeditor.',
        ], function () {
            Route::controller(CKEditorController::class)->group(function () {
                Route::post('upload-ck-editor', 'uploadCKEditor')->name('upload');
            });
        });
    });
