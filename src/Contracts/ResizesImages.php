<?php

namespace JustBetter\ImageOptimize\Contracts;

use Illuminate\Bus\Batch;

interface ResizesImages
{
    public function resize(bool $forceAll = false): Batch;
}
