<?php

use Illuminate\Support\Facades\Route;
use JustBetter\ImageOptimize\Http\Controllers\CP\ImageResizeController;

Route::prefix('statamic-image-optimize')
    ->name('statamic-image-optimize.')
    ->controller(ImageResizeController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/resize-images/{forceAll?}', 'resizeImages')->name('resize-images');
        Route::get('/resize-images-count/{batchId?}', 'resizeImagesJobCount')->name('resize-images-count');
    });
