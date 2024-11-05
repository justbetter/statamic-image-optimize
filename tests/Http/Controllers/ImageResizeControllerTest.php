<?php

namespace JustBetter\ImageOptimize\Tests\Http\Controllers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\BatchFake;
use JustBetter\ImageOptimize\Contracts\ResizesImages;
use JustBetter\ImageOptimize\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ImageResizeControllerTest extends TestCase
{
    #[Test]
    public function it_can_resize_images(): void
    {
        $fakeBatch = new BatchFake('::batch-id::', '::name::', 0, 0, 0, [], [], now()->toImmutable());

        $this->mock(ResizesImages::class, function (MockInterface $mock) use ($fakeBatch): void {
            $mock
                ->shouldReceive('resize')
                ->andReturn($fakeBatch);
        });

        $this
            ->withoutMiddleware()
            ->get(route('statamic.cp.statamic-image-optimize.resize-images'))
            ->assertSuccessful()
            ->assertJson([
                'imagesOptimized' => true,
                'batchId' => '::batch-id::',
            ]);
    }

    #[Test]
    public function it_can_get_resize_images_count(): void
    {
        Bus::fake();

        $this->createAsset();

        $this
            ->withoutMiddleware()
            ->get(route('statamic.cp.statamic-image-optimize.resize-images-count'))
            ->assertSuccessful()
            ->assertJson([
                'assetsToOptimize' => 1,
                'assetTotal' => 1,
            ]);
    }

    #[Test]
    public function it_can_get_resize_images_count_with_batch(): void
    {
        Bus::fake();

        $this->createAsset();
        $fakeBatch = new BatchFake('::batch-id::', '::name::', 1, 0, 0, [], [], now()->toImmutable());

        Bus::spy()
            ->shouldReceive('findBatch')
            ->andReturn($fakeBatch);

        $this
            ->withoutMiddleware()
            ->get(route('statamic.cp.statamic-image-optimize.resize-images-count', ['batchId' => '::batch-id::']))
            ->assertSuccessful()
            ->assertJson([
                'assetsToOptimize' => 0,
                'assetTotal' => 1,
            ]);
    }
}
