<?php

namespace JustBetter\ImageOptimize\Listeners;

use Statamic\Assets\Asset;
use Statamic\Events\AssetUploaded;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;

class AssetUploadedListener
{
    public function handle(AssetUploaded $event): void
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
