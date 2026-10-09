<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Promo & Penawaran - RS St. Elisabeth Semarang')])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/glossarium.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
</head>
<body>

    @include('components.navbar')

<main>
        <section id="hero-section">
            <div class="container">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Beranda') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Promo dan Penawaran') }}</li>
                    </ol>
                </nav>

                <div class="row">
                    <!-- Kolom kiri: Judul, subjudul -->
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">{{ __('Promo & Penawaran Spesial') }}</h1>
                        <p class="hero-subtitle">{{ __('Temukan penawaran menarik layanan kesehatan unggulan dari RS St. Elisabeth Semarang.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="promotions-list" style="background: radial-gradient(circle 360px at 12% 20%, rgba(0, 143, 215, 0.08), rgba(0, 143, 215, 0) 70%), linear-gradient(180deg, #ffffff 0%, #ecf4fb 100%);">
            <div class="container py-5">
                <div class="row cards">
                    @forelse ($promotions as $promo)
                        @php
                            $imgSrc = !empty($promo->image_url) ? $promo->image_url : asset('images/placeholder.jpg');
                            $promoTitle = $promo->title ?? __('Promo');
                            $promoDesc = $promo->description ?? __('Info spesial dari RS St. Elisabeth Semarang.');
                            $waGreeting = app()->getLocale() === 'en' ? 'Hello, I would like to order the promo ' : 'Halo, saya ingin memesan promo ';
                        @endphp
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden" 
                                 data-bs-toggle="modal" data-bs-target="#promoModal"
                                 data-title="{{ $promoTitle }}"
                                 data-desc="{{ $promoDesc }}"
                                 data-img="{{ $imgSrc }}"
                                 style="cursor: pointer; transition: transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                                <img src="{{ $imgSrc }}" class="card-img-top" alt="{{ $promoTitle }}" style="height: 300px; object-fit: contain; background-color: #f8f9fa;">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title fw-bold text-primary mb-3" style="text-transform: capitalize">{{ $promoTitle }}</h5>
                                    <p class="card-text text-muted flex-grow-1">{{ $promoDesc }}</p>
                                    <div class="mt-4">
                                        <a href="https://wa.me/6281234567890?text={{ urlencode($waGreeting . $promoTitle) }}" target="_blank" class="btn btn-success w-100 rounded-pill fw-bold shadow-sm d-flex justify-content-center align-items-center">
                                            <i class="fa-brands fa-whatsapp fs-5 me-2"></i> {{ __('Pesan Sekarang') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-tags" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Belum Ada Paket & Promo') }}</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">{{ __('Saat ini belum ada penawaran spesial terbaru. Silakan periksa kembali nanti untuk promo menarik dari kami.') }}</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Modal Promotion -->
            <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9;"></button>
                        <div class="modal-body p-0">
                            <div class="row g-0">
                                <!-- Bagian Kiri: Gambar Promo -->
                                <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">
                                    <img src="" id="promoModalImg" class="img-fluid w-100 h-100" style="object-fit: contain; max-height: 85vh;" alt="Promo">
                                </div>
                                <!-- Bagian Kanan: Detail Promo -->
                                <div class="col-md-6 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                                    <h6 class="text-uppercase fw-bold mb-2" style="color: var(--secondary-color); letter-spacing: 1px;">{{ __('Info Spesial') }}</h6>
                                    <h2 id="promoModalTitle" class="fw-bold mb-4" style="color: var(--primary-color);"></h2>
                                    <p id="promoModalDesc" class="text-secondary fs-5 lh-base mb-5"></p>
                                    
                                    <div class="mt-auto border-top pt-4">
                                        <p class="text-muted fw-semibold mb-3">{{ __('Dapatkan penawaran ini sekarang!') }}</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center flex-grow-1 justify-content-center">
                                                <i class="fa-brands fa-whatsapp fs-5 me-2"></i> {{ __('Hubungi Kami') }}
                                            </a>
                                            <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-semibold flex-grow-0" data-bs-dismiss="modal">{{ __('Tutup') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.footer')
    @include('components.floating-buttons')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        'resources/js/promotions/promotions.js'
    ])
    @include('components.json-ld')
    </body>
</html>