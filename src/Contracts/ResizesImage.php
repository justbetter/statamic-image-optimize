<?php

namespace JustBetter\ImageOptimize\Contracts;

use Statamic\Assets\Asset;

interface ResizesImage
{
    public function resize(Asset $asset, ?int $width = null, ?int $height = null): void;
}
