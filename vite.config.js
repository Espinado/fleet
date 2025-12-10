import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Админка
                'resources/css/app.css',
                'resources/js/app.js',

                // Driver App
                'resources/driver/css/app.css',
                'resources/driver/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
