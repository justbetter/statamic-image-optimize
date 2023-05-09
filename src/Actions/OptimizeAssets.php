<?php

namespace JustBetter\ImageOptimize\Actions;

use JustBetter\ImageOptimize\Jobs\ResizeImageJob;
use Statamic\Actions\Action;
use Statamic\Contracts\Assets\Asset;

class OptimizeAssets extends Action
{
    public static function title()
    {
        return __('image-optimize::messages.optimize');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        return $this->visibleTo($items->first());
    }

    public function run($assets, $values)
    {
        collect($assets)
            ->each(function ($asset) {
                if ($asset instanceof Asset && $asset->isImage()) {
                    ResizeImageJob::dispatch($asset);
                }
            });
    }
}
