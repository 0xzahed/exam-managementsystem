import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/pages/welcome.js',
                'resources/js/pages/registration.js',
                'resources/js/pages/auth.js',
                'resources/js/pages/assignments-submissions.js',
                'resources/js/pages/assignment-show.js',
                'resources/js/pages/assignments/create.js',
                'resources/js/pages/students/index.js',
                'resources/js/pages/courses/course-create.js',
                'resources/js/pages/courses/manage.js',
                'resources/js/pages/courses/materials.js',
                'resources/js/pages/courses-enrollment.js',
                'resources/js/pages/exams-index.js',
                'resources/js/pages/exams/create.js',
                'resources/js/pages/exams/take.js',
                'resources/js/pages/announcements-index.js',
                'resources/js/pages/profile-student-settings.js',
                'resources/js/pages/profile-instructor-settings.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: undefined,
            }
        }
    }
});
