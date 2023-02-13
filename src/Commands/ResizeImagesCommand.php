<?php

namespace JustBetter\ImageOptimize\Commands;

use Illuminate\Console\Command;
use JustBetter\ImageOptimize\Actions\ResizeImages;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;
use Statamic\Facades\Asset;

class ResizeImagesCommand extends Command
{
    protected $signature = 'justbetter:optimize:images';

    protected $description = 'Optimize all images in the asset library';

    public function handle(): int
    {
        if ($this->getOutput()->isVerbose()) {
            $this->output->info("Starting the resize images job");

            $assets = Asset::all();

            $progress = $this->output->createProgressBar($assets->count());
            $progress->start();

            new ResizeImages($assets, $progress);

            $progress->finish();

            $this->output->newLine(2);
            $this->output->success("All images have been resized");
        } else {
            ResizeImagesJob::dispatch();
        }

        return static::SUCCESS;
    }
}
