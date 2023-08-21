<?php

namespace JustBetter\ImageOptimize\Actions;

use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use JustBetter\ImageOptimize\Contracts\ResizesImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use League\Glide\Manipulators\Size;
use Statamic\Assets\Asset;

class ResizeImage implements ResizesImage
{
    public function resize(Asset $asset, ?int $width = null, ?int $height = null): void
    {
        $width ??= (int) config('image-optimize.default_resize_width');
        $height ??= (int) config('image-optimize.default_resize_height');

        // Prevents exceptions occurring when resizing non-compatible filetypes like SVG.
        try {
            $path = $this->event->asset->resolvedPath();
            $orientedImage = Image::make($path)->orientate();

            $image = (new Size())->runMaxResize($orientedImage->stream(), $width, $height);

            $asset->disk()->filesystem()->put($orientedImage, $image->encode());

            $asset->merge(['image-optimized' => '1']);

            $asset->save();
            $asset->meta();
        } catch (NotReadableException) {
            return;
        }

        ImageResizedEvent::dispatch();
    }

    public static function bind(): void
    {
        app()->singleton(ResizesImage::class,static::class);
    }
}
