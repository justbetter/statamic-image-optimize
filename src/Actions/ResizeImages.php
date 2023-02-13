<?php

namespace JustBetter\ImageOptimize\Actions;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;
use Symfony\Component\Console\Helper\ProgressBar;

class ResizeImages
{
    public int $chunkSize = 200;

    public function __construct(
        AssetCollection $assetCollection,
        public ?ProgressBar $progressBar = null
    ) {
        $this->resizeImages($assetCollection);
    }

    public function resizeImages(AssetCollection $assetCollection): void
    {
        $assetCollection->lazy()->each(function (Asset $asset) {
            if($asset->isImage()) {
                $asset->hydrate();

                if ($this->progressBar) {
                    ResizeImageJob::dispatchSync($asset);
                    $this->progressBar->advance();
                } else {
                    ResizeImageJob::dispatch($asset);
                }
            }
        });
    }
}
