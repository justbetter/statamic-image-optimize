<?php

namespace JustBetter\ImageOptimize\Http\Controllers\CP;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use JustBetter\ImageOptimize\Contracts\ResizesImages;
use Statamic\Facades\Asset;

class ImageResizeController extends Controller
{
    /**
     * @codeCoverageIgnore
     */
    public function index(): Factory|View|string
    {
        $assets = Asset::all()->getOptimizableAssets(); // @phpstan-ignore-line
        $unoptimizedAssets = $assets->whereNull('image-optimized');
        $databaseConnected = true;

        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $databaseConnected = false;
        }

        return view('statamic-image-optimize::cp.image-resize.index', [
            'title' => 'JustBetter Image Optimize',
            'total_assets' => $assets->count(),
            'unoptimized_assets' => $unoptimizedAssets->count(),
            'can_optimize' => $databaseConnected,
        ]);
    }

    public function resizeImages(ResizesImages $resizesImages, ?string $forceAll = null): JsonResponse
    {
        $batch = $resizesImages->resize($forceAll !== null);

        return response()->json([
            'imagesOptimized' => true,
            'batchId' => $batch->id,
        ]);
    }

    public function resizeImagesJobCount(?string $batchId = null): JsonResponse
    {
        $batch = $batchId ? Bus::findBatch($batchId) : null;

        if ($batch) {
            return response()->json([
                'assetsToOptimize' => $batch->pendingJobs,
                'assetTotal' => $batch->totalJobs,
            ]);
        }

        $allAssets = Asset::all();
        $assets = $allAssets->getOptimizableAssets() // @phpstan-ignore-line
            ->whereNull('image-optimized');

        return response()->json([
            'assetsToOptimize' => $assets->count(),
            'assetTotal' => $allAssets->count(),
        ]);
    }
}
