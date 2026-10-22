    <!-- 1. Top Bar -->
    <div class="top-bar d-none d-xl-block border-bottom">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Kiri: Layanan Darurat / Emergency (Unified Emergency Pill) -->
            <div class="top-bar-left d-flex align-items-center">
                <a href="tel:+62248502244" class="top-bar-emergency-pill" title="{{ __('Panggilan Darurat IGD 24 Jam: (024) 8502244') }}">
                    <span class="pill-badge">
                        <i class="fa-solid fa-truck-medical"></i>
                        <span>{{ __('IGD 24 Jam') }}</span>
                    </span>
                    <span class="pill-divider">•</span>
                    <span class="pill-phone">
                        <i class="fas fa-phone-alt"></i>
                        <span>(024) 8502244</span>
                    </span>
                </a>
            </div>

            <!-- Tengah: Layanan Pelanggan & WhatsApp -->
            <div class="top-bar-center d-flex align-items-center gap-2 gap-xl-3">
                <div class="top-bar-group item-support d-flex align-items-center gap-1">
                    <i class="fas fa-headset item-icon me-1"></i>
                    <span class="item-label">{{ __('Layanan Pelanggan') }}:</span>
                    <a href="tel:+62248310076" class="top-bar-item item-support" title="{{ __('Telepon Layanan Pelanggan 1') }}">
                        <span class="item-val">(024) 8310076</span>
                    </a>
                    <span class="top-bar-slash">/</span>
                    <a href="tel:+62248310035" class="top-bar-item item-support" title="{{ __('Telepon Layanan Pelanggan 2') }}">
                        <span class="item-val">(024) 8310035</span>
                    </a>
                </div>

                <span class="top-bar-divider"></span>

                <a href="https://wa.me/6282138907740?text={{ __('Halo, saya ingin melakukan konsultasi') }}" target="_blank" rel="noopener noreferrer" class="top-bar-item item-whatsapp" title="{{ __('Chat WhatsApp RS St. Elisabeth') }}">
                    <i class="fab fa-whatsapp item-icon"></i>
                    <span class="item-label">WhatsApp:</span>
                    <span class="item-val">0821-3890-7740</span>
                </a>
            </div>

            <!-- Kanan: Social Media Elisabeth Hospital -->
            <div class="top-bar-right d-flex align-items-center gap-2">
                <span class="item-label me-1">{{ __('Ikuti Kami') }}</span>
                <a href="#" class="btn-social instagram" title="Instagram RS St. Elisabeth" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="btn-social facebook" title="Facebook RS St. Elisabeth" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="btn-social youtube" title="YouTube RS St. Elisabeth" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- 2. Main Navbar (Logo, Menu, dan CTA tergabung) -->
    <nav id="main-navbar" class="navbar navbar-expand-xl bg-white sticky-top py-2">
        <div class="container-fluid px-3 px-xl-4 px-xxl-5 align-items-center">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 m-0" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="Logo RS St. Elisabeth Semarang" height="45" class="logo-main navbar-logo">
                <img src="{{ asset('images/akreditasi.png') }}" alt="Akreditasi" height="45" class="d-none d-sm-block navbar-logo">
            </a>

            <!-- Mobile Toggles (Kanan) -->
            <div class="d-flex align-items-center gap-2 d-xl-none">
                <a class="btn btn-outline-danger btn-sm rounded-circle p-2 mobile-igd-btn" href="tel:+62248502244"><i class="fa-solid fa-phone"></i></a>
                <!-- Tombol Offcanvas Toggler -->
                <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas">
                    <i class="fa-solid fa-bars fs-3 text-dark"></i>
                </button>
            </div>

            <!-- Desktop Menu & Actions -->
            <div class="collapse navbar-collapse justify-content-end" id="mainNavbar">
                <!-- Navigasi Utama -->
                <ul class="navbar-nav mx-auto mb-2 mb-xl-0 fw-medium gap-xl-1">
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('home.index') || request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">{{ __('Beranda') }}</a></li>
                    <li class="nav-item dropdown nav-tentang-kami">
                        <a class="nav-link nav-link-animated dropdown-toggle {{ request()->routeIs('tentang-kami.*') ? 'active' : '' }}" href="{{ route('tentang-kami.index') }}" data-bs-toggle="dropdown">{{ __('Tentang Kami') }}</a>
                        <ul class="dropdown-menu main-dropdown-menu shadow-sm border-0 mt-2">
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}">{{ __('Profil') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#sejarah-singkat">{{ __('Sejarah') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#visi-dan-misi">{{ __('Visi & Misi') }}</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('dokter.*') ? 'active' : '' }}" href="{{ route('dokter.index') }}">{{ __('Cari Dokter') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('ruang-perawatan.*') ? 'active' : '' }}" href="{{ route('ruang-perawatan.index') }}">{{ __('Ruang Perawatan') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('facilities.*') ? 'active' : '' }}" href="{{ route('facilities.index') }}">{{ __('Fasilitas') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('promotions.*') ? 'active' : '' }}" href="{{ route('promotions.index') }}">{{ __('Paket & Promo') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('customer-information.*') ? 'active' : '' }}" href="{{ route('customer-information.index') }}">{{ __('Informasi Pelanggan') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">{{ __('ElisaNews') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-animated {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}">{{ __('Artikel') }}</a></li>
                </ul>

                <!-- Tombol Aksi Kanan -->
                <div class="d-flex align-items-center gap-3">
                    {{-- Dropdown Bahasa (ID / EN) --}}
                    <div class="dropdown nav-lang-dropdown">
                        <button class="btn dropdown-toggle d-flex align-items-center gap-2 shadow-sm" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(app()->getLocale() === 'id')
                                <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="18" height="13" style="border: 1px solid rgba(0,0,0,0.15);">
                                    <path fill="#e70011" d="M0 0h640v240H0z"/>
                                    <path fill="#ffffff" d="M0 240h640v240H0z"/>
                                </svg>
                                <span>ID</span>
                            @else
                                <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="18" height="13">
                                    <path fill="#012169" d="M0 0h640v480H0z"/>
                                    <path fill="#fff" d="m75 0 244 181L562 0h78v62L400 241l240 178v61h-80L320 301 81 480H0v-60l239-179L0 64V0h75z"/>
                                    <path fill="#c8102e" d="m424 281 216 159v40L369 281h55zm-184 20 6 35L54 480H0l240-179zM640 0v3L447 146l31 35L640 40V0zM0 40l239 179h-60L0 42v-2z"/>
                                    <path fill="#fff" d="M240 0v480h160V0H240zM0 160v160h640V160H0z"/>
                                    <path fill="#c8102e" d="M0 192v96h640v-96H0zM272 0v480h96V0h-96z"/>
                                </svg>
                                <span>EN</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 py-2" aria-labelledby="languageDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 {{ app()->getLocale() === 'id' ? 'active' : '' }}" href="{{ route('lang.switch', 'id') }}">
                                    <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="20" height="15" style="border: 1px solid rgba(0,0,0,0.15);">
                                        <path fill="#e70011" d="M0 0h640v240H0z"/>
                                        <path fill="#ffffff" d="M0 240h640v240H0z"/>
                                    </svg>
                                    <span class="fw-medium">Indonesia</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 {{ app()->getLocale() === 'en' ? 'active' : '' }}" href="{{ route('lang.switch', 'en') }}">
                                    <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="20" height="15">
                                        <path fill="#012169" d="M0 0h640v480H0z"/>
                                        <path fill="#fff" d="m75 0 244 181L562 0h78v62L400 241l240 178v61h-80L320 301 81 480H0v-60l239-179L0 64V0h75z"/>
                                        <path fill="#c8102e" d="m424 281 216 159v40L369 281h55zm-184 20 6 35L54 480H0l240-179zM640 0v3L447 146l31 35L640 40V0zM0 40l239 179h-60L0 42v-2z"/>
                                        <path fill="#fff" d="M240 0v480h160V0H240zM0 160v160h640V160H0z"/>
                                        <path fill="#c8102e" d="M0 192v96h640v-96H0zM272 0v480h96V0h-96z"/>
                                    </svg>
                                    <span class="fw-medium">English</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- Search Trigger (Buka Modal) -->
                    {{-- 
                    <button class="nav-search-trigger" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="{{ __('Cari') }}">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                     --}}
                    <!-- Buat Janji CTA -->
                    <a class="btn btn-accent btn-janji rounded-pill px-4 fw-semibold" href="https://regonline.rs-elisabeth.com" target="_blank">
                        <i class="far fa-calendar-check me-2"></i>{{ __('Buat Janji') }}
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
                    <p class="search-modal-label">{{ __('Apa yang Anda cari?') }}</p>
                    <form class="d-flex w-100 search-modal-form" role="search" action="{{ route('dokter.index') }}" method="GET">
                        <div class="search-input-wrapper flex-grow-1 me-2">
                            <i class="fa-solid fa-magnifying-glass search-input-icon"></i>
                            <input class="form-control form-control-lg search-input" name="nama" type="search" placeholder="{{ __('Dokter, klinik, jadwal, layanan...') }}" autofocus>
                        </div>
                        <button class="btn btn-primary-custom btn-lg px-4" type="submit">{{ __('Cari') }}</button>
                    </form>
                    <div class="search-suggestions mt-3">
                        <span class="search-suggestion-label">{{ __('Pencarian populer:') }}</span>
                        <a href="{{ route('dokter.index') }}?spesialis=jantung" class="search-tag">{{ __('Dokter Jantung') }}</a>
                        <a href="{{ route('facilities.index') }}" class="search-tag">IGD</a>
                        <a href="{{ route('dokter.index') }}" class="search-tag">{{ __('Jadwal Poli') }}</a>
                        <a href="{{ route('facilities.index') }}" class="search-tag">{{ __('Stroke Terpadu') }}</a>
                        <a href="{{ route('customer-information.index') }}" class="search-tag">{{ __('Informasi Pelanggan') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Mobile Offcanvas Menu (Menggantikan Modal Lama) -->
    <div class="offcanvas offcanvas-end offcanvas-menu" tabindex="-1" id="mobileOffcanvas">
        <div class="offcanvas-header offcanvas-menu-header">
            <div class="offcanvas-logo-container">
                <img src="{{ asset('images/logo.png') }}" alt="Logo RS St. Elisabeth" height="32" class="offcanvas-logo">
            </div>
            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="{{ __('Tutup') }}"></button>
        </div>
        <div class="offcanvas-body offcanvas-menu-body">
            <!-- Mobile Language Switcher Bar -->
            <div class="px-4 py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                <span class="small fw-semibold text-secondary">
                    <i class="fas fa-globe me-2 text-primary"></i>{{ __('Bahasa') }}
                </span>
                <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Pilih Bahasa') }}">
                    <a href="{{ route('lang.switch', 'id') }}" class="btn {{ app()->getLocale() === 'id' ? 'btn-primary text-white' : 'btn-outline-secondary' }} px-3 py-1 fw-bold d-inline-flex align-items-center gap-2">
                        <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="16" height="12" style="border: 1px solid rgba(0,0,0,0.15);">
                            <path fill="#e70011" d="M0 0h640v240H0z"/>
                            <path fill="#ffffff" d="M0 240h640v240H0z"/>
                        </svg>
                        <span>ID</span>
                    </a>
                    <a href="{{ route('lang.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'btn-primary text-white' : 'btn-outline-secondary' }} px-3 py-1 fw-bold d-inline-flex align-items-center gap-2">
                        <svg class="flag-icon rounded-1 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 480" width="16" height="12">
                            <path fill="#012169" d="M0 0h640v480H0z"/>
                            <path fill="#fff" d="m75 0 244 181L562 0h78v62L400 241l240 178v61h-80L320 301 81 480H0v-60l239-179L0 64V0h75z"/>
                            <path fill="#c8102e" d="m424 281 216 159v40L369 281h55zm-184 20 6 35L54 480H0l240-179zM640 0v3L447 146l31 35L640 40V0zM0 40l239 179h-60L0 42v-2z"/>
                            <path fill="#fff" d="M240 0v480h160V0H240zM0 160v160h640V160H0z"/>
                            <path fill="#c8102e" d="M0 192v96h640v-96H0zM272 0v480h96V0h-96z"/>
                        </svg>
                        <span>EN</span>
                    </a>
                </div>
            </div>
            <ul class="offcanvas-nav-list">
                <li><a class="offcanvas-nav-link {{ request()->routeIs('home.index') || request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}"><i class="fa-solid fa-house me-2"></i>{{ __('Beranda') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('tentang-kami.*') ? 'active' : '' }}" href="{{ route('tentang-kami.index') }}"><i class="fa-solid fa-hospital me-2"></i>{{ __('Tentang Kami') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('dokter.*') ? 'active' : '' }}" href="{{ route('dokter.index') }}"><i class="fa-solid fa-user-doctor me-2"></i>{{ __('Cari Dokter') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('ruang-perawatan.*') ? 'active' : '' }}" href="{{ route('ruang-perawatan.index') }}"><i class="fa-solid fa-bed-pulse me-2"></i>{{ __('Ruang Perawatan') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('facilities.*') ? 'active' : '' }}" href="{{ route('facilities.index') }}"><i class="fa-solid fa-star-of-life me-2"></i>{{ __('Fasilitas') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('promotions.*') ? 'active' : '' }}" href="{{ route('promotions.index') }}"><i class="fa-solid fa-tags me-2"></i>{{ __('Paket & Promo') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('customer-information.*') ? 'active' : '' }}" href="{{ route('customer-information.index') }}"><i class="fa-solid fa-circle-info me-2"></i>{{ __('Informasi Pelanggan') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}"><i class="fa-solid fa-newspaper me-2"></i>{{ __('ElisaNews') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}"><i class="fa-solid fa-file-lines me-2"></i>{{ __('Artikel') }}</a></li>
                <li><a class="offcanvas-nav-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}" href="{{ route('feedback.index') }}"><i class="fa-solid fa-comment-dots me-2"></i>{{ __('Kritik & Saran') }}</a></li>
            </ul>

            <div class="offcanvas-cta-wrapper">
                <a class="btn btn-accent w-100 rounded-pill py-3 offcanvas-cta-btn" href="https://regonline.rs-elisabeth.com">
                    <i class="far fa-calendar-check me-2"></i> {{ __('Buat Janji Sekarang') }}
                </a>
                <a class="offcanvas-igd-link" href="tel:+62248502244">
                    <i class="fa-solid fa-truck-medical me-2"></i> {{ __('IGD 24 Jam') }}: (024) 8502244
                </a>
            </div>
        </div>
    </div>
