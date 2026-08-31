<?php

use Illuminate\Support\Facades\Route;
use Packages\Core\Src\Http\Controllers\Admin\DashboardController;
use Packages\Core\Src\Http\Controllers\Admin\RoleController;
use Packages\Core\Src\Http\Controllers\Admin\SettingController;
use Packages\Core\Src\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix(admin_prefix())
    ->middleware(['web', 'auth', 'permission'])
    ->name(admin_route_name())
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::resource('users', UserController::class);
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::post('users/bulk-lock', [UserController::class, 'bulkLock'])->name('users.bulk-lock');
        Route::post('users/bulk-change-role', [UserController::class, 'bulkChangeRole'])->name('users.bulk-change-role');

        // Roles
        Route::resource('roles', RoleController::class);
        Route::post('roles/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');

        // Settings (System)
        Route::get('settings', [SettingController::class, 'index'])
            ->middleware('permission:settings.index')
            ->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])
            ->middleware('permission:settings.edit')
            ->name('settings.update');
        Route::post('settings/test-mail', [SettingController::class, 'testMail'])
            ->middleware('permission:settings.edit')
            ->name('settings.test-mail');
    });
