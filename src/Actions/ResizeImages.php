<?php

namespace JustBetter\ImageOptimize\Actions;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use JustBetter\ImageOptimize\Contracts\ResizesImages;
use JustBetter\ImageOptimize\Events\ImagesResizedEvent;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;
use Statamic\Facades\Asset as AssetFacade;

class ResizeImages implements ResizesImages
{
    public function resize(bool $forceAll = false): Batch
    {
        /** @var AssetCollection $assets */
        $assets = AssetFacade::all();

        $assets->getOptimizableAssets() // @phpstan-ignore-line
            ->when(! $forceAll, fn () => $assets->whereNull('image-optimized'));

        $jobs = $assets
            ->filter(fn (Asset $asset): bool => $asset->isImage())
            ->map(fn (Asset $asset) => new ResizeImageJob($asset->hydrate()));

        return Bus::batch($jobs)
            ->name('image-optimize')
            ->onConnection(config('image-optimize.default_queue_connection'))
            ->onQueue(config('image-optimize.default_queue_name'))
            ->then(function (): void {
                ImagesResizedEvent::dispatch();
            })
            ->dispatch();
    }

    public static function bind(): void
    {
        app()->singleton(ResizesImages::class, static::class);
    }
}
