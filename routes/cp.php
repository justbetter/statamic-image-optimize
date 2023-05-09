<?php

use Illuminate\Support\Facades\Route;
use JustBetter\ImageOptimize\Http\Controllers\CP\ImageResizeController;

Route::prefix('statamic-image-optimize')
    ->name('statamic-image-optimize.')
    ->group(function() {
        Route::get('/', [ImageResizeController::class, 'index'])
            ->name('index');
        Route::get('/resize-images/{forceAll?}', [ImageResizeController::class, 'resizeImages'])
            ->name('resize-images');
        Route::get('/resize-images-count/{batchId?}', [ImageResizeController::class, 'resizeImagesJobCount'])
            ->name('resize-images-count');
    });
