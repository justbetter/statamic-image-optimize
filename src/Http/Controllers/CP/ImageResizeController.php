<?php

namespace JustBetter\ImageOptimize\Http\Controllers\CP;

use App\Http\Controllers\Controller;
use JustBetter\ImageOptimize\Jobs\ResizeImagesJob;
use Statamic\Assets\AssetCollection;
use Statamic\Assets\AssetRepository;
use Statamic\Facades\Asset;
use Illuminate\Support\Facades\Queue;

class ImageResizeController extends Controller
{
    public function index() : string
    {
        $allAssets = Asset::all();
        $assets = $allAssets->whereNull('image-optimized');

        return view('statamic-image-optimize::cp.image-resize.index', [
            'title' => 'JustBetter Image Optimize',
            'total_assets' => $allAssets->count(),
            'unoptimized_assets' => $assets->count(),
        ]);
    }

    public function resizeImages(): array
    {
        ResizeImagesJob::dispatch();

        return ['imagesOptimized' => true];
    }

    public function resizeAllImages(): array
    {
        $allAssets = Asset::all()->whereNotNull('image-optimized');
        $allAssets->lazy()->each(fn($asset) => $asset->data('image-optimized', null)->save());

        ResizeImagesJob::dispatch();

        return ['imagesOptimized' => true];
    }

    public function resizeImagesJobCount(): array
    {
        $allAssets = Asset::all();
        $assets = $allAssets->whereNull('image-optimized');

        return ['assetsToOptimize' => $assets->count(), 'assetTotal' => $allAssets->count()];
    }
}