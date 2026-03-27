<?php

namespace JustBetter\ImageOptimize\Tests\Actions;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use JustBetter\ImageOptimize\Actions\ResizeImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use JustBetter\ImageOptimize\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;

class ResizeImageTest extends TestCase
{
    #[Test]
    public function it_can_resize_image(): void
    {
        Event::fake();

        $asset = $this->createAsset();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset, 100, 100);

        $this->assertEquals(100, $asset->meta('width'));
        $this->assertEquals(63, $asset->meta('height'));
        $this->assertEquals(1, $asset->meta('data.image-optimized'));

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
