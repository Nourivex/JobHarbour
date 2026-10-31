import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/home.js',
                'resources/js/pages/catalog.js',
                'resources/js/pages/apply.js',
                'resources/js/pages/my-dock.js',
                'resources/js/pages/profile.js',
                'resources/js/pages/recruiter.js',
                'resources/js/pages/auth.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
