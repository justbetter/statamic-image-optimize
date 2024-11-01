<?php

namespace JustBetter\ImageOptimize\Tests\Actions;

use Closure;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use JustBetter\ImageOptimize\Actions\ResizeImages;
use JustBetter\ImageOptimize\Events\ImagesResizedEvent;
use JustBetter\ImageOptimize\Tests\TestCase;
use Orchestra\Testbench\Attributes\WithMigration;
use PHPUnit\Framework\Attributes\Test;

class ResizeImagesTest extends TestCase
{
    #[Test]
    public function it_can_resize_images(): void
    {
        Bus::fake();

        $this->createAsset();

        /** @var ResizeImages $action */
        $action = app(ResizeImages::class);
        $action->resize();

        Bus::assertBatched(fn (PendingBatchFake $batch): bool => $batch->jobs->count() === 1);
    }

    #[Test]
    #[WithMigration('queue')]
    public function it_can_dispatch_events(): void
    {
        Bus::fake();
        Event::fake();

        /** @var ResizeImages $action */
        $action = app(ResizeImages::class);

        $batch = $action->resize();

        /** @var non-empty-array<Closure> $thenCallbacks */
        $thenCallbacks = $batch->then; // @phpstan-ignore-line

        call_user_func($thenCallbacks[0], $batch);

        Event::assertDispatched(ImagesResizedEvent::class);
    }
}
