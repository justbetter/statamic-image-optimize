<?php

use Illuminate\Support\Facades\Route;
use JustBetter\ImageOptimize\Http\Controllers\CP\ImageResizeController;

Route::prefix('statamic-image-optimize')
    ->name('statamic-image-optimize.')
    ->controller(ImageResizeController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');

        Route::post('/batches', 'startBatch')->name('batches.start');
        Route::get('/batches/{batchId}', 'batchStatus')->name('batches.status');
    });
