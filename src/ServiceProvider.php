<?php

namespace JustBetter\ImageOptimize;

use Illuminate\Support\Facades\Event;
use Statamic\Providers\AddonServiceProvider;
use JustBetter\ImageOptimize\Listeners\AssetUploadedListener;
use Statamic\Events\AssetUploaded;

class ServiceProvider extends AddonServiceProvider
{
    public function boot()
    {
        Event::listen(AssetUploaded::class, AssetUploadedListener::class);
    }
}
