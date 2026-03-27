<?php

namespace JustBetter\ImageOptimize\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use JustBetter\ImageOptimize\Contracts\ResizesImages;
use Statamic\Facades\Asset;
use Throwable;

class ImageResizeController extends Controller
{
    /**
     * @codeCoverageIgnore
     */
    public function index(): Response
    {
        $assets = Asset::all()->getOptimizableAssets(); // @phpstan-ignore-line
        $unoptimizedAssets = $assets->whereNull('image-optimized');
        $databaseConnected = true;

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            $databaseConnected = false;
        }

        return Inertia::render('statamic-image-optimize::ImageResize/Index', [
            'totalAssets' => $assets->count(),
            'unoptimizedAssets' => $unoptimizedAssets->count(),
            'canOptimize' => $databaseConnected,
            'startBatchUrl' => cp_route('statamic-image-optimize.batches.start'),
            'batchStatusUrlTemplate' => cp_route('statamic-image-optimize.batches.status', ['batchId' => '__BATCH_ID__']),
        ]);
    }

    public function startBatch(ResizesImages $resizesImages): JsonResponse
    {
        $validated = request()->validate([
            'scope' => ['required', 'string', 'in:remaining,all'],
        ]);

        $batch = $resizesImages->resize($validated['scope'] === 'all');

        return response()->json([
            'batchId' => $batch->id,
        ]);
    }

    public function batchStatus(string $batchId): JsonResponse
    {
        $batch = Bus::findBatch($batchId);

        if ($batch) {
            $processedJobs = max(0, $batch->totalJobs - $batch->pendingJobs - $batch->failedJobs);

            return response()->json([
                'batchId' => $batch->id,
                'total' => $batch->totalJobs,
                'pending' => $batch->pendingJobs,
                'failed' => $batch->failedJobs,
                'processed' => $processedJobs,
                'finished' => $batch->finished(),
            ]);
        }

        return response()->json([
            'batchId' => $batchId,
            'missing' => true,
        ], 404);
    }
}
