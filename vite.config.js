import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
            port: 5173,
        },
    },

    plugins: [
        laravel({
            input: [
                // Admin app
                'resources/css/app.css',
                'resources/js/app.js',

                // Driver app (твоя структура!)
                'resources/driver/css/app.css',
                'resources/driver/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
