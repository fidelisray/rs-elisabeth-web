<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Temukan fasilitas dan layanan unggulan RS St. Elisabeth Semarang — dari Pelayanan Stroke Terpadu, ICU, IGD 24 Jam, hingga Klinik Spesialis berstandar tinggi.">
    <title>Fasilitas & Layanan - RS St. Elisabeth Semarang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/facilities-and-services.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
</head>
<body>


    @include('components.navbar')

<main>
        <h1 class="visually-hidden">Fasilitas dan Layanan RS Santa Elisabeth Semarang</h1>

        {{-- ===== HERO SECTION (diambil dari promotions/index.blade.php) ===== --}}
        <section id="hero-section">
            <div class="container">
                {{-- Breadcrumb --}}
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Fasilitas & Layanan</li>
                    </ol>
                </nav>

                <div class="row">
                    {{-- Kolom kiri: Judul, subjudul --}}
                    <div class="col-12 col-lg-8">
                        <h2 class="hero-title">Fasilitas &amp; Layanan Unggulan</h2>
                        <p class="hero-subtitle">Kami menyediakan fasilitas dan layanan berteknologi canggih demi memberikan pelayanan yang berkualitas dan paripurna kepada setiap pasien.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== INTRO SECTION ===== --}}
        <section id="facilities-intro">
            <div class="container text-center">
                <div class="intro-label">
                    <span>LAYANAN &amp; FASILITAS</span>
                </div>
                <p class="intro-desc">
                    Kami menyediakan Layanan dan Fasilitas berteknologi canggih demi memberikan pelayanan yang berkualitas dan paripurna.
                </p>
            </div>
        </section>

        {{-- ===== FASILITAS UTAMA: LIST + DETAIL ===== --}}
        <section id="facilities-main">
            <div class="container">
                <div class="row g-4 align-items-start">

                    {{-- ---- Kolom Kiri: Daftar Fasilitas ---- --}}
                    <div class="col-12 col-lg-4">
                        <div class="facility-sidebar">
                            <div class="facility-sidebar-header">
                                <i class="fa-solid fa-list-ul"></i>
                                <span>Pilih Fasilitas</span>
                            </div>
                            <div class="facility-list">
                                @forelse($facilities as $facility)
                                <button class="btn-facility" data-target="facility-{{ $facility->slug ?? $facility->id }}">
                                    <i class="fa-solid fa-chevron-right"></i>
                                    {{ $facility->name }}
                                </button>
                                @empty
                                <p class="text-center text-muted">Belum ada fasilitas yang ditambahkan.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ---- Kolom Kanan: Detail Panel ---- --}}
                    <div class="col-12 col-lg-8">
                        <div class="facility-detail-panel">

                            @foreach ($facilities as $facility)
                            <div class="facility-detail-item" id="facility-{{ $facility->slug ?? $facility->id }}">
                                <div class="facility-img-wrapper">
                                    <img src="{{ $facility->image_url ?? asset('images/placeholder.jpg') }}" alt="{{ $facility->name }} RS St. Elisabeth Semarang">
                                </div>
                                <div class="facility-content-body">
                                    @if(!empty($facility->category))
                                    <span class="facility-tag">{{ $facility->category }}</span>
                                    @endif
                                    
                                    <h3 class="facility-name">{{ $facility->name }}</h3>
                                    <hr class="facility-divider">
                                    
                                    <div class="facility-desc-wrapper facility-desc">
                                        {!! $facility->description !!}
                                    </div>
                                    
                                    @if(!empty($facility->highlights) && is_array($facility->highlights))
                                    <div class="facility-highlights">
                                        @foreach($facility->highlights as $highlight)
                                        <span class="facility-highlight-badge"><i class="fa-solid fa-check-circle"></i> {{ is_array($highlight) ? ($highlight['name'] ?? '') : $highlight }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    <div class="facility-cta">
                                        <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20layanan%20{{ urlencode($facility->name) }}" target="_blank" class="btn-primary-facility">
                                            <i class="fa-brands fa-whatsapp"></i> Hubungi Kami
                                        </a>
                                        <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-outline-facility">
                                            <i class="fa-regular fa-calendar-check"></i> Buat Janji
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>{{-- END .facility-detail-panel --}}
                    </div>{{-- END kolom kanan --}}

                </div>{{-- END .row --}}
            </div>{{-- END .container --}}
        </section>

    </main>

    {{-- ===== FOOTER ===== --}}
    <footer id="footer" class="pt-5">
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
                            <li><a href="https://play.google.com/store/apps/details?id=com.elisameds.app" aria-label="Unduh aplikasi Elisameds di Google Play Store"><i class="fa-brands fa-google-play"></i></a></li>
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
    </footer>

    @include('components.floating-buttons')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        'resources/js/facilities-and-services/facilities.js'
    ])
</body>
</html>
