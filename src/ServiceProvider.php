<?php

namespace JustBetter\ImageOptimize;

use Illuminate\Support\Facades\Event;
use JustBetter\ImageOptimize\Actions\ResizeImage;
use JustBetter\ImageOptimize\Actions\ResizeImages;
use JustBetter\ImageOptimize\Commands\ResizeImagesCommand;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use Statamic\Events\AssetUploaded;
use Statamic\Events\AssetReuploaded;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\OptimizeAssets::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php'
    ];

    protected $scripts = [
        __DIR__ . '/../dist/js/statamic-image-optimize.js'
    ];

    public function register(): void
    {
        $this->registerConfig()
            ->registerActions();
    }


    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/image-optimize.php', 'image-optimize');

        return $this;
    }

    protected function registerActions(): static
    {
        ResizeImage::bind();
        ResizeImages::bind();

        return $this;
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'statamic-image-optimize');

        $this->bootPublishables()
            ->bootEvents()
            ->bootCommands()
            ->bootNav()
            ->handleTranslations();
    }



    public function bootEvents(): static
    {
        Event::listen([AssetUploaded::class, AssetReuploaded::class], AssetUploadedListener::class);

        return $this;
    }

    protected function bootCommands(): static
    {
        $this->commands([
            ResizeImagesCommand::class
        ]);

        return $this;
    }

    protected function bootPublishables(): static
    {
        $this->publishes([
            __DIR__.'/../config/image-optimize.php' => config_path('image-optimize.php'),
        ], 'config');

        return $this;
    }

    protected function bootNav(): static
    {
        Nav::extend(function ($nav) {
            $nav->create('Image Optimize')
                ->section('Tools')
                ->route('statamic-image-optimize.index')
                ->icon('collection');
        });

        return $this;
    }

    protected function handleTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'image-optimize');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/statamic-image-optimize'),
        ], 'image-optimize-translations');

        return $this;
    }
}
