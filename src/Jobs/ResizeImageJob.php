<?php

namespace JustBetter\ImageOptimize\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use League\Glide\Manipulators\Size;
use Statamic\Assets\Asset;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;

class ResizeImageJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public Asset $asset,
        public int $width = 1680,
        public int $height = 1680,
    ) {
    }

    public function handle(): void
    {
        // Prevents exceptions occurring when resizing non-compatible filetypes like SVG.
        try {
            (new Size())
                ->runMaxResize(Image::make($this->asset->resolvedPath()), $this->width, $this->height)
                ->save();

            $this->asset->save();
            $this->asset->meta();
        } catch (NotReadableException) {
            return;
        }

        ImageResizedEvent::dispatch();
    }

    public function uniqueId(): string
    {
        return $this->asset->id();
    }
}
