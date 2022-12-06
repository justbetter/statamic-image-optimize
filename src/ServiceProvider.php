<?php

namespace JustBetter\ImageOptimize;

use Illuminate\Support\Facades\Event;
use JustBetter\ImageOptimize\Commands\ResizeImagesCommand;
use Statamic\Providers\AddonServiceProvider;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use JustBetter\ImageOptimize\Listeners\AssetReuploadedListener;
use Statamic\Events\AssetUploaded;
use Statamic\Events\AssetReuploaded;

class ServiceProvider extends AddonServiceProvider
{
    protected $actions = [
        Actions\OptimizeAssets::class,
    ];

    public function boot() : void
    {
        parent::boot();

        $this->bootPublishables()
            ->bootEvents()
            ->bootCommands()
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

    protected function handleTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'image-optimize');

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/statamic-image-optimize'),
        ], 'image-optimize-translations');
    }
}
