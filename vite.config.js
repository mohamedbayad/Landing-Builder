import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        // Set limit to 5000 kB (default is typically 500 kB)
        chunkSizeWarningLimit: 5000,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/editor.js', 'resources/js/dashboard.js', 'resources/js/online-users.js'],
            refresh: true,
        }),
    ],
});
