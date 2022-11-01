<?php

namespace JustBetter\ImageOptimize;

use Illuminate\Support\Facades\Event;
use Statamic\Providers\AddonServiceProvider;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use Statamic\Events\AssetUploaded;

class ServiceProvider extends AddonServiceProvider
{
    public function boot() : void
    {
        $this->bootPublishables()
            ->bootEvents();
    }

    public function register() : void
    {
        $this->bootConfig();
    }

    public function bootEvents() : self
    {
        Event::listen(AssetUploaded::class, AssetUploadedListener::class);

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
}
