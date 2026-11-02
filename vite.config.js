import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import os from 'os';

function getLocalIp() {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        if (/wsl|vEthernet|docker|loopback|virtual|vmware/i.test(name)) continue;
        for (const iface of interfaces[name] || []) {
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return null;
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    
    let host = env.VITE_HOST;
    if (!host) {
        const localIp = getLocalIp();
        if (localIp) {
            host = localIp;
        } else if (env.APP_URL) {
            try {
                host = new URL(env.APP_URL).hostname;
            } catch {
                host = 'localhost';
            }
        } else {
            host = 'localhost';
        }
    }

    return {
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
                    'resources/css/online-library.css',
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
                    'resources/css/faq-section.css',
                    'resources/css/feedback.css',
                    'resources/css/questionnaire.css',
                    'resources/css/top-bar.css',
                    'resources/css/promo-banner.css',
                    'resources/css/footer.css',
                    'resources/js/app.js',
                    'resources/js/navbar/navbar.js',
                    'resources/js/navbar/navbar-dropdown.js',
                    'resources/js/promotions/promotions.js',
                    'resources/js/facilities-and-services/facilities.js',
                    'resources/js/ruang-perawatan/ruang-perawatan.js',
                    'resources/js/glosarium/glosarium.js',
                    'resources/js/dokter/script.js',
                    'resources/js/components/back-to-top.js',
                    'resources/js/home/elisameds-animation.js',
                ],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            cors: true,
            hmr: {
                host: host,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
