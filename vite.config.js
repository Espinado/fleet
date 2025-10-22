import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    server: {
        host: 'fleet.test',          // 👈 указываем домен Laragon
        port: 5173,
        https: false,                // (если нет SSL)
        hmr: {
            host: 'fleet.test',      // 👈 чтобы hot reload знал свой домен
        },
    },
});
