import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/style.css',
                'resources/css/btn-accent.css',
                'resources/css/navbar-dropdown.css',
                'resources/css/facilities-and-services.css',
                'resources/css/ruang-perawatan.css',
                'resources/css/news.css',
                'resources/css/stats-section.css',
                'resources/css/app-section.css',
                'resources/css/glossarium.css',
                'resources/css/hero.css',
                'resources/css/home-hero.css',
                'resources/css/search-and-quick-access.css',
                'resources/css/dokter.css',
                'resources/css/jadwal_dokter.css',
                'resources/css/tentang-kami.css',
                'resources/css/customer-information.css',
                'resources/css/customer-info-section.css',
                'resources/css/top-bar.css',
                'resources/css/promo-banner.css',
                'resources/js/app.js',
                'resources/js/navbar/navbar.js',
                'resources/js/navbar/navbar-dropdown.js',
                'resources/js/promotions/promotions.js',
                'resources/js/facilities-and-services/facilities.js',
                'resources/js/ruang-perawatan/ruang-perawatan.js',
                'resources/js/glosarium/glosarium.js',
                'resources/js/dokter/script.js',
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
