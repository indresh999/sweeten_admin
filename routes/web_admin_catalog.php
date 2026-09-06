<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminSubcategoryController;
use App\Http\Controllers\Admin\AdminHomeLayoutController;
use App\Http\Controllers\Admin\AdminShopHomeLayoutController;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Home Layout configurator (categories)
    Route::get('/home-layout',       [AdminHomeLayoutController::class, 'index'])->name('home-layout.index');
    Route::post('/home-layout/save', [AdminHomeLayoutController::class, 'save']) ->name('home-layout.save');

    // Shop Home Layout configurator
    Route::get ('/shop-home-layout',                [AdminShopHomeLayoutController::class, 'index'])        ->name('shop-home-layout.index');
    Route::post('/shop-home-layout/featured/save',  [AdminShopHomeLayoutController::class, 'saveFeatured']) ->name('shop-home-layout.featured.save');
    Route::post('/shop-home-layout/popular/save',   [AdminShopHomeLayoutController::class, 'savePopular'])  ->name('shop-home-layout.popular.save');

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/',              [AdminCategoryController::class,'index'])    ->name('index');
        Route::get('/create',        [AdminCategoryController::class,'create'])   ->name('create');
        Route::post('/',             [AdminCategoryController::class,'store'])    ->name('store');
        Route::get('/{id}',          [AdminCategoryController::class,'show'])     ->name('show');
        Route::get('/{id}/edit',     [AdminCategoryController::class,'edit'])     ->name('edit');
        Route::put('/{id}',          [AdminCategoryController::class,'update'])   ->name('update');
        Route::delete('/{id}',       [AdminCategoryController::class,'destroy'])  ->name('destroy');
        Route::post('/{id}/toggle',  [AdminCategoryController::class,'toggle'])   ->name('toggle');
        Route::post('/bulk',         [AdminCategoryController::class,'bulkAction'])->name('bulk');
        Route::post('/reorder',      [AdminCategoryController::class,'reorder'])  ->name('reorder');
        Route::get('/ajax/search',   [AdminCategoryController::class,'ajaxSearch'])->name('ajax.search');
        Route::get('/{id}/subcategories',[AdminCategoryController::class,'getSubcategories'])->name('subcategories');
    });

    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/',              [AdminSubcategoryController::class,'index'])   ->name('index');
        Route::get('/create',        [AdminSubcategoryController::class,'create'])  ->name('create');
        Route::post('/',             [AdminSubcategoryController::class,'store'])   ->name('store');
        Route::get('/{id}',          [AdminSubcategoryController::class,'show'])    ->name('show');
        Route::get('/{id}/edit',     [AdminSubcategoryController::class,'edit'])    ->name('edit');
        Route::put('/{id}',          [AdminSubcategoryController::class,'update'])  ->name('update');
        Route::delete('/{id}',       [AdminSubcategoryController::class,'destroy']) ->name('destroy');
        Route::post('/{id}/toggle',  [AdminSubcategoryController::class,'toggle'])  ->name('toggle');
        Route::get('/ajax/search',   [AdminSubcategoryController::class,'ajaxSearch'])->name('ajax.search');
    });
});
