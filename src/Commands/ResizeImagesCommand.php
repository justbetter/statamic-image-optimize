<?php

namespace JustBetter\ImageOptimize\Commands;

use Illuminate\Console\Command;
use JustBetter\ImageOptimize\Actions\ResizeImages;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;
use Statamic\Facades\Asset;

class ResizeImagesCommand extends Command
{
    protected $signature = 'justbetter:optimize:images {--forceAll}';

    protected $description = 'Optimize all images in the asset library';

    public function handle(): int
    {
        $forceAll = $this->option('forceAll');

        if ($this->getOutput()->isVerbose()) {
            $this->output->info("Starting the resize images job");

            if ($forceAll) {
                $this->output->text("Forcing to optimize all images");
            }

            $assets = Asset::all();
            $batch = (new ResizeImages($forceAll ? $assets : $assets->whereNull('image-optimized')))->resizeImages();

            $progress = $this->output->createProgressBar($batch->totalJobs);
            $progress->start();

            while($batch->pendingJobs && !$batch->finished() && !$batch->cancelled()) {
                $batch = $batch->fresh();
                $progress->setProgress($batch->processedJobs());
            }

            $progress->finish();

            $this->output->newLine(2);
            $this->output->success("All images have been resized");
        } else {
            ResizeImagesJob::dispatch($forceAll);
        }

        return static::SUCCESS;
    }
}
