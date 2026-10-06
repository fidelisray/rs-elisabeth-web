    <!-- 1. Top Bar (Ringkas & Tipis) -->
    <div class="top-bar d-none d-lg-block border-bottom">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Kiri: Darurat & Kontak -->
            <div class="d-flex align-items-center gap-3">
                <a href="tel:+62248502244" class="ambulance-call p-1 px-3 d-inline-block ms-0">
                    <i class="fa-solid fa-truck-medical me-2"></i> IGD 24 Jam
                </a>
                <span class="text-white-50">|</span>
                <a href="tel:+62248502244">
                    <i class="fas fa-phone-alt me-2"></i> (024) 8502244
                </a>
            </div>
            <!-- Kanan: Portal & Bahasa -->
            <div class="d-flex align-items-center gap-3">
                <a href="#"><i class="fas fa-user-circle me-1"></i> Portal Pasien</a>
                <span class="text-white-50">|</span>
                <a href="#"><i class="fas fa-globe me-1"></i> ID <i class="fas fa-chevron-down ms-1 fa-xs"></i></a>
            </div>
        </div>
    </div>

    <!-- 2. Main Navbar (Logo, Menu, dan CTA tergabung) -->
    <nav id="main-navbar" class="navbar navbar-expand-lg bg-white sticky-top py-2">
        <div class="container align-items-center">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="Logo RS St. Elisabeth Semarang" height="45" class="logo-main navbar-logo">
                <img src="{{ asset('images/akreditasi.png') }}" alt="Akreditasi" height="45" class="d-none d-sm-block navbar-logo">
            </a>

            <!-- Mobile Toggles (Kanan) -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <a class="btn btn-outline-danger btn-sm rounded-circle p-2 mobile-igd-btn" href="tel:+62248502244"><i class="fa-solid fa-phone"></i></a>
                <!-- Tombol Offcanvas Toggler -->
                <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas">
                    <i class="fa-solid fa-bars fs-3 text-dark"></i>
                </button>
            </div>

            <!-- Desktop Menu & Actions -->
            <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                <!-- Navigasi Utama -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium gap-lg-1">
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('home.index') || request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a></li>
                    <li class="nav-item dropdown nav-tentang-kami">
                        <a class="nav-link nav-link-animated dropdown-toggle {{ request()->routeIs('tentang-kami.*') ? 'active' : '' }}" href="{{ route('tentang-kami.index') }}" data-bs-toggle="dropdown">Tentang Kami</a>
                        <ul class="dropdown-menu main-dropdown-menu shadow-sm border-0 mt-2">
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}">Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#sejarah-singkat">Sejarah</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#visi-dan-misi">Visi & Misi</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('dokter.*') ? 'active' : '' }}" href="{{ route('dokter.index') }}">Cari Dokter</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('ruang-perawatan.*') ? 'active' : '' }}" href="{{ route('ruang-perawatan.index') }}">Ruang Perawatan</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('facilities.*') ? 'active' : '' }}" href="{{ route('facilities.index') }}">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('promotions.*') ? 'active' : '' }}" href="{{ route('promotions.index') }}">Paket & Promo</a></li>
                </ul>

                <!-- Tombol Aksi Kanan -->
                <div class="d-flex align-items-center gap-3">
                    <!-- Search Trigger (Buka Modal) -->
                    <button class="nav-search-trigger" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Cari">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <!-- Buat Janji CTA -->
                    <a class="btn btn-accent btn-janji rounded-pill px-4 fw-semibold" href="https://regonline.rs-elisabeth.com" target="_blank">
                        <i class="far fa-calendar-check me-2"></i>Buat Janji
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 3. Search Modal Overlay (Untuk Desktop & Mobile) -->
    <div class="modal fade search-modal" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content search-modal-content border-0">
                <div class="modal-body p-4">
                    <p class="search-modal-label">Apa yang Anda cari?</p>
                    <form class="d-flex w-100 search-modal-form" role="search" action="{{ route('dokter.index') }}" method="GET">
                        <div class="search-input-wrapper flex-grow-1 me-2">
                            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
                            <input class="form-control form-control-lg search-input" name="nama" type="search" placeholder="Dokter, klinik, jadwal, layanan..." autofocus>
                        </div>
                        <button class="btn btn-primary-custom btn-lg px-4" type="submit">Cari</button>
                    </form>
                    <div class="search-suggestions mt-3">
                        <span class="search-suggestion-label">Pencarian populer:</span>
                        <a href="{{ route('dokter.index') }}?spesialis=jantung" class="search-tag">Dokter Jantung</a>
                        <a href="{{ route('facilities.index') }}" class="search-tag">IGD</a>
                        <a href="{{ route('dokter.index') }}" class="search-tag">Jadwal Poli</a>
                        <a href="{{ route('facilities.index') }}" class="search-tag">Stroke Terpadu</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Mobile Offcanvas Menu (Menggantikan Modal Lama) -->
    <div class="offcanvas offcanvas-end offcanvas-menu" tabindex="-1" id="mobileOffcanvas">
        <div class="offcanvas-header offcanvas-menu-header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo RS St. Elisabeth" height="36" class="offcanvas-logo">
            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body offcanvas-menu-body">
            <ul class="offcanvas-nav-list">
                <li><a class="offcanvas-nav-link {{ request()->routeIs('home.index') || request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}"><i class="fa-solid fa-house me-2"></i>Beranda</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('tentang-kami.*') ? 'active' : '' }}" href="{{ route('tentang-kami.index') }}"><i class="fa-solid fa-hospital me-2"></i>Tentang Kami</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('dokter.*') ? 'active' : '' }}" href="{{ route('dokter.index') }}"><i class="fa-solid fa-user-doctor me-2"></i>Cari Dokter</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('ruang-perawatan.*') ? 'active' : '' }}" href="{{ route('ruang-perawatan.index') }}"><i class="fa-solid fa-bed-pulse me-2"></i>Ruang Perawatan</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('facilities.*') ? 'active' : '' }}" href="{{ route('facilities.index') }}"><i class="fa-solid fa-star-of-life me-2"></i>Fasilitas</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}" href="{{ route('promotions.index') }}"><i class="fa-solid fa-tags me-2"></i>Paket & Promo</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('customer-information.*') ? 'active' : '' }}" href="{{ route('customer-information.index') }}"><i class="fa-solid fa-circle-info me-2"></i>Informasi Pelanggan</a></li>
            </ul>
            <div class="offcanvas-cta-wrapper">
                <a class="btn btn-accent w-100 rounded-pill py-3 offcanvas-cta-btn" href="https://regonline.rs-elisabeth.com">
                    <i class="far fa-calendar-check me-2"></i> Buat Janji Sekarang
                </a>
                <a class="offcanvas-igd-link" href="tel:+62248502244">
                    <i class="fa-solid fa-truck-medical me-2"></i> IGD 24 Jam: (024) 8502244
                </a>
            </div>
        </div>
    </div>
