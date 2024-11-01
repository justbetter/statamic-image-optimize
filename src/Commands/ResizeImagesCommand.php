<?php

namespace JustBetter\ImageOptimize\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JustBetter\ImageOptimize\Contracts\ResizesImages;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;

/** @codeCoverageIgnore */
class ResizeImagesCommand extends Command
{
    protected $signature = 'justbetter:optimize:images {--forceAll}';

    protected $description = 'Optimize all images in the asset library';

    public function handle(ResizesImages $resizesImages): int
    {
        /** @var bool $forceAll */
        $forceAll = $this->option('forceAll');

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('You need an active database connection in order to use the optimize addon.');

            return static::FAILURE;
        }

        if ($this->getOutput()->isVerbose()) {
            $this->line('Starting the resize images job');

            if ($forceAll) {
                $this->comment('Forcing to optimize all images');
            }

            $batch = $resizesImages->resize($forceAll);

            $progress = $this->output->createProgressBar($batch->totalJobs);
            $progress->start();

            while ($batch->pendingJobs && ! $batch->finished() && ! $batch->cancelled()) {
                $batch = $batch->fresh();
                $progress->setProgress($batch->processedJobs());
            }

            $progress->finish();

            $this->output->newLine(2);
            $this->info('All images have been resized');
        } else {
            ResizeImagesJob::dispatch($forceAll);
            $this->info('Jobs dispatched');
        }

        return static::SUCCESS;
    }
}
