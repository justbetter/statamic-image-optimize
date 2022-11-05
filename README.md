# Image Optimize

> Image optimization after upload

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require justbetter/statamic-image-optimize
```

## Config

### Publish

```
php artisan vendor:publish --provider="JustBetter\ImageOptimize\ServiceProvider"
```

### Settings

It's possible to change to default resize width and height by overriding the config file and changing the parameters within.


## Commands
```
php artisan justbetter:optimize:images
```

By running this command you can recursively optimize all the images in the assets folder.

## Features

- After an image is uploaded an event will trigger to optimize the image.
The event optimizes the images and resizes it to a specified size, this is being controlled by the config file.


- By using the resize images command you can recursively optimize all the images in the assets folder.


- Added an action in the CP that allows you to select assets and trigger the optimize job manually.
