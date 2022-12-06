<?php

namespace JustBetter\ImageOptimize\Listeners;

use Statamic\Assets\Asset;
use Statamic\Events\AssetReuploaded;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;

class AssetReuploadedListener
{
    public function handle(AssetReuploaded $event): void
    {
        /** @var Asset $asset */
        $asset = $event->asset;

        if (!$asset->exists()) {
            return;
        }

        if ($asset->isImage()) {
            ResizeImageJob::dispatch($asset);
        }
    }
}
