import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin/stickers-page.js',
                'resources/js/admin/campus-map.js',
                'resources/css/admin/stickers.css',
                'resources/css/admin/campus-map.css'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    optimizeDeps: {
        include: [
            'laravel-echo',
            'pusher-js'
        ]
    },
    server: {
        https: false,
        hmr: {
            host: 'localhost',
        },
    },
});
