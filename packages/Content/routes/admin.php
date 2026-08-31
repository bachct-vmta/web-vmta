<?php

use Illuminate\Support\Facades\Route;
use Packages\Content\Src\Http\Controllers\Admin\AboutSectionController;
use Packages\Content\Src\Http\Controllers\Admin\AchievementSectionController;
use Packages\Content\Src\Http\Controllers\Admin\AllianceSectionController;
use Packages\Content\Src\Http\Controllers\Admin\CategoryController;
use Packages\Content\Src\Http\Controllers\Admin\CoreValuesController;
use Packages\Content\Src\Http\Controllers\Admin\MedicalCaseController;
use Packages\Content\Src\Http\Controllers\Admin\HomeSectionController;
use Packages\Content\Src\Http\Controllers\Admin\MenuController;
use Packages\Content\Src\Http\Controllers\Admin\MenuItemController;
use Packages\Content\Src\Http\Controllers\Admin\MenuItemQuickAddController;
use Packages\Content\Src\Http\Controllers\Admin\MenuItemReorderController;
use Packages\Content\Src\Http\Controllers\Admin\MenuSourceController;
use Packages\Content\Src\Http\Controllers\Admin\PageController;
use Packages\Content\Src\Http\Controllers\Admin\PostController;
use Packages\Content\Src\Http\Controllers\Admin\TranslationController;

/*
| Mounted by ContentServiceProvider inside the shared Core admin group (auth + permission).
| Each route declares the required permission flag explicitly because the default
| `permission` middleware derives the flag from the route NAME, and our names
| (admin.{pages,posts,categories}.*) do not match flags in permissions.php
| (content.create/edit/delete are coarse for the whole Content domain).
*/

// Translation proxy — VI→EN for content fields
Route::post('translate', [TranslationController::class, 'translate'])
    ->middleware('permission:content.edit')
    ->middleware('throttle:30,1')
    ->name('translate');

// Pages
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/', [PageController::class, 'index'])->middleware('permission:content.index')->name('index');
    Route::get('create', [PageController::class, 'create'])->middleware('permission:content.create')->name('create');
    Route::post('/', [PageController::class, 'store'])->middleware('permission:content.create')->name('store');
    Route::post('bulk-delete', [PageController::class, 'bulkDelete'])->middleware('permission:content.delete')->name('bulk-delete');
    Route::get('{page}/edit', [PageController::class, 'edit'])->whereNumber('page')->middleware('permission:content.edit')->name('edit');
    Route::put('{page}', [PageController::class, 'update'])->whereNumber('page')->middleware('permission:content.edit')->name('update');
    Route::delete('{page}', [PageController::class, 'destroy'])->whereNumber('page')->middleware('permission:content.delete')->name('destroy');
});

// Posts
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->middleware('permission:content.index')->name('index');
    Route::get('create', [PostController::class, 'create'])->middleware('permission:content.create')->name('create');
    Route::post('/', [PostController::class, 'store'])->middleware('permission:content.create')->name('store');
    Route::post('bulk-delete', [PostController::class, 'bulkDelete'])->middleware('permission:content.delete')->name('bulk-delete');
    Route::get('{post}/edit', [PostController::class, 'edit'])->whereNumber('post')->middleware('permission:content.edit')->name('edit');
    Route::put('{post}', [PostController::class, 'update'])->whereNumber('post')->middleware('permission:content.edit')->name('update');
    Route::delete('{post}', [PostController::class, 'destroy'])->whereNumber('post')->middleware('permission:content.delete')->name('destroy');
});

// Categories
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->middleware('permission:content.index')->name('index');
    Route::get('create', [CategoryController::class, 'create'])->middleware('permission:content.create')->name('create');
    Route::post('/', [CategoryController::class, 'store'])->middleware('permission:content.create')->name('store');
    Route::post('bulk-delete', [CategoryController::class, 'bulkDelete'])->middleware('permission:content.delete')->name('bulk-delete');
    Route::get('{category}/edit', [CategoryController::class, 'edit'])->whereNumber('category')->middleware('permission:content.edit')->name('edit');
    Route::put('{category}', [CategoryController::class, 'update'])->whereNumber('category')->middleware('permission:content.edit')->name('update');
    Route::delete('{category}', [CategoryController::class, 'destroy'])->whereNumber('category')->middleware('permission:content.delete')->name('destroy');
});

// Menus + MenuItems (nested)
Route::prefix('menus')->name('menus.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->middleware('permission:content.index')->name('index');
    Route::get('create', [MenuController::class, 'create'])->middleware('permission:content.create')->name('create');
    Route::post('/', [MenuController::class, 'store'])->middleware('permission:content.create')->name('store');

    // Picker source for the add-link panel (page/post/category search).
    Route::get('sources', MenuSourceController::class)->middleware('permission:content.index')->name('sources');

    Route::get('{menu}/edit', [MenuController::class, 'edit'])->whereNumber('menu')->middleware('permission:content.edit')->name('edit');
    Route::put('{menu}', [MenuController::class, 'update'])->whereNumber('menu')->middleware('permission:content.edit')->name('update');
    Route::delete('{menu}', [MenuController::class, 'destroy'])->whereNumber('menu')->middleware('permission:content.delete')->name('destroy');

    // Drag-drop reorder (AJAX, JSON).
    Route::post('{menu}/reorder', MenuItemReorderController::class)->whereNumber('menu')->middleware('permission:content.edit')->name('reorder');

    // Quick-add a new root item from the picker panel.
    Route::post('{menu}/items/quick-add', MenuItemQuickAddController::class)->whereNumber('menu')->middleware('permission:content.create')->name('items.quick-add');

    // MenuItems scoped under their parent Menu (legacy form-based fallback).
    Route::prefix('{menu}/items')->name('items.')->whereNumber('menu')->group(function () {
        Route::get('create', [MenuItemController::class, 'create'])->middleware('permission:content.create')->name('create');
        Route::post('/', [MenuItemController::class, 'store'])->middleware('permission:content.create')->name('store');
        Route::get('{item}/edit', [MenuItemController::class, 'edit'])->whereNumber('item')->middleware('permission:content.edit')->name('edit');
        Route::put('{item}', [MenuItemController::class, 'update'])->whereNumber('item')->middleware('permission:content.edit')->name('update');
        Route::delete('{item}', [MenuItemController::class, 'destroy'])->whereNumber('item')->middleware('permission:content.delete')->name('destroy');
    });
});

// MenuItems AJAX surface (top-level — item id alone is enough, menu_id derived via FK).
Route::prefix('menu-items')->name('menu-items.')->group(function () {
    Route::patch('{item}', [MenuItemController::class, 'updateAjax'])->whereNumber('item')->middleware('permission:content.edit')->name('update');
    Route::delete('{item}', [MenuItemController::class, 'destroyAjax'])->whereNumber('item')->middleware('permission:content.delete')->name('destroy-ajax');
});

// Home Sections (homepage CMS)
Route::prefix('home')->name('home.')->group(function () {
    Route::get('/', [HomeSectionController::class, 'index'])->middleware('permission:home.manage')->name('index');
    Route::put('{position}', [HomeSectionController::class, 'update'])->where('position', '[a-z_]+')->middleware('permission:home.manage')->name('update');
});

// About Page Sections (gioi-thieu CMS)
Route::prefix('about')->name('about.')->group(function () {
    Route::get('/', [AboutSectionController::class, 'index'])->middleware('permission:about.manage')->name('index');
    Route::put('{position}', [AboutSectionController::class, 'update'])->where('position', '[a-z_]+')->middleware('permission:about.manage')->name('update');
});

// Core Values (shared block used by both Home & About pages)
Route::prefix('core-values')->name('core-values.')->group(function () {
    Route::get('/', [CoreValuesController::class, 'index'])->middleware('permission:about.manage')->name('index');
});

// Alliance Network Page Sections (mang-luoi-lien-minh CMS)
Route::prefix('alliance')->name('alliance.')->group(function () {
    Route::get('/', [AllianceSectionController::class, 'index'])->middleware('permission:alliance.manage')->name('index');
    Route::put('{position}', [AllianceSectionController::class, 'update'])->where('position', '[a-z_]+')->middleware('permission:alliance.manage')->name('update');
});

// Achievement Page Sections (thanh-tuu-y-khoa CMS) + Medical Cases CRUD
Route::prefix('achievement')->name('achievement.')->group(function () {
    Route::get('/', [AchievementSectionController::class, 'index'])->middleware('permission:achievement.manage')->name('index');
    Route::put('{position}', [AchievementSectionController::class, 'update'])->where('position', '[a-z_]+')->middleware('permission:achievement.manage')->name('update');

    Route::prefix('cases')->name('cases.')->group(function () {
        Route::get('/', [MedicalCaseController::class, 'index'])->middleware('permission:achievement.manage')->name('index');
        Route::get('create', [MedicalCaseController::class, 'create'])->middleware('permission:achievement.manage')->name('create');
        Route::post('/', [MedicalCaseController::class, 'store'])->middleware('permission:achievement.manage')->name('store');
        Route::get('{case}/edit', [MedicalCaseController::class, 'edit'])->whereNumber('case')->middleware('permission:achievement.manage')->name('edit');
        Route::put('{case}', [MedicalCaseController::class, 'update'])->whereNumber('case')->middleware('permission:achievement.manage')->name('update');
        Route::get('{case}/detail', [MedicalCaseController::class, 'editDetail'])->whereNumber('case')->middleware('permission:achievement.manage')->name('detail.edit');
        Route::put('{case}/detail', [MedicalCaseController::class, 'updateDetail'])->whereNumber('case')->middleware('permission:achievement.manage')->name('detail.update');
        Route::delete('{case}', [MedicalCaseController::class, 'destroy'])->whereNumber('case')->middleware('permission:achievement.manage')->name('destroy');
    });
});
