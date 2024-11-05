<?php

namespace JustBetter\ImageOptimize\Tests\Jobs;

use JustBetter\ImageOptimize\Contracts\ResizesImages;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;
use JustBetter\ImageOptimize\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ResizeImagesJobTest extends TestCase
{
    #[Test]
    #[DataProvider('cases')]
    public function it_can_resize_images(bool $forceAll): void
    {
        $this->mock(ResizesImages::class, function (MockInterface $mock) use ($forceAll): void {
            $mock
                ->shouldReceive('resize')
                ->with($forceAll)
                ->once();
        });

        ResizeImagesJob::dispatch($forceAll);
    }

    public static function cases(): array
    {
        return [
            'true' => [
                'forceAll' => true,
            ],
            'false' => [
                'forceAll' => false,
            ],
        ];
    }
}
