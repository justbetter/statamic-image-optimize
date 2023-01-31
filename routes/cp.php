<?php

Route::namespace('\JustBetter\ImageOptimize\Http\Controllers\CP')
    ->prefix('statamic-image-optimize')
    ->name('statamic-image-optimize.')
    ->group(function() {
        Route::get('/', 'ImageResizeController@index')
            ->name('index');
        Route::get('/resize-images/', 'ImageResizeController@resizeImages')
            ->name('resize-images');
        Route::get('/resize-all-images/', 'ImageResizeController@resizeAllImages')
            ->name('resize-all-images');
        Route::get('/resize-images-count/', 'ImageResizeController@resizeImagesJobCount')
            ->name('resize-images-count');
    });