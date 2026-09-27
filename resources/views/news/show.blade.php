<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $news->title }} - RS St. Elisabeth Semarang</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        @vite([
            'resources/css/style.css',
            'resources/css/btn-accent.css',
            'resources/css/hero.css',
            'resources/css/news.css',
            'resources/css/navbar-dropdown.css',
            'resources/css/top-bar.css',
            'resources/css/search-and-quick-access.css',
        ])
    </head>
    <body>
            <!-- Top Bar -->
    <div class="top-bar d-none d-lg-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="tel:+62248502244" class="ambulance-call p-1 px-3 d-inline-block text-white text-decoration-none"
                        style="border-radius: 1.5rem; border: 1px solid #d10202; background-color: #d10202; font-weight:600; margin-left: 0;">
                        <i class="fa-solid fa-truck-medical me-1"></i> IGD 24 Jam
                    </a>
                    <a href="tel:+62248502244" class="text-white text-decoration-none"><i class="fas fa-phone-alt me-2"></i> (024) 8502244</a>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#"><i class="fas fa-user-circle me-1"></i> Portal Pasien</a>
                    <a href="#"><i class="fas fa-globe me-1"></i> ID <i class="fas fa-chevron-down ms-1"
                            style="font-size: 0.7em;"></i></a>
                </div>
            </div>
        </div>
    </div>
    <header class="nav-group">
        <nav class="navbar bg-body-tertiary">
            <div class="container d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-center gap-3">
                <a class="navbar-brand m-0" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo RS St. Elisabeth Semarang" width="auto" height="50" class="d-inline-block align-text-top logo-main">
                    <img src="{{ asset('images/akreditasi.png') }}" alt="Logo RS St. Elisabeth Semarang" width="auto" height="50" class="d-inline-block align-text-top logo-akreditasi d-none d-sm-inline-block">
                </a>
                <form class="d-flex nav-form-search flex-grow-1 mx-lg-3 order-3 order-lg-2 w-100 w-lg-auto" role="search">
                    <input class="form-control me-2" type="search" placeholder="Temukan dokter, klinik, jadwal.." aria-label="Search"/>
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
                <div class="d-flex align-items-center order-2 order-lg-3">
                    <a class="btn btn-accent btn-sm d-lg-none me-2" href="tel:+62248502244"><i class="fa-solid fa-phone"></i></a>
                    <a class="btn btn-accent btn-sm d-lg-none" href="https://regonline.rs-elisabeth.com" target="_blank" rel="noopener noreferrer"><i class="far fa-calendar-check"></i></a>
                    <a class="btn btn-accent d-none d-lg-inline-block" href="https://regonline.rs-elisabeth.com" target="_blank" rel="noopener noreferrer"><i class="far fa-calendar-check me-2"></i>Buat Janji</a>
                </div>
            </div>
        </nav>
    </header>
    <div id="navbar-sentinel" class="navbar-sentinel"></div>
    <nav id="second-navbar" class="navbar navbar-expand-lg second-nav">
        <div class="container second-nav-body">
                        <!-- Mobile Menu Modal Toggler -->
            <button class="navbar-toggler text-white border-white" type="button" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-controls="mobileMenuModal" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="d-lg-none text-white fw-bold ms-2 me-auto">Menu Utama</span>

            <!-- Desktop Sidebar -->
            <div class="collapse navbar-collapse justify-content-center d-none d-lg-flex" id="navbarNavDropdown">
                <ul class="navbar-nav nav-content gap-2">
                    <li class="nav-item nav-beranda">
                        <a class="nav-link " aria-current="page" href="{{ route('home.index') }}">Beranda</a>
                    </li>
                    <li class="nav-item dropdown nav-tentang-kami">
                        <a class="nav-link dropdown-toggle " href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tentang Kami</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}">Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#visi-dan-misi">Visi & Misi</a></li>
                            <li><a class="dropdown-item" href="{{ route('tentang-kami.index') }}#sejarah-singkat">Sejarah</a></li>
                        </ul>
                    </li>
                    <li class="nav-item nav-cari-dokter">
                        <a class="nav-link " href="{{ route('dokter.index') }}">Cari Dokter</a>
                    </li>
                    <li class="nav-item nav-ruang-perawatan">
                        <a class="nav-link " href="{{ route('ruang-perawatan.index') }}">Ruang Perawatan</a>
                    </li>
                    <li class="nav-item nav-fasilitas">
                        <a class="nav-link " href="{{ route('facilities.index') }}">Fasilitas</a>
                    </li>
                    <li class="nav-item nav-paket-dan-promo">
                        <a class="nav-link " href="{{ route('promotions.index') }}">Paket dan Promo</a>
                    </li>
                    <li class="nav-item nav-informasi-pelanggan">
                        <a class="nav-link " href="{{ route('customer-information.index') }}">Informasi Pelanggan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Modal -->
    <div class="modal fade" id="mobileMenuModal" tabindex="-1" aria-labelledby="mobileMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mobile-menu-dialog">
            <div class="modal-content mobile-menu-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <ul class="mobile-nav-list">
                        <li><a class="" href="{{ route('home.index') }}">Beranda</a></li>
                        <li>
                            <a href="#collapseTentangKamiMobile" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseTentangKamiMobile" class="d-flex justify-content-center align-items-center gap-2 ">
                                Tentang Kami <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                            </a>
                            <div class="collapse" id="collapseTentangKamiMobile">
                                <ul class="mobile-submenu-list">
                                    <li><a href="{{ route('tentang-kami.index') }}">Profil</a></li>
                                    <li><a href="{{ route('tentang-kami.index') }}#visi-dan-misi">Visi & Misi</a></li>
                                    <li><a href="{{ route('tentang-kami.index') }}#sejarah-singkat">Sejarah</a></li>
                                </ul>
                            </div>
                        </li>
                        <li><a class="" href="{{ route('dokter.index') }}">Cari Dokter</a></li>
                        <li><a class="" href="{{ route('ruang-perawatan.index') }}">Ruang Perawatan</a></li>
                        <li><a class="" href="{{ route('facilities.index') }}">Fasilitas</a></li>
                        <li><a class="" href="{{ route('promotions.index') }}">Paket dan Promo</a></li>
                        <li><a class="" href="{{ route('customer-information.index') }}">Informasi Pelanggan</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <section id="hero-section">
            <div class="container">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{{ route('news.index') }}}">Berita & Artikel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($news->title, 30) }}</li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">{{ $news->title }}</h1>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="py-5 bg-white">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="article-header text-center">
                            <div class="article-meta justify-content-center">
                                <span><i class="fa-regular fa-calendar me-2"></i> {{ $news->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</span>
                                <span><i class="fa-regular fa-folder-open me-2"></i> {{ $news->category ?? 'Berita Umum' }}</span>
                            </div>
                        </div>
                        
                        <img src="{{ $news->image_url ?? asset('images/hero.jpg') }}" class="article-featured-image" alt="{{ $news->title }}">
                        
                        <div class="article-content">
                            {!! $news->content !!}
                        </div>
                        
                        <div class="mt-5 border-top pt-4 text-center">
                            <a href="{{{ route('news.index') }}}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Berita
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="footer" class="pt-5">
            <div class="container-fluid col-12 col-md-12">
                <div class="container">
                    <div class="row footer-body">
                        <div class="col mb-5">
                            <div class="footer-header mb-4">
                                <ul>
                                    <li><h4 class="rs-name">RS St. Elisabeth Semarang</h4></li>
                                    <li><p class="moto">Pancaran cintanya menyembuhkan derita sesama</p></li>
                                </ul>
                            </div>
                            <ul>
                                <li><h4 class="footer-title">Hubungi Kami</h4></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-location-dot"></i> Jl. Kawi No.1</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-phone"></i> (024) 8502244</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-phone"></i> (024) 8310076 / (024) 8310035</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-envelope"></i> sekretariat@365.rs-elisabeth.com</a></li>
                            </ul>
                            <div class="social-media d-flex gap-2">
                                <div class="insta"><i class="fa-brands fa-instagram"></i></div>
                                <div class="facebook"><i class="fa-brands fa-facebook"></i></div>
                                <div class="youtube"><i class="fa-brands fa-youtube"></i></div>
                            </div>
                        </div>
                        <div class="col">
                            <ul>
                                <li><h4 class="footer-title">Tautan Cepat</h4></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Tentang Kami</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Elisanews</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Artikel</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Hubungi Kami</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Rekanan</a></li>
                                <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Perpustakaan Online</a></li>
                            </ul>
                        </div>
                        <div class="col">
                            <ul>
                                <li><h4 class="footer-title">Elisameds</h4></li>
                                <li class="footer-list">
                                    <p class="elisameds-desc">Aplikasi Mobile Rumah Sakit St. Elisabeth Semarang untuk meningkatkan kualitas pelayanan kesehatan kepada pasien.</p>
                                </li>
                                <li><a href="https://play.google.com/store/apps/details?id=com.elisameds.app"><i class="fa-brands fa-google-play"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="row footer-copyright">
                        <div class="col">
                            <p class="copyright"><i class="fa-solid fa-copyright"></i> 2026 Rumah Sakit Santa Elisabeth Semarang</p>
                        </div>
                        <div class="col d-flex justify-content-center gap-3">
                            <p class="d-inline-block">Designed By Lorem, ipsum dolor.</p>
                            <p class="d-inline-block">Developed By Lorem, ipsum.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
        @vite([
            'resources/js/navbar/navbar.js',
            'resources/js/navbar/navbar-dropdown.js'
        ])
    </body>
</html>
