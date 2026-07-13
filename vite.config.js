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
                'resources/css/welcome.css',
                'resources/js/welcome.js',
                // Super Admin
                'resources/css/super_admin/dashboard.css',
                'resources/js/super_admin/dashboard.js',
                'resources/css/super_admin/instansi.css',
                'resources/js/super_admin/instansi.js',
                'resources/css/super_admin/pembimbing.css',
                'resources/js/super_admin/pembimbing.js',
                'resources/css/super_admin/peserta.css',
                'resources/js/super_admin/peserta.js',
                'resources/css/super_admin/settings.css',
                'resources/js/super_admin/settings.js',

                // Admin
                'resources/css/admin/dashboard.css',
                'resources/js/admin/dashboard.js',
                'resources/css/admin/interns.css',
                'resources/js/admin/logbooks.js',
                'resources/js/admin/leaves.js',

                // Peserta
                'resources/css/peserta/dashboard.css',
                'resources/js/peserta/dashboard.js',
                'resources/js/peserta/logbook.js',
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
