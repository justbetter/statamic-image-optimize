<?php

namespace JustBetter\ImageOptimize;

use Illuminate\Support\Facades\Event;
use JustBetter\ImageOptimize\Commands\ResizeImagesCommand;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use Statamic\Events\AssetUploaded;
use Statamic\Events\AssetReuploaded;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\OptimizeAssets::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php'
    ];

    public $scripts = [
        __DIR__ . '/../dist/js/statamic-image-optimize.js'
    ];

    public function boot() : void
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'statamic-image-optimize');

        $this->bootPublishables()
            ->bootEvents()
            ->bootCommands()
            ->bootNav()
            ->handleTranslations();
    }

    public function register() : void
    {
        $this->bootConfig();
    }

    public function bootEvents() : self
    {
        Event::listen([AssetUploaded::class, AssetReuploaded::class], AssetUploadedListener::class);

        return $this;
    }

    public function bootCommands() : self
    {
        $this->commands([
            ResizeImagesCommand::class
        ]);

        return $this;
    }

    public function bootConfig() : self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/image-optimize.php', 'image-optimize');

        return $this;
    }

    public function bootPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../config/image-optimize.php' => config_path('image-optimize.php'),
        ], 'config');

        return $this;
    }

    public function bootNav() : self
    {
        Nav::extend(function ($nav) {
            $nav->create('Image Optimize')
                ->section('Tools')
                ->route('statamic-image-optimize.index')
                ->icon('collection');
        });

        return $this;
    }

    protected function handleTranslations() : self
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'image-optimize');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/statamic-image-optimize'),
        ], 'image-optimize-translations');

        return $this;
    }
}
