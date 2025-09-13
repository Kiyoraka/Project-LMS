import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, when referenced
                    // in Single File Components, to point to the Laravel web server.
                    // Setting this to `null` allows the Laravel plugin to instead re-write these
                    // URLs to point to the Vite server instead.
                    base: null,
                    // The Laravel plugin will parse absolute URLs and treat them as
                    // absolute paths on the local file system. This is problematic when
                    // using Laravel routes with the ziggy package for example. Setting
                    // this to `false` will prevent the plugin from converting these URLs.
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
