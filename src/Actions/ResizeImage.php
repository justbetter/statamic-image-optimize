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
    public function resize(Asset $asset, ?int $width = null, ?int $height = null): void
    {
        if (! $asset->exists() ||
            ! $asset->isImage() ||
            in_array($asset->containerHandle(), config('image-optimize.excluded_containers'))
        ) {
            return;
        }

        $width ??= (int) config('image-optimize.default_resize_width');
        $height ??= (int) config('image-optimize.default_resize_height');

        try {
            $manager = ImageManager::gd();

            $image = $manager->read($asset->resolvedPath())
                ->scaleDown($width, $height);

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
