import ResizeForm from './components/cp/image-resize/ResizeForm';

Statamic.booting(() => {
    Statamic.component('justbetter-statamic-optimize-image-form', ResizeForm);
});