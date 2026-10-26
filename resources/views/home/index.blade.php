<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      @include('components.seo-meta', ['title' => 'RS St. Elisabeth Semarang'])
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
      @vite([
        'resources/css/footer.css',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/home-hero.css',
        'resources/css/news.css',
        'resources/css/stats-section.css',
        'resources/css/app-section.css',
        'resources/css/top-bar.css',
        'resources/css/promo-banner.css',
        'resources/css/customer-info-section.css',
        'resources/css/faq-section.css'
      ])
  </head>
  <body>

    @include('components.navbar')

    <main class="home-canvas">
        <h1 class="visually-hidden">Rumah Sakit Santa Elisabeth Semarang</h1>

        <!-- Modern Promo Banner Section (Hero Baru) -->
        <section id="promo-banner-section">
            <div id="promo-banner-carousel" class="carousel slide" data-bs-ride="carousel">

                {{-- ===== INDIKATOR SLIDE (Dinamis dari CMS) ===== --}}
                @if(count($banners) > 1)
                <div class="carousel-indicators">
                    @foreach($banners as $index => $banner)
                    <button type="button"
                        data-bs-target="#promo-banner-carousel"
                        data-bs-slide-to="{{ $index }}"
                        class="{{ $index === 0 ? 'active' : '' }}"
                        {{ $index === 0 ? 'aria-current="true"' : '' }}
                        aria-label="Slide {{ $index + 1 }}">
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- ===== KONTEN GAMBAR BANNER (Dinamis dari CMS) ===== --}}
                <div class="carousel-inner">
                    @forelse($banners as $index => $banner)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $banner->image_url ?? asset('images/placeholder.jpg') }}"
                             class="d-block w-100"
                             alt="{{ $banner->title }}">
                    </div>
                    @empty
                    {{-- Fallback: Tampilkan Default Hero Banner jika data CMS sedang kosong --}}
                    <div class="carousel-item active">
                        <div class="d-flex flex-column align-items-center justify-content-center text-center px-4" style="min-height: 420px; background: linear-gradient(135deg, var(--primary-color, #008fd7) 0%, var(--secondary-darker-color, #1a2740) 100%); position: relative; overflow: hidden;">
                            <!-- Watermark Ikon -->
                            <div class="position-absolute top-50 start-50 translate-middle" style="opacity: 0.03; pointer-events: none;">
                                <i class="fa-solid fa-house-medical" style="font-size: 30rem; color: #ffffff;"></i>
                            </div>
                            <!-- Konten Banner -->
                            <div style="z-index: 1;">
                                <div class="mb-4">
                                    <span class="badge rounded-pill px-3 py-2" style="background-color: rgba(255,255,255,0.15); color: #fff; font-weight: 500; letter-spacing: 1px;">
                                        <i class="fa-solid fa-heart-pulse me-1 text-warning"></i> {{ __('Melayani dengan Kasih') }}
                                    </span>
                                </div>
                                <h2 class="text-white fw-bold mb-3 display-6" style="letter-spacing: 0.5px;">RS St. Elisabeth Semarang</h2>
                                <p class="text-white opacity-75 mb-0 mx-auto fs-6 fs-md-5" style="max-width: 650px; line-height: 1.6;">
                                    {{ __('Hadir dengan sepenuh hati memberikan pelayanan kesehatan terbaik, berteknologi mutakhir, dan profesional bagi Anda dan keluarga.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- ===== TOMBOL NAVIGASI (hanya tampil jika ada lebih dari 1 banner) ===== --}}
                @if(count($banners) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#promo-banner-carousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{{ __('Sebelumnya') }}</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#promo-banner-carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">{{ __('Selanjutnya') }}</span>
                </button>
                @endif

            </div>
        </section>

        <section id="quick-access" class="py-0">
            <div class="container quick-access-container">
                <div class="row g-0 quick-access-options">
                    <div class="col-6 col-md-3 quick-access-option-1">
                        <a href="{{{ route('dokter.index') }}}" class="text-decoration-none h-100">
                            <div class="card card-doctor h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-solid fa-user-doctor doctor-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">{{ __('Cari Dokter') }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-2">
                        <a href="https://regonline.rs-elisabeth.com" class="text-decoration-none h-100">
                            <div class="card card-appointment h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-regular fa-calendar appointment-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">{{ __('Buat Janji') }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-3">
                        <a href="https://wa.me/6285600600870?text={{ urlencode(__('Halo, saya ingin membuat janji temu')) }}" class="text-decoration-none h-100">
                            <div class="card card-contact h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-brands fa-whatsapp whatsapp-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">{{ __('Hubungi Kami') }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-4">
                        <a href="{{{ route('glossary.index') }}}" class="text-decoration-none h-100">
                            <div class="card card-emergency h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-solid fa-book-medical glossary-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">{{ __('Kamus Medis') }}</h5>
                                    <p class="mb-0 mt-1 mt-md-2 opacity-75 small d-none d-md-block">{{ __('Temukan penyakit & istilah medis') }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>





        <section id="about-us" class="py-5">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">{{ __('Percayakan Kesehatan Anda Bersama Kami') }}</h2>
                    <div class="divider"></div>
                </div>
                <div class="row align-items-center mt-5">
                    <div class="col-md-5 mb-4 mb-md-0 px-md-4">
                        <div class="about-rs mb-5">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-medal"></i>
                                </div>
                                {{ __('Terakreditasi Paripurna') }}
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">{{ __('Kami mendapat predikat PARIPURNA dari Komisi Akreditasi Rumah Sakit (KARS), yang merupakan predikat dengan hasil penilaian tertinggi berdasarkan penilaian terhadap manajemen mutu dan keselamatan pasien yang diterapkan di Rumah Sakit.') }}</p>
                        </div>
                        <div class="about-rs mb-5">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                {{ __('Layanan 24 Jam') }}
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">{{ __('Kami menyediakan layanan 24 jam untuk memenuhi kebutuhan Kesehatan anda, khususnya bagi anda yang membutuhkan penanganan emergency.') }}</p>
                        </div>
                        <div class="about-rs">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                {{ __('Pelayanan Prima') }}
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">{{ __('Berpusat kepada pasien sebagai “Tamu Ilahi”, Kami senantiasa memberikan kualitas pelayanan yang bermutu tinggi dan profesional, dengan tetap memperhatikan aspek keselamatan pasien.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-7 text-center position-relative mt-4 mt-md-0">
                        <div class="position-absolute w-100 h-100 rounded-4 about-bg-accent d-none d-md-block"></div>
                        <img src="{{ asset('images/feature.jpg') }}" class="img-fluid rounded-4 shadow-lg position-relative" alt="{{ __('Fasilitas RS St. Elisabeth Semarang') }}" style="z-index: 2; object-fit: cover; max-height: 500px; width: 100%;">
                    </div>
                </div>
            </div>
        </section>


        <section id="facilities-and-services" class="py-5">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">{{ __('Fasilitas dan Layanan') }}</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">{{ __('Pelayanan unggulan dengan dokter spesialis berpengalaman.') }}</p>
                </div>

                <div id="carouselExampleCaptions" class="carousel slide shadow rounded-4 overflow-hidden bg-white" data-bs-ride="carousel">
                    @if(count($facilities) > 0)
                    <div class="carousel-indicators">
                        @foreach ($facilities as $index => $facility)
                            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }} bg-dark" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    @endif
                    <div class="carousel-inner" id="carousel-facilities-and-services">
                        @forelse ($facilities as $index => $facility)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="row align-items-center g-0">
                                <div class="col-md-6 text-center facility-img-wrapper">
                                    <img src="{{ $facility->image_url ?? asset('images/placeholder.jpg') }}" class="w-100 h-100 object-fit-cover" alt="{{ $facility->name }}">
                                </div>
                                <div class="col-md-6 p-4 p-md-5">
                                    <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-bold">{{ $facility->category ?? 'Featured' }}</span>
                                    <h3 class="fw-bold" style="color: var(--secondary-color);">{{ $facility->name }}</h3>
                                    <p class="text-muted fs-5 mt-3">{{ $facility->short_description ?? \Illuminate\Support\Str::limit(strip_tags($facility->description ?? ''), 150) }}</p>
                                    <div class="mt-4">
                                        <a href="{{ route('facilities.index') }}#facility-{{ $facility->slug ?? $facility->id }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">{{ __('Selengkapnya') }} <i class="fas fa-arrow-right ms-2"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="carousel-item active p-5 text-center">
                            <p class="text-muted">{{ __('Belum ada data fasilitas unggulan.') }}</p>
                        </div>
                        @endforelse
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark p-3 rounded-circle" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">{{ __('Sebelumnya') }}</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark p-3 rounded-circle" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">{{ __('Selanjutnya') }}</span>
                    </button>
                </div>
            </div>
        </section>

        
        <section id="home-hero">
            <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false">
                <div class="carousel-inner">
                    <div class="carousel-item active hero-bg-image" style="background-image: url('{{ asset('images/hero.jpg') }}');"></div>
                </div>
            </div>

            <div class="hero-overlay"></div>

            <div class="container position-relative h-100">
                <div class="hero-content h-100 d-flex flex-column justify-content-center">
                    <h1 class="display-5 fw-bold text-white mb-3">{{ __('Kesehatan Anda Adalah Prioritas Utama Kami') }}</h1>
                    <p class="fs-5 text-white mb-4">{{ __('Pelayanan prima dan paripurna dari RS St. Elisabeth Semarang dengan fasilitas berstandar internasional dan tenaga medis profesional yang penuh kasih.') }}</p>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        {{-- <button class="btn btn-primary-custom px-4 py-2">Temukan Dokter</button> --}}
                        {{-- <button class="btn btn-outline-light px-4 py-2 fw-semibold rounded-pill">Hubungi Kami</button> --}}
                    </div>
                </div>
            </div>
        </section>

        <section id="search-and-quick-access">
            <h2 class="visually-hidden">{{ __('Pencarian Layanan dan Akses Cepat') }}</h2>
            <div class="container">
                <div class="search-widget">
                    <ul class="nav nav-tabs" id="searchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#doctor"
                                type="button"><i class="fas fa-user-md me-2"></i>{{ __('Cari Dokter') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#clinic" type="button"><i
                                    class="fas fa-hospital me-2"></i>{{ __('Cari Klinik') }}</button>
                        </li>
                        {{-- <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#condition"
                                type="button"><i class="fas fa-calendar-alt me-2"></i>Jadwal Praktek</button>
                        </li> --}}
                    </ul>
                    <div class="tab-content" id="searchTabsContent">
                        <div class="tab-pane fade show active" id="doctor">
                            <form class="row g-3" action="{{{ route('dokter.index') }}}" method="GET">
                                <div class="col-md-4">
                                    <input type="text" name="nama" class="form-control" placeholder="{{ __('Nama Dokter') }}">
                                </div>
                                <div class="col-md-4">
                                    <select name="specialty_code" class="form-select">
                                        <option value="" selected>{{ __('Pilih Spesialisasi') }}</option>
                                        @if(isset($spesialisasi))
                                            @foreach ($spesialisasi as $spesialis)
                                                <option value="{{ $spesialis->Code }}">{{ ucwords(strtolower($spesialis->Name)) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary-custom w-100" type="submit"><i
                                            class="fas fa-search me-2"></i>{{ __('Cari') }}</button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="clinic">
                            <form class="row g-3" action="{{{ route('dokter.index') }}}" method="GET">
                                <div class="col-md-8">
                                    <input type="text" name="klinik" class="form-control"
                                        placeholder="{{ __('Nama Klinik') }}">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary-custom w-100" type="submit"><i
                                            class="fas fa-search me-2"></i>{{ __('Cari') }}</button>
                                </div>
                            </form>
                        </div>
                        {{-- <div class="tab-pane fade" id="condition">
                            <form class="row g-3">
                                <div class="col-md-8">
                                    <input type="text" class="form-control"
                                        placeholder="Temukan jadwal dokter hari ini">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary-custom w-100" type="button"><i
                                            class="fas fa-search me-2"></i>Cari</button>
                                </div>
                            </form>
                        </div> --}}
                    </div>
                </div>
            </div>
        </section>

        <section id="promotions" class="position-relative pb-5">
            <div class="section-title text-center mb-5">
                <h2 class="display-8 fw-bold">{{ __('Paket dan Promo') }}</h2>
                <div class="divider"></div>
            </div>
            <div class="container pb-5">
                <div class="row g-4 justify-content-center" id="promoTrack">
                    @forelse($promotions as $promo)
                        @php
                            $promoImg = !empty($promo->image_url) ? $promo->image_url : asset('images/placeholder.jpg');
                            $promoTitle = $promo->title ?? __('Promo');
                            $promoDesc = $promo->description ?? __('Penawaran spesial dari RS St. Elisabeth Semarang.');
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="promo-card" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#promoModal" 
                                 data-title="{{ $promoTitle }}" 
                                 data-desc="{{ $promoDesc }}" 
                                 data-img="{{ $promoImg }}">
                                <div class="card h-100">
                                    <div class="promo-img-wrapper">
                                        <img src="{{ $promoImg }}" class="card-img-top promo-card-img" alt="{{ $promoTitle }}" loading="lazy">
                                    </div>
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title fw-bold text-primary mb-2" style="text-transform: capitalize;">{{ $promoTitle }}</h5>
                                        <p class="card-text text-muted small flex-grow-1">{{ Str::limit($promo->excerpt ?? strip_tags($promoDesc), 90) }}</p>
                                        <div class="mt-3 text-end">
                                            <span class="text-secondary fw-semibold small">{{ __('Lihat Detail') }} <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                        </div>
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
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3 bg-white p-2 rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="{{ __('Tutup') }}" style="opacity: 0.9;"></button>
                        <div class="modal-body p-0">
                            <div class="row g-0">
                                <!-- Bagian Kiri: Gambar Promo -->
                                <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">
                                    <img src="" id="promoModalImg" class="img-fluid w-100 h-100" style="object-fit: contain; max-height: 85vh;" alt="{{ __('Promo') }}">
                                </div>
                                <!-- Bagian Kanan: Detail Promo -->
                                <div class="col-md-6 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                                    <h6 class="text-uppercase fw-bold mb-2" style="color: var(--secondary-color); letter-spacing: 1px;">{{ __('Info Spesial') }}</h6>
                                    <h2 id="promoModalTitle" class="fw-bold mb-4" style="color: var(--primary-color);"></h2>
                                    <div id="promoModalDesc" class="text-secondary fs-5 lh-base mb-5"></div>
                                    
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

            <div class="text-center mt-5">
                <a href="{{ route('promotions.index') }}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="{{ __('Lihat Penawaran Menarik Lainnya') }}">
                    {{ __('Lihat Penawaran Menarik Lainnya') }} <i class="fa-solid fa-arrow-down ms-2"></i>
                </a>
            </div>
        </section>

        <!-- Latest Articles Section -->
        <section id="latest-articles" class="news-section pt-2 pb-5">
            <div class="section-title text-center mb-5 mt-5">
                <h2 class="display-8 fw-bold">{{ __('Artikel Kesehatan') }}</h2>
                <div class="divider"></div>
            </div>
            <div class="container pb-5">
                <div class="row g-4 justify-content-center">
                    @forelse($latestArticles as $item)
                    @php
                        $articleUrl = !empty($item->slug) ? route('articles.show', ['slug' => $item->slug]) : route('articles.index');
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="news-card h-100">
                            <div class="news-card-img-wrapper">
                                <a href="{{ $articleUrl }}" aria-label="{{ $item->title }}">
                                    <img src="{{ $item->image_url ?? asset('images/hero.jpg') }}" class="news-card-img" alt="{{ $item->title }}" loading="lazy">
                                </a>
                            </div>
                            <div class="news-card-body">
                                <div class="news-date d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $item->created_at?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="news-category-badge">{{ $item->tags ? Str::before($item->tags, ',') : __('Artikel Kesehatan') }}</span>
                                </div>
                                <a href="{{ $articleUrl }}">
                                    <h3 class="news-title">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 95) }}</p>
                                <a href="{{ $articleUrl }}" class="news-read-more">
                                    {{ __('Baca Selengkapnya') }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Artikel Belum Tersedia') }}</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">{{ __('Kami sedang menyiapkan artikel kesehatan terbaru untuk Anda. Silakan nantikan informasi bermanfaat selanjutnya.') }}</p>
                        </div>
                    </div>
                    @endforelse
                </div>
                <div class="text-center mt-5">
                    <a href="{{ route('articles.index') }}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="{{ __('Lihat Semua Artikel') }}">
                        {{ __('Lihat Semua Artikel') }} <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Latest News Section -->
        <section id="latest-news" class="news-section pt-2 pb-5">
            <div class="section-title text-center mb-5 mt-5">
                <h2 class="display-8 fw-bold">ElisaNews</h2>
                <div class="divider"></div>
            </div>
            <div class="container pb-5">
                <div class="row g-4 justify-content-center">
                    @forelse($latestNews as $item)
                    @php
                        $newsUrl = !empty($item->slug) ? route('news.show', ['slug' => $item->slug]) : route('news.index');
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="news-card h-100">
                            <div class="news-card-img-wrapper">
                                <a href="{{ $newsUrl }}" aria-label="{{ $item->title }}">
                                    <img src="{{ $item->image_url ?? asset('images/hero.jpg') }}" class="news-card-img" alt="{{ $item->title }}" loading="lazy">
                                </a>
                            </div>
                            <div class="news-card-body">
                                <div class="news-date d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $item->created_at?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="news-category-badge">{{ $item->category ?? __('Berita Umum') }}</span>
                                </div>
                                <a href="{{ $newsUrl }}">
                                    <h3 class="news-title">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 95) }}</p>
                                <a href="{{ $newsUrl }}" class="news-read-more">
                                    {{ __('Baca Selengkapnya') }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-bullhorn" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Berita Belum Tersedia') }}</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">{{ __('Belum ada berita terbaru seputar rumah sakit saat ini. Kami akan segera memperbarui informasi terkini untuk Anda.') }}</p>
                        </div>
                    </div>
                    @endforelse
                </div>
                <div class="text-center mt-5">
                    <a href="{{ route('news.index') }}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="{{ __('Lihat Semua Berita') }}">
                        {{ __('Lihat Semua Berita') }} <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <section id="frequently-ask-question">
            <div class="container">
                <div class="row g-5 align-items-start">
                    <!-- Kolom Kiri: Header & Quick Support Card -->
                    <div class="col-lg-5">
                        <div class="pe-lg-4">
                            <span class="faq-eyebrow">
                                <i class="fa-solid fa-circle-question"></i> {{ __('Informasi & Tanya Jawab') }}
                            </span>
                            <h2 class="faq-title">{{ __('Pertanyaan yang Sering Diajukan') }}</h2>
                            <p class="faq-desc">
                                {{ __('Temukan informasi penting seputar jam kunjungan pasien, tata tertib rumah sakit, dan ketentuan berkunjung untuk kenyamanan bersama di RS St. Elisabeth Semarang.') }}
                            </p>

                            <!-- Kartu Bantuan / Narahubung Cepat -->
                            <div class="faq-help-card">
                                <div class="faq-help-icon">
                                    <i class="fa-solid fa-headset"></i>
                                </div>
                                <h3 class="faq-help-title text-white">{{ __('Butuh Bantuan Lainnya?') }}</h3>
                                <p class="faq-help-desc">
                                    {{ __('Tim customer care kami siap memberikan informasi seputar layanan medis, rawat inap, dan pendaftaran 24 jam sehari.') }}
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="faq-btn-wa">
                                        <i class="fa-brands fa-whatsapp fs-5"></i> {{ __('Chat WhatsApp') }}
                                    </a>
                                    <a href="{{ route('customer-information.index') }}" class="faq-btn-info">
                                        <i class="fa-solid fa-circle-info"></i> {{ __('Informasi Pelanggan') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Accordion FAQ -->
                    <div class="col-lg-7">
                        <div class="accordion faq-accordion" id="faqAccordion">

                            <!-- FAQ 1: Jam Kunjungan -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingFaqOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqOne" aria-expanded="true" aria-controls="collapseFaqOne">
                                        <span class="faq-icon-bullet"><i class="fa-solid fa-clock"></i></span>
                                        <span>{{ __('Kapan waktu jam kunjungan (besuk) pasien rawat inap?') }}</span>
                                    </button>
                                </h3>
                                <div id="collapseFaqOne" class="accordion-collapse collapse show" aria-labelledby="headingFaqOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-3">
                                            {{ __('Untuk menjaga ketenangan, kenyamanan, dan proses istirahat pasien, jam kunjungan diatur menjadi dua sesi setiap harinya:') }}
                                        </p>
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="faq-time-card">
                                                    <div class="faq-time-title">
                                                        <i class="fa-solid fa-calendar-day text-primary"></i>
                                                        <span>{{ __('Senin – Sabtu') }}</span>
                                                    </div>
                                                    <div class="faq-time-row">
                                                        <span class="text-muted"><i class="fa-regular fa-sun text-warning me-1"></i> {{ __('Pagi') }}</span>
                                                        <span class="fw-bold text-dark">09.30 - 11.00 WIB</span>
                                                    </div>
                                                    <div class="faq-time-row">
                                                        <span class="text-muted"><i class="fa-solid fa-cloud-sun text-info me-1"></i> {{ __('Sore') }}</span>
                                                        <span class="fw-bold text-dark">17.00 - 18.30 WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="faq-time-card">
                                                    <div class="faq-time-title">
                                                        <i class="fa-regular fa-calendar-check text-primary"></i>
                                                        <span>{{ __('Minggu & Hari Libur') }}</span>
                                                    </div>
                                                    <div class="faq-time-row">
                                                        <span class="text-muted"><i class="fa-regular fa-sun text-warning me-1"></i> {{ __('Pagi') }}</span>
                                                        <span class="fw-bold text-dark">09.30 - 11.30 WIB</span>
                                                    </div>
                                                    <div class="faq-time-row">
                                                        <span class="text-muted"><i class="fa-solid fa-cloud-sun text-info me-1"></i> {{ __('Sore') }}</span>
                                                        <span class="fw-bold text-dark">16.30 - 18.30 WIB</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 2: Batas Jumlah Pengunjung -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingFaqTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqTwo" aria-expanded="false" aria-controls="collapseFaqTwo">
                                        <span class="faq-icon-bullet"><i class="fa-solid fa-users"></i></span>
                                        <span>{{ __('Berapa jumlah maksimal pengunjung untuk setiap pasien?') }}</span>
                                    </button>
                                </h3>
                                <div id="collapseFaqTwo" class="accordion-collapse collapse" aria-labelledby="headingFaqTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-2">
                                            {!! __('Demi ketenangan dan kelancaran perawatan medis pasien, setiap pasien diperkenankan menerima :badge secara bergantian.', ['badge' => '<span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">' . __('maksimal 2 pengunjung') . '</span>']) !!}
                                        </p>
                                        <p class="mb-0 text-muted">
                                            {{ __('Apabila keluarga atau kerabat yang datang berjumlah lebih dari dua orang, dimohon untuk menunggu di ruang tunggu yang telah disediakan dan bergantian saat memasuki kamar rawat inap.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 3: Batas Usia Anak -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingFaqThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqThree" aria-expanded="false" aria-controls="collapseFaqThree">
                                        <span class="faq-icon-bullet"><i class="fa-solid fa-child-reaching"></i></span>
                                        <span>{{ __('Apakah anak-anak diizinkan berkunjung menjenguk pasien?') }}</span>
                                    </button>
                                </h3>
                                <div id="collapseFaqThree" class="accordion-collapse collapse" aria-labelledby="headingFaqThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-2">
                                            {!! __('Anak-anak :badge tidak diizinkan berkunjung ke area ruang perawatan pasien.', ['badge' => '<span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">' . __('di bawah usia 12 tahun') . '</span>']) !!}
                                        </p>
                                        <p class="mb-0 text-muted">
                                            {{ __('Kebijakan ini diterapkan demi keselamatan dan perlindungan imunitas anak terhadap risiko paparan kuman serta penyakit menular di lingkungan rumah sakit.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 4: Kondisi Kesehatan Pengunjung -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingFaqFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqFour" aria-expanded="false" aria-controls="collapseFaqFour">
                                        <span class="faq-icon-bullet"><i class="fa-solid fa-heart-pulse"></i></span>
                                        <span>{{ __('Bagaimana ketentuan bagi pengunjung yang sedang merasa kurang sehat?') }}</span>
                                    </button>
                                </h3>
                                <div id="collapseFaqFour" class="accordion-collapse collapse" aria-labelledby="headingFaqFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-2">
                                            {!! __('Demi kesehatan pasien yang sedang dalam masa penyembuhan, kami menyarankan untuk :badge terlebih dahulu apabila kondisi badan Anda sedang tidak fit.', ['badge' => '<span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1">' . __('tidak melakukan kunjungan') . '</span>']) !!}
                                        </p>
                                        <p class="mb-0 text-muted">
                                            {{ __('Jika mengalami gejala seperti demam, flu, batuk, pilek, atau keluhan infeksi lainnya, mohon beristirahat hingga pulih sebelum merencanakan kunjungan kembali.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 5: Tata Tertib & Kenyamanan -->
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingFaqFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaqFive" aria-expanded="false" aria-controls="collapseFaqFive">
                                        <span class="faq-icon-bullet"><i class="fa-solid fa-volume-xmark"></i></span>
                                        <span>{{ __('Apa saja tata tertib yang perlu ditaati selama berkunjung?') }}</span>
                                    </button>
                                </h3>
                                <div id="collapseFaqFive" class="accordion-collapse collapse" aria-labelledby="headingFaqFive" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <ul class="mb-3 ps-3">
                                            <li class="mb-1"><strong>{{ __('Ketenangan:') }}</strong> {{ __('Mohon untuk tidak berbicara keras atau membuat kegaduhan di lingkungan ruang rawat inap.') }}</li>
                                            <li class="mb-1"><strong>{{ __('Kebersihan Tangan:') }}</strong> {{ __('Gunakan cairan antiseptik hand sanitizer yang disediakan sebelum dan setelah memasuki kamar pasien.') }}</li>
                                            <li class="mb-1"><strong>{{ __('Ketepatan Waktu:') }}</strong> {{ __('Harap mengakhiri kunjungan tepat pada saat jam kunjungan berakhir agar pasien dapat beristirahat.') }}</li>
                                        </ul>
                                        <div class="p-3 rounded-3 bg-light text-muted small fst-italic border border-light-subtle">
                                            <i class="fa-solid fa-heart text-danger me-1"></i>
                                            {{ __('Terima kasih atas kesediaan Anda dalam membantu kami menjaga ketenangan dan kenyamanan pasien selama di rawat di Rumah Sakit St. Elisabeth Semarang.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Jam Kunjungan & Ketentuan Pengunjung -->
        {{-- <section id="customer-info">
            <div class="container">
                <!-- Judul utama -->
                <div class="section-title text-center mb-3">
                    <span class="ci-eyebrow mb-3"><i class="fa-solid fa-hospital-user"></i> {{ __('Informasi Pelanggan') }}</span>
                    <h2 class="display-8 fw-bold">{{ __('Jam Kunjungan Pasien') }}</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">{{ __('Untuk menjaga ketenangan dan kenyamanan pasien, kami mohon Anda dapat melakukan kunjungan sesuai dengan ketentuan berikut:') }}</p>
                </div>

                <!-- Kartu jam kunjungan -->
                <div class="row g-4 justify-content-center mb-5">
                    <!-- Senin - Sabtu -->
                    <div class="col-md-5">
                        <div class="ci-visit-card p-4 p-md-5 text-center">
                            <div class="ci-visit-icon mx-auto mb-3">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <h4 class="fw-bold mb-4" style="color: var(--secondary-darker-color);">{{ __('Senin – Sabtu') }}</h4>
                            <div class="d-flex flex-column gap-2">
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-regular fa-sun text-warning"></i> {{ __('Pagi') }}</span>
                                    <span class="ci-time-value">09.30 – 11.00</span>
                                </div>
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-solid fa-cloud-sun text-info"></i> {{ __('Sore') }}</span>
                                    <span class="ci-time-value">17.00 – 18.30</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Minggu & Hari Libur -->
                    <div class="col-md-5">
                        <div class="ci-visit-card p-4 p-md-5 text-center">
                            <div class="ci-visit-icon mx-auto mb-3">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <h4 class="fw-bold mb-4" style="color: var(--secondary-darker-color);">{{ __('Minggu & Hari Libur') }}</h4>
                            <div class="d-flex flex-column gap-2">
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-regular fa-sun text-warning"></i> {{ __('Pagi') }}</span>
                                    <span class="ci-time-value">09.30 – 11.30</span>
                                </div>
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-solid fa-cloud-sun text-info"></i> {{ __('Sore') }}</span>
                                    <span class="ci-time-value">16.30 – 18.30</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ketentuan Pengunjung -->
                <div class="section-title text-center mb-4">
                    <h3 class="fw-bold" style="color: var(--secondary-darker-color);">{{ __('Ketentuan Pengunjung') }}</h3>
                    <div class="divider"></div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="ci-rule-card p-4 d-flex flex-column h-100 text-center">
                                    <div class="ci-rule-icon mx-auto mb-3">
                                        <i class="fa-solid fa-heart-pulse"></i>
                                    </div>
                                    <p class="mb-0 text-muted">{!! __('Demi Kesehatan Anda, kami menyarankan untuk :strong terlebih dahulu apabila kondisi badan sedang tidak fit.', ['strong' => '<strong>' . __('tidak melakukan kunjungan') . '</strong>']) !!}</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="ci-rule-card p-4 d-flex flex-column h-100 text-center">
                                    <div class="ci-rule-icon mx-auto mb-3">
                                        <i class="fa-solid fa-volume-xmark"></i>
                                    </div>
                                    <p class="mb-0 text-muted">{!! __('Mohon untuk :strong1 dan berkunjung secara bergantian (:strong2 untuk tiap pasien).', ['strong1' => '<strong>' . __('tidak berbicara keras') . '</strong>', 'strong2' => '<strong>' . __('maksimal 2 pengunjung') . '</strong>']) !!}</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="ci-rule-card p-4 d-flex flex-column h-100 text-center">
                                    <div class="ci-rule-icon mx-auto mb-3">
                                        <i class="fa-solid fa-child-reaching"></i>
                                    </div>
                                    <p class="mb-0 text-muted">{!! __('Anak-anak :strong tidak diizinkan berkunjung.', ['strong' => '<strong>' . __('di bawah usia 12 tahun') . '</strong>']) !!}</p>
                                </div>
                            </div>
                        </div>

                        <div class="ci-thankyou text-center p-4 mt-4">
                            <p class="mb-0 fst-italic mx-auto" style="max-width: 750px;"><i class="fa-solid fa-heart me-2"></i>{{ __('Terima kasih atas kesediaan Anda dalam membantu kami menjaga ketenangan dan kenyamanan pasien selama di rawat di Rumah Sakit St. Elisabeth Semarang.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}

        {{-- <section id="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">95+</div>
                            <div class="stat-text">{{ __('Tahun Pengalaman') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">150+</div>
                            <div class="stat-text">{{ __('Dokter Ahli') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <div class="stat-item">
                            <div class="stat-number">{{ __('Paripurna') }}</div>
                            <div class="stat-text">{{ __('Akreditasi KARS') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-text">{{ __('Pelayanan Prima') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section> --}}

        <section id="app-section">
            <!-- Decorative background elements -->
            <div class="app-bg-shape app-bg-shape-1"></div>
            <div class="app-bg-shape app-bg-shape-2"></div>
            
            <div class="container position-relative z-1">
                <div class="row align-items-center mb-5 pb-lg-4">
                    <div class="col-lg-6 mb-5 mb-lg-0 app-content pe-lg-5">
                        <span class="badge bg-primary text-white mb-3 px-3 py-2 rounded-pill fw-semibold shadow-sm">Elisameds App</span>
                        <h2 class="display-5 mb-4">{{ __('Kemudahan Dalam Genggaman Anda') }}</h2>
                        <p class="lead mb-4" style="color: #4a5568;">{!! __('Aplikasi :strong hadir untuk menyederhanakan layanan kesehatan Anda di RS St. Elisabeth Semarang. Dari pendaftaran hingga riwayat medis, semuanya lebih praktis.', ['strong' => '<strong>' . __('Elisameds') . '</strong>']) !!}</p>

                        <ul class="list-unstyled mb-5">
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>{{ __('Reservasi dokter dan klinik secara online') }}</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>{{ __('Akses riwayat kesehatan dengan aman') }}</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>{{ __('Info antrean dan jadwal dokter secara real-time') }}</span></li>
                        </ul>

                        <div class="d-flex flex-wrap gap-3 mt-4">
                            <a href="https://play.google.com/store/apps/details?id=com.elisameds.app" class="btn-store shadow">
                                <i class="fab fa-google-play"></i>
                                <div class="store-text">
                                    <span class="small-text">GET IT ON</span>
                                    <span class="large-text">Google Play</span>
                                </div>
                            </a>
                            <a href="#" class="btn-store shadow">
                                <i class="fab fa-apple"></i>
                                <div class="store-text">
                                    <span class="small-text">Download on the</span>
                                    <span class="large-text">App Store</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="app-mockup-container">
                            <div class="mobile-frame app-mockup-secondary">
                                <img src="{{ asset('images/elisameds/elisameds-2.webp') }}" alt="Elisameds Feature">
                            </div>
                            <div class="mobile-frame app-mockup-main">
                                <img src="{{ asset('images/elisameds/elisameds-1.webp') }}" alt="Elisameds Main App">
                            </div>
                            
                            <div class="floating-badge">
                                <div class="icon-box"><i class="fa-solid fa-calendar-check"></i></div>
                                <div>
                                    <strong>{{ __('Reservasi Mudah') }}</strong>
                                    <span>{{ __('Tanpa antre lama') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row app-features-mockups mt-5 pt-4 justify-content-center">
                    <div class="col-lg-4 col-md-6 mb-5 d-flex justify-content-center">
                        <div class="static-mockup-wrapper">
                            <div class="mockup-glow"></div>
                            <div class="static-mobile-frame">
                                <img src="{{ asset('images/elisameds/elisameds-3.webp') }}" alt="Elisameds Feature 1">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-5 d-flex justify-content-center">
                        <div class="static-mockup-wrapper">
                            <div class="mockup-glow"></div>
                            <div class="static-mobile-frame">
                                <img src="{{ asset('images/elisameds/elisameds-4.webp') }}" alt="Elisameds Feature 2">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-5 d-flex justify-content-center">
                        <div class="static-mockup-wrapper">
                            <div class="mockup-glow"></div>
                            <div class="static-mobile-frame">
                                <img src="{{ asset('images/elisameds/elisameds-5.webp') }}" alt="Elisameds Feature 3">
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
        'resources/js/promotions/promotions.js',
        'resources/js/components/back-to-top.js'
    ])
    @include('components.json-ld')
    </body>
</html>