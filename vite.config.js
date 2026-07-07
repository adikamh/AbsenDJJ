import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/auth-login.css',
                'resources/js/auth-login.js',
                'resources/css/dashboard-layout.css',
                'resources/js/dashboard-layout.js',
                'resources/css/dashboard-super-admin.css',
                'resources/js/dashboard-super-admin.js',
                'resources/css/dashboard-admin.css',
                'resources/js/dashboard-admin.js',
                'resources/css/dashboard-peserta.css',
                'resources/js/dashboard-peserta.js',
                'resources/css/welcome.css',
                'resources/js/welcome.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
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
