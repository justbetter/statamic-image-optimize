<?php

namespace JustBetter\ImageOptimize\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\ImageOptimize\Actions\OptimizeAssets;
use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use JustBetter\ImageOptimize\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OptimizeAssetsTest extends TestCase
{
    #[Test]
    public function it_has_a_title(): void
    {
        $this->assertEquals('Optimize', OptimizeAssets::title());
    }

    #[Test]
    public function it_can_be_visible(): void
    {
        $asset = $this->createAsset();

        /** @var OptimizeAssets $action */
        $action = app(OptimizeAssets::class);

        $this->assertTrue(
            $action->visibleTo($asset)
        );
    }

    #[Test]
    public function it_can_be_visible_to_bulk(): void
    {
        $assets = collect([
            $this->createAsset(),
        ]);

        /** @var OptimizeAssets $action */
        $action = app(OptimizeAssets::class);

        $this->assertTrue(
            $action->visibleToBulk($assets)
        );
    }

    #[Test]
    public function it_can_run(): void
    {
        Bus::fake();

        $assets = collect([
            $this->createAsset(),
            'asset',
            false,
            null,
        ]);

        /** @var OptimizeAssets $action */
        $action = app(OptimizeAssets::class);
        $action->run($assets, null);

        Bus::assertDispatched(ResizeImageJob::class, 1);
    }
}
