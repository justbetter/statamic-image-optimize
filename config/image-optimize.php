<?php

return [
    // Set the max resize width in pixels
    'max_resize_width' => env('IMAGE_OPTIMIZE_WIDTH', 2560),

    // Set the max resize height in pixels
    'max_resize_height' => env('IMAGE_OPTIMIZE_HEIGHT', 2560),

    // Set the default queue name
    'default_queue_name' => env('IMAGE_OPTIMIZE_QUEUE_NAME', 'default'),

    // Set the default queue connection
    'default_queue_connection' => env('IMAGE_OPTIMIZE_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),

    // The following mime types will be used to optimize images
    'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],

    // You can exclude containers from optimization entirely here
    'excluded_containers' => [],
];
