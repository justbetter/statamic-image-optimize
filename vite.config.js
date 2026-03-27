import { defineConfig } from 'vite';
import statamic from '@statamic/cms/vite-plugin';

export default defineConfig({
    plugins: [statamic()],
    build: {
        outDir: 'dist',
        rollupOptions: {
            input: 'resources/js/statamic-image-optimize.js',
            output: {
                entryFileNames: 'js/statamic-image-optimize.js',
                chunkFileNames: 'js/[name]-[hash].js',
                assetFileNames: '[name]-[hash][extname]',
            },
        },
    },
});

