<?php

namespace JustBetter\ImageOptimize\Listeners;

use Statamic\Assets\Asset;
use Statamic\Events\AssetUploaded;
use Statamic\Events\AssetReuploaded;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;

class AssetUploadedListener
{
    public function handle(AssetUploaded|AssetReuploaded $event): void
    {
        /** @var Asset $asset */
        $asset = $event->asset;

        ResizeImageJob::dispatch($asset);
    }
}
