import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/register.css',
                'resources/css/login.css',
                'resources/css/verify-email.css',
                'resources/css/attendance.css',
                'resources/css/attendance_list.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
