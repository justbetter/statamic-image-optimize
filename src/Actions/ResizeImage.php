<?php

namespace JustBetter\ImageOptimize\Actions;

use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Exceptions\RuntimeException;
use Intervention\Image\ImageManager;
use JustBetter\ImageOptimize\Contracts\ResizesImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use Statamic\Assets\Asset;

class ResizeImage implements ResizesImage
{
    public function resize(Asset $asset): void
    {
        if (! $asset->exists() ||
            ! $asset->isImage() ||
            in_array($asset->containerHandle(), config('image-optimize.excluded_containers'))
        ) {
            return;
        }

        $maxWidth = (int) config('image-optimize.max_resize_width');
        $maxHeight = (int) config('image-optimize.max_resize_height');

        try {
            $manager = ImageManager::gd();

            $image = $manager->read($asset->resolvedPath());

            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // @codeCoverageIgnoreStart
            if ($originalWidth <= 0 || $originalHeight <= 0) {
                return;
            }
            // @codeCoverageIgnoreEnd

            $maxWidth = $maxWidth > 0 ? $maxWidth : $originalWidth;
            $maxHeight = $maxHeight > 0 ? $maxHeight : $originalHeight;

            $widthScale = $maxWidth / $originalWidth;
            $heightScale = $maxHeight / $originalHeight;

            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                if ($widthScale <= $heightScale) {
                    $image = $image->scaleDown($maxWidth, null);
                } else {
                    $image = $image->scaleDown(null, $maxHeight);
                }
            }

            $extension = $asset->extension();

            $asset->disk()->filesystem()->put(
                $asset->path(),
                $image->encodeByExtension($extension)
            );

            $asset->merge(['image-optimized' => '1']);

            $asset->save();
            $asset->meta();
        } catch (RuntimeException|DriverException) {
            return;
        }

        ImageResizedEvent::dispatch();
    }

    public static function bind(): void
    {
        app()->singleton(ResizesImage::class, static::class);
    }
}
