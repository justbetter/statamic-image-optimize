<?php

namespace JustBetter\ImageOptimize\Tests\Actions;

use Illuminate\Support\Facades\Event;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;
use JustBetter\ImageOptimize\Actions\ResizeImage;
use JustBetter\ImageOptimize\Events\ImageResizedEvent;
use JustBetter\ImageOptimize\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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

        Image::spy()
            ->shouldReceive('make')
            ->andThrow(NotReadableException::class);

        $asset = $this->createAsset();

        /** @var ResizeImage $action */
        $action = app(ResizeImage::class);
        $action->resize($asset);

        Event::assertNotDispatched(ImageResizedEvent::class);
    }
}
