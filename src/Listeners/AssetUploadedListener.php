<?php

namespace JustBetter\ImageOptimize\Listeners;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Assets\Asset;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetUploaded;

class AssetUploadedListener
{
    public function handle(AssetUploaded|AssetReuploaded $event): void
    {
        /** @var Asset $asset */
        $asset = $event->asset;

        ResizeImageJob::dispatch($asset);
    }
}
