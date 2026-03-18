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
    public function it_can_start_a_batch_for_remaining_images(): void
    {
        $fakeBatch = new BatchFake('::batch-id::', '::name::', 0, 0, 0, [], [], now()->toImmutable());

        $this->mock(ResizesImages::class, function (MockInterface $mock) use ($fakeBatch): void {
            $mock
                ->shouldReceive('resize')
                ->with(false)
                ->andReturn($fakeBatch);
        });

        $this
            ->withoutMiddleware()
            ->post(route('statamic.cp.statamic-image-optimize.batches.start'), ['scope' => 'remaining'])
            ->assertSuccessful()
            ->assertJson([
                'batchId' => '::batch-id::',
            ]);
    }

    #[Test]
    public function it_can_start_a_batch_for_all_images(): void
    {
        $fakeBatch = new BatchFake('::batch-id::', '::name::', 0, 0, 0, [], [], now()->toImmutable());

        $this->mock(ResizesImages::class, function (MockInterface $mock) use ($fakeBatch): void {
            $mock
                ->shouldReceive('resize')
                ->with(true)
                ->andReturn($fakeBatch);
        });

        $this
            ->withoutMiddleware()
            ->post(route('statamic.cp.statamic-image-optimize.batches.start'), ['scope' => 'all'])
            ->assertSuccessful()
            ->assertJson([
                'batchId' => '::batch-id::',
            ]);
    }

    #[Test]
    public function it_can_get_batch_status(): void
    {
        $fakeBatch = new BatchFake('::batch-id::', '::name::', 2, 1, 1, [], [], now()->toImmutable());

        Bus::spy()
            ->shouldReceive('findBatch')
            ->andReturn($fakeBatch);

        $this
            ->withoutMiddleware()
            ->get(route('statamic.cp.statamic-image-optimize.batches.status', ['batchId' => '::batch-id::']))
            ->assertSuccessful()
            ->assertJson([
                'batchId' => '::batch-id::',
                'total' => 2,
                'pending' => 1,
                'failed' => 1,
                'processed' => 0,
            ]);
    }

    #[Test]
    public function it_returns_404_when_batch_is_missing(): void
    {
        Bus::spy()
            ->shouldReceive('findBatch')
            ->andReturn(null);

        $this
            ->withoutMiddleware()
            ->get(route('statamic.cp.statamic-image-optimize.batches.status', ['batchId' => '::missing::']))
            ->assertNotFound()
            ->assertJson([
                'batchId' => '::missing::',
                'missing' => true,
            ]);
    }
}
