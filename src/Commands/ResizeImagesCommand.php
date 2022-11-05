<?php

namespace JustBetter\ImageOptimize\Commands;

use Illuminate\Console\Command;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;

class ResizeImagesCommand extends Command
{
    protected $signature = 'justbetter:optimize:images';

    protected $description = 'Optimize all images in the asset library';

    public function handle(): int
    {
        ResizeImagesJob::dispatch();

        return static::SUCCESS;
    }
}
