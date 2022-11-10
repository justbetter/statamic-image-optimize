<?php

namespace JustBetter\ImageOptimize\Actions;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;

class ResizeImages
{
    public int $chunkSize = 200;

    public function __construct(
        AssetCollection $assetCollection
    ) {
        $this->resizeImages($assetCollection);
    }

    public function resizeImages(AssetCollection $assetCollection): void
    {
        $assetCollection->chunk($this->chunkSize)->each(function (AssetCollection $assets) {
            $assets->each(function (Asset $asset) {
                if($asset->isImage()) {
                    $asset->hydrate();
                    ResizeImageJob::dispatch($asset);
                }
            });
        });
    }
}
