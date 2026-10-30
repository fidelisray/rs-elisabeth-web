import './bootstrap';

// Registrasi aset gambar statis ke pipeline Vite untuk otomatisasi content hash & Vite::asset()
import.meta.glob([
    '../images/**',
], { eager: true });
