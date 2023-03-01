<?php

namespace JustBetter\ImageOptimize\Http\Controllers\CP;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Bus;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;

class ImageResizeController extends Controller
{
    public array $mimeTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp'
    ];

    public function index() : string
    {
        $assets = Asset::all()->whereIn('mime_type', $this->mimeTypes);
        $unoptimizedAssets = $assets->whereNull('image-optimized');

        return view('statamic-image-optimize::cp.image-resize.index', [
            'title' => 'JustBetter Image Optimize',
            'total_assets' => $assets->count(),
            'unoptimized_assets' => $unoptimizedAssets->count(),
        ]);
    }

    public function resizeImages(string $forceAll = null): array
    {
        $batch = (new ResizeImagesJob($forceAll ? true : false))->handle();
        return ['imagesOptimized' => true, 'batchId' => $batch->id];
    }

    public function resizeImagesJobCount(string $batchId = null): array
    {
        $batch = Bus::findBatch($batchId);

        if ($batch) {
            return ['assetsToOptimize' => $batch->pendingJobs ?? 0, 'assetTotal' => $batch->totalJobs ?? 0];
        }

        $allAssets = Asset::all();
        $assets = $allAssets->whereNull('image-optimized');

        return ['assetsToOptimize' => $assets->count(), 'assetTotal' => $allAssets->count()];
    }
}