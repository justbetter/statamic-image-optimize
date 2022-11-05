<?php

namespace JustBetter\ImageOptimize\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\ImageOptimize\Events\ImagesResizedEvent;
use Statamic\facades\Asset;
use JustBetter\ImageOptimize\Actions\ResizeImages;

class ResizeImagesJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
    ) {
        $this->onConnection(config('image-optimize.default_queue_connection'));
        $this->onQueue(config('image-optimize.default_queue_name'));
    }

    public function handle(): void
    {
        $assets = Asset::all();
        new ResizeImages($assets);
        ImagesResizedEvent::dispatch();
    }
}
