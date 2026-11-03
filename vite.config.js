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
                'resources/js/pages/job-detail.js',
                'resources/js/pages/apply.js',
                'resources/js/pages/my-dock.js',
                'resources/js/pages/profile.js',
                'resources/js/pages/auth.js',
                'resources/js/pages/recruiter/dashboard.js',
                'resources/js/pages/recruiter/jobs.js',
                'resources/js/pages/recruiter/job-detail.js',
                'resources/js/pages/recruiter/pipeline.js',
                'resources/js/pages/recruiter/interviews.js',
                'resources/js/pages/recruiter/analytics.js',
                'resources/js/pages/recruiter/candidate.js',
                'resources/js/pages/recruiter/company.js',
                'resources/js/pages/recruiter/verification.js',
                'resources/js/pages/recruiter/settings.js',
                'resources/js/pages/admin/overview.js',
                'resources/js/pages/admin/verifications.js',
                'resources/js/pages/admin/companies.js',
                'resources/js/pages/admin/company.js',
                'resources/js/pages/admin/users.js',
                'resources/js/pages/admin/jobs.js',
                'resources/js/pages/admin/sources.js',
                'resources/js/pages/admin/audit.js',
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
