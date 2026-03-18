import ImageResizeIndex from './Pages/ImageResize/Index.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('statamic-image-optimize::ImageResize/Index', ImageResizeIndex);
});