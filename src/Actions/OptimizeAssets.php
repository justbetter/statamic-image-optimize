<?php

namespace JustBetter\ImageOptimize\Actions;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Actions\Action;
use Statamic\Assets\Asset;

class OptimizeAssets extends Action
{
    public static function title(): string
    {
        return __('image-optimize::messages.optimize');
    }

    // @phpstan-ignore-next-line
    public function visibleTo($item): bool
    {
        return $item instanceof Asset;
    }

    // @phpstan-ignore-next-line
    public function visibleToBulk($items): bool
    {
        return $this->visibleTo($items->first());
    }

    // @phpstan-ignore-next-line
    public function run($assets, $values): void
    {
        // @phpstan-ignore-next-line
        collect($assets ?? [])
            ->filter(fn (mixed $asset): bool => $asset instanceof Asset)
            ->each(function (Asset $asset): void {
                ResizeImageJob::dispatch($asset);
            });
    }
}
