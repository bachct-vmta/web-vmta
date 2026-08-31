import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/hls-bootstrap.js',
                'packages/Site/resources/css/app.css',
                'packages/Site/resources/js/app.js',
                'packages/Chatbot/resources/js/chatbot-widget.js',
                'packages/Report/resources/js/charts.js',
            ],
            refresh: [
                'resources/views/**',
                'packages/*/resources/views/**',
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
