<?php

namespace JustBetter\ImageOptimize\Tests\Jobs;

use JustBetter\ImageOptimize\Contracts\ResizesImage;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use JustBetter\ImageOptimize\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ResizeImageJobTest extends TestCase
{
    #[Test]
    public function it_can_resize_an_image(): void
    {
        $asset = $this->createAsset();

        $this->mock(ResizesImage::class, function (MockInterface $mock): void {
            $mock
                ->shouldReceive('resize')
                ->once();
        });

        ResizeImageJob::dispatch($asset, 100, 100);
    }
}
