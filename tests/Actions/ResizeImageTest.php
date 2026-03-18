<?php

namespace JustBetter\ImageOptimize\Tests\Actions;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use JustBetter\ImageOptimize\Actions\ResizeImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use JustBetter\ImageOptimize\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;

class ResizeImageTest extends TestCase
{
    #[Test]
    public function it_resizes_within_max_dimensions_without_changing_aspect_ratio(): void
    {
        Event::fake();

        config()->set('image-optimize.max_resize_width', 2560);
        config()->set('image-optimize.max_resize_height', 2560);

        $manager = ImageManager::gd();
        $contents = $manager->create(6000, 4000)->encodeByExtension('png')->toString();

        /** @var Asset $asset */
        $asset = $this->createAsset('landscape.png');
        $asset->disk()->filesystem()->put($asset->path(), $contents);
        $asset->meta();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        $resized = $manager->read($asset->resolvedPath());

        $this->assertSame(2560, $resized->width());
        $this->assertSame(1707, $resized->height());

        $this->assertSame(2560, $asset->meta('width'));
        $this->assertSame(1707, $asset->meta('height'));
        $this->assertSame(1, (int) $asset->meta('data.image-optimized'));

        Event::assertDispatched(ImageResizedEvent::class);
    }

    #[Test]
    public function it_resizes_portrait_images_within_max_dimensions_without_changing_aspect_ratio(): void
    {
        Event::fake();

        config()->set('image-optimize.max_resize_width', 2560);
        config()->set('image-optimize.max_resize_height', 2560);

        $manager = ImageManager::gd();
        $contents = $manager->create(4000, 6000)->encodeByExtension('png')->toString();

        /** @var Asset $asset */
        $asset = $this->createAsset('portrait.png');
        $asset->disk()->filesystem()->put($asset->path(), $contents);
        $asset->meta();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        $resized = $manager->read($asset->resolvedPath());

        $this->assertSame(1707, $resized->width());
        $this->assertSame(2560, $resized->height());

        $this->assertSame(1707, $asset->meta('width'));
        $this->assertSame(2560, $asset->meta('height'));
        $this->assertSame(1, (int) $asset->meta('data.image-optimized'));

        Event::assertDispatched(ImageResizedEvent::class);
    }

    #[Test]
    public function it_can_ignore_excluded_containers(): void
    {
        Event::fake();

        config()->set('image-optimize.excluded_containers', [
            'test_container',
        ]);

        $asset = $this->createAsset();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        Event::assertNotDispatched(ImageResizedEvent::class);
    }

    #[Test]
    public function it_can_catch_exceptions(): void
    {
        Event::fake();

        Storage::disk('assets')->put('broken.png', 'this-is-not-a-valid-image');

        $asset = new Asset;
        $asset->container($this->assetContainer());
        $asset->path('broken.png');
        $asset->save();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        Event::assertNotDispatched(ImageResizedEvent::class);
    }

    #[Test]
    public function it_can_skip_missing_assets(): void
    {
        Event::fake();

        $asset = new Asset;
        $asset->container($this->assetContainer());
        $asset->path('does-not-exist.png');
        $asset->save();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        Event::assertNotDispatched(ImageResizedEvent::class);
    }
}
