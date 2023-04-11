<?php

namespace JustBetter\ImageOptimize\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\ImageOptimize\Events\ImagesResizedEvent;
use Statamic\Facades\Asset;
use JustBetter\ImageOptimize\Actions\ResizeImages;

class ResizeImagesJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public array $mimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp'
    ];

    public function __construct(
        public $forceAll = false
    ) {
        $this->onConnection(config('image-optimize.default_queue_connection'));
        $this->onQueue(config('image-optimize.default_queue_name'));
    }

    public function handle(): Batch
    {
        $assets = Asset::all()->whereIn('mime_type', $this->mimeTypes);
        $batch = (new ResizeImages($this->forceAll ? $assets : $assets->whereNull('image-optimized')))->resizeImages();
        ImagesResizedEvent::dispatch();

        return $batch;
    }
}
