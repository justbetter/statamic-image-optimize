<?php

namespace JustBetter\ImageOptimize\Actions;

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use League\Glide\Manipulators\Size;
use Statamic\Assets\Asset;

class ResizeImage
{
    public function __construct(
        public Asset $asset,
        public int $width = 1680,
        public int $height = 1680,
    ) {
        $this->resize();
    }

    public function resize(): void
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
    }
}