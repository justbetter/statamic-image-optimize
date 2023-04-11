<?php

namespace JustBetter\ImageOptimize\Actions;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class ResizeImages
{
    public int $chunkSize = 200;

    public function __construct(
        public AssetCollection $assetCollection
    ) {
    }

    public function resizeImages(): Batch
    {
        $batches = $this->assetCollection->map(function (Asset $asset) {
            if(!$asset->isImage()) {
                return null;
            }

            $asset->hydrate();
            return new ResizeImageJob($asset);
        })->filter();

        return Bus::batch($batches)
            ->name('image-optimize')
            ->onQueue(config('image-optimize.default_queue_name'))
            ->dispatch();
    }
}
