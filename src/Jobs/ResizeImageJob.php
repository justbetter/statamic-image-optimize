<?php

namespace JustBetter\ImageOptimize\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Statamic\Assets\Asset;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use JustBetter\ImageOptimize\Actions\ResizeImage;
use Illuminate\Bus\Batchable;

class ResizeImageJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use Batchable;

    public int $width;
    public int $height;

    public function __construct(
        public Asset $asset,
    ) {
        $this->width = config('image-optimize.default_resize_width');
        $this->height = config('image-optimize.default_resize_height');
        $this->onConnection(config('image-optimize.default_queue_connection'));
        $this->onQueue(config('image-optimize.default_queue_name'));
    }

    public function handle(): void
    {
        new ResizeImage($this->asset, $this->width, $this->height);
        ImageResizedEvent::dispatch();
    }

    public function uniqueId(): string
    {
        return $this->asset->id();
    }
}
