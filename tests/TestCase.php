<?php

namespace JustBetter\ImageOptimize\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageServiceProvider;
use JustBetter\ImageOptimize\ServiceProvider;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use InteractsWithViews;
    use LazilyRefreshDatabase;
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected ?AssetContainer $assetContainer = null;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            ImageServiceProvider::class,
        ]);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');

        $app['config']->set('statamic.assets.image_manipulation.driver', 'gd');

        $app['config']->set('filesystems.disks.assets', [
            'driver' => 'local',
            'root' => $this->fixturePath('assets'),
        ]);

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('queue.batching.database', 'testbench');
        $app['config']->set('queue.failed.database', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function assetContainer(): AssetContainer
    {
        if ($this->assetContainer === null) {
            $this->assetContainer = (new AssetContainer) // @phpstan-ignore-line
                ->handle('test_container')
                ->disk('assets')
                ->save();
        }

        return $this->assetContainer;
    }

    protected function fixturePath(string $file = ''): string
    {
        $path = __DIR__.'/__fixtures__';

        if (strlen($file) > 0) {
            $path .= '/'.$file;
        }

        return $path;
    }

    protected function createAsset(string $filename = 'test.png'): Asset
    {
        Storage::disk('assets')->put($filename, file_get_contents($this->fixturePath('uploads/test.png'))); // @phpstan-ignore-line

        /** @var Asset $asset */
        $asset = (new Asset)->container($this->assetContainer())->path($filename); // @phpstan-ignore-line
        $asset->save();

        return $asset;
    }
}
