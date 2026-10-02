<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ElisNews - St Elisabeth Hospital</title>
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

    @include('components.navbar')

<section id="hero-section">
            <div class="container">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Berita & Artikel</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">ElisaNews</h1>
                        <p class="hero-subtitle">Ikuti perkembangan terbaru, inovasi medis, dan informasi kesehatan dari Rumah Sakit St. Elisabeth Semarang.</p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="news-section">
            <div class="container">
                <div class="row g-4">
                    @forelse($newsList as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="news-card">
                            <div class="news-card-img-wrapper">
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}">
                                    <img src="{{ $item->image_url ?? asset('images/hero.jpg') }}" class="news-card-img" alt="{{ $item->title }}">
                                </a>
                            </div>
                            <div class="news-card-body">
                                <div class="news-date d-flex justify-content-between align-items-center mb-3">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $item->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">{{ $item->category ?? 'Berita Umum' }}</span>
                                </div>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}">
                                    <h3 class="news-title">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 80) }}</p>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}" class="news-read-more">
                                    Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info">Belum ada berita terbaru saat ini.</div>
                    </div>
                    @endforelse
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
