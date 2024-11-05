<?php

namespace JustBetter\ImageOptimize\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\ImageOptimize\Contracts\ResizesImage;
use Statamic\Assets\Asset;

class ResizeImageJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public Asset $asset,
        public ?int $width = null,
        public ?int $height = null,
    ) {
        $this->onConnection(config('image-optimize.default_queue_connection'));
        $this->onQueue(config('image-optimize.default_queue_name'));
    }

    public function handle(ResizesImage $contract): void
    {
        $contract->resize($this->asset, $this->width, $this->height);
    }

    public function uniqueId(): string
    {
        return $this->asset->id();
    }
}
