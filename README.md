# Image Optimize

> Image optimization after upload

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require justbetter/image-optimize
```

## Config

### Publish

```
php artisan vendor:publish --provider="JustBetter\ImageOptimize\ServiceProvider"
```

### Settigns

It's possible to change to default resize width and height by overriding the config file and changing the parameters within.


## Features

After an image is uploaded an event will trigger to optimize the image.
The event optimizes the images and resizes it to a maximum of 1600px x 1600px
