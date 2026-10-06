<footer id="footer" class="pt-5 pb-3">
    <div class="container">
        <div class="row g-5 mb-5">
            <!-- Bagian 1: Identitas & Kontak -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-header mb-4">
                    <h4 class="rs-name mb-1">RS St. Elisabeth Semarang</h4>
                    <p class="moto">Pancaran cintanya menyembuhkan derita sesama</p>
                </div>
                
                <h4 class="footer-title mb-4 fs-5">Hubungi Kami</h4>
                <ul class="list-unstyled mb-4">
                    <li class="footer-list mb-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-location-dot mt-1" style="color: var(--accent-color);"></i>
                        <a href="#">Jl. Kawi No.1, Semarang, Jawa Tengah</a>
                    </li>
                    <li class="footer-list mb-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-phone mt-1" style="color: var(--accent-color);"></i>
                        <a href="tel:0248502244">(024) 8502244</a>
                    </li>
                    <li class="footer-list mb-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-headset mt-1" style="color: var(--accent-color);"></i>
                        <a href="tel:0248310076">(024) 8310076 / 8310035</a>
                    </li>
                    <li class="footer-list mb-3 d-flex align-items-start gap-3">
                        <i class="fa-solid fa-envelope mt-1" style="color: var(--accent-color);"></i>
                        <a href="mailto:sekretariat@365.rs-elisabeth.com">sekretariat@365.rs-elisabeth.com</a>
                    </li>
                </ul>

                <div class="social-media d-flex gap-3">
                    <a href="#" class="btn-social instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="btn-social facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="btn-social youtube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <!-- Bagian 2: Tautan Cepat (2 Kolom) -->
            <div class="col-lg-4 col-md-6">
                <h4 class="footer-title mb-4 fs-5">Tautan Cepat</h4>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li class="footer-list mb-3"><a href="{{{ route('tentang-kami.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Tentang Kami</a></li>
                            <li class="footer-list mb-3"><a href="{{{ route('news.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Elisanews</a></li>
                            <li class="footer-list mb-3"><a href="{{{ route('promotions.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Promo Menarik</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li class="footer-list mb-3"><a href="{{{ route('articles.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Artikel</a></li>
                            <li class="footer-list mb-3"><a href="{{{ route('customer-information.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Info Pelanggan</a></li>
                            <li class="footer-list mb-3"><a href="{{{ route('glossary.index') }}}"><i class="fa-solid fa-angle-right me-2" style="color: var(--accent-color); font-size: 0.8rem;"></i> Kamus Medis</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Aplikasi Elisameds -->
            <div class="col-lg-4 col-md-12">
                <h4 class="footer-title mb-4 fs-5">Aplikasi Elisameds</h4>
                <div class="p-4 rounded-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <p class="elisameds-desc mb-4 lh-lg" style="font-size: 0.95rem; color: rgba(255, 255, 255, 0.75);">
                        Permudah urusan kesehatan Anda. Unduh aplikasi Mobile RS St. Elisabeth Semarang sekarang juga untuk layanan pendaftaran dan riwayat medis yang lebih cepat & praktis.
                    </p>
                    <a href="https://play.google.com/store/apps/details?id=com.elisameds.app" aria-label="Unduh aplikasi Elisameds di Google Play Store" class="google-play-btn d-inline-block">
                        <i class="fa-brands fa-google-play"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bagian 4: Copyright & Credits -->
        <div class="row align-items-center py-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 small" style="color: rgba(255,255,255,0.7);">
                    &copy; 2026 <strong>Rumah Sakit Santa Elisabeth Semarang</strong>. All Rights Reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 small" style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">
                    Designed & Developed with <i class="fa-solid fa-heart text-danger mx-1"></i> by Lorem Ipsum
                </p>
            </div>
        </div>
    </div>
</footer>
