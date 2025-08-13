import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/pages/courses/manage.js',
                'resources/js/pages/courses/materials.js',
                'resources/js/pages/courses-enrollment.js',
                'resources/js/pages/students/index.js',
                'resources/js/pages/assignments/create.js',
                'resources/js/pages/assignment-show.js',
                'resources/js/pages/registration.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
