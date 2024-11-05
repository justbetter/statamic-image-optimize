<?php

namespace JustBetter\ImageOptimize\Tests\Listeners;

use Illuminate\Support\Facades\Bus;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use JustBetter\ImageOptimize\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\AssetUploaded;

class AssetUploadedListenerTest extends TestCase
{
    #[Test]
    public function it_can_resize_an_image(): void
    {
        Bus::fake();

        $asset = $this->createAsset();

        $event = new AssetUploaded($asset);

        /** @var AssetUploadedListener $listener */
        $listener = app(AssetUploadedListener::class);
        $listener->handle($event);

        Bus::assertDispatched(ResizeImageJob::class);
    }
}
