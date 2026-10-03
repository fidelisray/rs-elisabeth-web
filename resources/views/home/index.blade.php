<!doctype html>
<html lang="id">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="Rumah Sakit St. Elisabeth Semarang mengedepankan keselamatan, mutu, dan terpercaya serta menjadi sarana kehadiran cinta dan kuasa Allah, terakreditasi Paripurna oleh Komite Akreditasi Rumah Sakit KARS, dengan layanan IGD 24 Jam, Stroke Terpadu, dan Klinik Neurologi.  ">
      <link rel="icon" type="image/png" href="{{ asset('images/web-icon.webp') }}">
      <title>St. Elisabeth Hospital</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
      @vite([
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
        'resources/css/customer-info-section.css'
      ])
  </head>
  <body>

    @include('components.navbar')

</div>
    <main>
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
                                        <i class="fa-solid fa-heart-pulse me-1 text-warning"></i> Melayani dengan Kasih
                                    </span>
                                </div>
                                <h2 class="text-white fw-bold mb-3 display-6" style="letter-spacing: 0.5px;">RS St. Elisabeth Semarang</h2>
                                <p class="text-white opacity-75 mb-0 mx-auto fs-6 fs-md-5" style="max-width: 650px; line-height: 1.6;">
                                    Hadir dengan sepenuh hati memberikan pelayanan kesehatan terbaik, berteknologi mutakhir, dan profesional bagi Anda dan keluarga.
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
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#promo-banner-carousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
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
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">Cari Dokter</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-2">
                        <a href="https://regonline.rs-elisabeth.com" class="text-decoration-none h-100">
                            <div class="card card-appointment h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-regular fa-calendar appointment-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">Buat Janji</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-3">
                        <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20membuat%20janji%20temu" class="text-decoration-none h-100">
                            <div class="card card-contact h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-brands fa-whatsapp whatsapp-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">Hubungi Kami</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3 quick-access-option-4">
                        <a href="{{{ route('glossary.index') }}}" class="text-decoration-none h-100">
                            <div class="card card-emergency h-100 border-0 rounded-0 shadow-sm">
                                <div class="card-body d-flex flex-column text-center align-items-center justify-content-center text-white py-4 py-md-5">
                                    <i class="fa-solid fa-book-medical glossary-icon mb-2 mb-md-3"></i>
                                    <h5 class="fw-bold m-0 text-white fs-6 fs-md-5">Kamus Medis</h5>
                                    <p class="mb-0 mt-1 mt-md-2 opacity-75 small d-none d-md-block">Temukan penyakit & istilah medis</p>
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
                    <h2 class="fw-bold">Percayakan Kesehatan Anda Bersama Kami</h2>
                    <div class="divider"></div>
                </div>
                <div class="row align-items-center mt-5">
                    <div class="col-md-5 mb-4 mb-md-0 px-md-4">
                        <div class="about-rs mb-5">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-medal"></i>
                                </div>
                                Terakreditasi Paripurna
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">Kami mendapat predikat PARIPURNA dari Komisi Akreditasi Rumah Sakit (KARS), yang merupakan predikat dengan hasil penilaian tertinggi berdasarkan penilaian terhadap manajemen mutu dan keselamatan pasien yang diterapkan di Rumah Sakit.</p>
                        </div>
                        <div class="about-rs mb-5">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                Layanan 24 Jam
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">Kami menyediakan layanan 24 jam untuk memenuhi kebutuhan Kesehatan anda, khususnya bagi anda yang membutuhkan penanganan emergency.</p>
                        </div>
                        <div class="about-rs">
                            <h4 class="fw-bold d-flex align-items-center" style="color: var(--primary-color);">
                                <div class="icon-circle bg-light me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                Service Excellent
                            </h4>
                            <p class="text-muted ms-5 ps-3" style="font-size: 1.1rem; line-height: 1.6;">Berpusat kepada pasien sebagai “Tamu Ilahi”, Kami senantiasa memberikan kualitas pelayanan yang bermutu tinggi dan profesional, dengan tetap memperhatikan aspek keselamatan pasien.</p>
                        </div>
                    </div>
                    <div class="col-md-7 text-center position-relative mt-4 mt-md-0">
                        <div class="position-absolute w-100 h-100 rounded-4 about-bg-accent d-none d-md-block"></div>
                        <img src="{{ asset('images/feature.jpg') }}" class="img-fluid rounded-4 shadow-lg position-relative" alt="Fasilitas RS St. Elisabeth Semarang" style="z-index: 2; object-fit: cover; max-height: 500px; width: 100%;">
                    </div>
                </div>
            </div>
        </section>


        <section id="facilities-and-services" class="py-5">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Fasilitas dan Layanan</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">Pelayanan unggulan dengan dokter spesialis berpengalaman.</p>
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
                                        <a href="{{ route('facilities.index') }}#facility-{{ $facility->slug ?? $facility->id }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Learn More <i class="fas fa-arrow-right ms-2"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="carousel-item active p-5 text-center">
                            <p class="text-muted">Belum ada data fasilitas unggulan.</p>
                        </div>
                        @endforelse
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark p-3 rounded-circle" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark p-3 rounded-circle" aria-hidden="true" style="background-size: 50%;"></span>
                        <span class="visually-hidden">Next</span>
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
                    <h1 class="display-5 fw-bold text-white mb-3">Kesehatan Anda Adalah Prioritas Utama Kami</h1>
                    <p class="fs-5 text-white mb-4">Pelayanan prima dan paripurna dari RS St. Elisabeth Semarang dengan fasilitas berstandar internasional dan tenaga medis profesional yang penuh kasih.</p>
                    <div class="d-flex gap-3 flex-column flex-sm-row">
                        {{-- <button class="btn btn-primary-custom px-4 py-2">Temukan Dokter</button> --}}
                        {{-- <button class="btn btn-outline-light px-4 py-2 fw-semibold rounded-pill">Hubungi Kami</button> --}}
                    </div>
                </div>
            </div>
        </section>

        <section id="search-and-quick-access">
            <h2 class="visually-hidden">Pencarian Layanan dan Akses Cepat</h2>
            <div class="container">
                <div class="search-widget">
                    <ul class="nav nav-tabs" id="searchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#doctor"
                                type="button"><i class="fas fa-user-md me-2"></i>Cari Dokter</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#clinic" type="button"><i
                                    class="fas fa-hospital me-2"></i>Cari Klinik</button>
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
                                    <input type="text" name="nama" class="form-control" placeholder="Nama Dokter">
                                </div>
                                <div class="col-md-4">
                                    <select name="specialty_code" class="form-select">
                                        <option value="" selected>Pilih Spesialisasi</option>
                                        @if(isset($spesialisasi))
                                            @foreach ($spesialisasi as $spesialis)
                                                <option value="{{ $spesialis->Code }}">{{ ucwords(strtolower($spesialis->Name)) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary-custom w-100" type="submit"><i
                                            class="fas fa-search me-2"></i>Cari</button>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="clinic">
                            <form class="row g-3" action="{{{ route('dokter.index') }}}" method="GET">
                                <div class="col-md-8">
                                    <input type="text" name="klinik" class="form-control"
                                        placeholder="Nama Klinik">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary-custom w-100" type="submit"><i
                                            class="fas fa-search me-2"></i>Cari</button>
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
                <h2 class="display-8 fw-bold">Paket dan Promo</h2>
                <div class="divider"></div>
                {{-- <p class="text-muted mt-3">Temukan penawaran terbaik untuk layanan kesehatan Anda</p> --}}
            </div>
            <div class="container pb-5">
                <div class="row g-4 justify-content-center" id="promoTrack">
                    @forelse($promotions as $promo)
                        <div class="col-md-6 col-lg-3 {{ $loop->iteration > 4 ? '' : 'position-relative' }}">
                            <div class="promo-card" {!! $loop->iteration <= 4 ? 'data-bs-toggle="modal" data-bs-target="#promoModal" data-title="'.e($promo->title ?? 'Promo').'" data-desc="'.e($promo->description ?? 'Penawaran spesial dari RS St. Elisabeth Semarang.').'" data-img="'.(!empty($promo->image_url) ? $promo->image_url : asset('images/placeholder.jpg')).'"' : '' !!}>
                                <div class="card {{ $loop->iteration > 4 ? 'position-relative teased-card' : 'h-100' }}">
                                    <img src="{{ !empty($promo->image_url) ? $promo->image_url : asset('images/placeholder.jpg') }}" class="card-img-top" alt="{{ $promo->title ?? 'Promo' }}">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-primary mb-2" style="text-transform: capitalize;">{{ $promo->title ?? 'Promo' }}</h5>
                                        <p class="card-text text-muted small flex-grow-1">{{ Str::limit($promo->excerpt ?? strip_tags($promo->description ?? 'Penawaran spesial dari RS St. Elisabeth Semarang.'), 60) }}</p>
                                        <div class="mt-3 text-end">
                                            <span class="text-secondary fw-semibold small">Lihat Detail <i class="fa-solid fa-arrow-right ms-1"></i></span>
                                        </div>
                                    </div>
                                    @if($loop->iteration > 4)
                                    <div class="tease-overlay position-absolute top-0 bottom-0 start-0 end-0" style="background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.95) 35%, rgba(255,255,255,1) 100%); z-index: 5; pointer-events: none;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-tags" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">Belum Ada Paket & Promo</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">Saat ini belum ada penawaran spesial terbaru. Silakan periksa kembali nanti untuk promo menarik dari kami.</p>
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
                                    <h6 class="text-uppercase fw-bold mb-2" style="color: var(--secondary-color); letter-spacing: 1px;">Info Spesial</h6>
                                    <h2 id="promoModalTitle" class="fw-bold mb-4" style="color: var(--primary-color);"></h2>
                                    <p id="promoModalDesc" class="text-secondary fs-5 lh-base mb-5"></p>
                                    
                                    <div class="mt-auto border-top pt-4">
                                        <p class="text-muted fw-semibold mb-3">Dapatkan penawaran ini sekarang!</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center flex-grow-1 justify-content-center">
                                                <i class="fa-brands fa-whatsapp fs-5 me-2"></i> Hubungi Kami
                                            </a>
                                            <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-semibold flex-grow-0" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="{{{ route('promotions.index') }}}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="Lihat Penawaran Menarik Lainnya">
                    Lihat Penawaran Menarik Lainnya <i class="fa-solid fa-arrow-down ms-2"></i>
                </a>
            </div>
        </section>

        <!-- Latest Articles Section -->
        <section id="latest-articles" class="news-section pt-2 pb-5">
            <div class="section-title text-center mb-5 mt-5">
                <h2 class="display-8 fw-bold">Artikel Kesehatan</h2>
                <div class="divider"></div>
            </div>
            <div class="container pb-5">
                <div class="row g-4 justify-content-center">
                    @forelse($latestArticles as $item)
                    <div class="col-md-6 col-lg-3 position-relative">
                        <div class="news-card h-100">
                            <div class="news-card-img-wrapper">
                                <a href="{{{ route('articles.show', ['slug' => $item->slug]) }}}">
                                    <img src="{{ $item->image_url ?? asset('images/hero.jpg') }}" class="news-card-img" alt="{{ $item->title }}">
                                </a>
                            </div>
                            <div class="news-card-body p-3">
                                <div class="news-date small">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $item->created_at?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}
                                </div>
                                <a href="{{{ route('articles.show', ['slug' => $item->slug]) }}}">
                                    <h3 class="news-title fs-6">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt small mb-3">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 80) }}</p>
                                <a href="{{{ route('articles.show', ['slug' => $item->slug]) }}}" class="news-read-more small mt-auto">
                                    Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        @if($loop->iteration > 4)
                            <div class="position-absolute top-0 bottom-0 start-0 end-0" style="background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.8) 70%, rgba(255,255,255,1) 100%); z-index: 5; border-radius: var(--bs-border-radius, 0.375rem); pointer-events: none;"></div>
                        @endif
                    </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">Artikel Belum Tersedia</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">Kami sedang menyiapkan artikel kesehatan terbaru untuk Anda. Silakan nantikan informasi bermanfaat selanjutnya.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
                <div class="text-center mt-5">
                    <a href="{{{ route('articles.index') }}}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="Lihat Semua Artikel">
                        Lihat Semua Artikel <i class="fa-solid fa-arrow-right ms-2"></i>
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
                    <div class="col-md-6 col-lg-3 position-relative">
                        <div class="news-card h-100">
                            <div class="news-card-img-wrapper">
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}">
                                    <img src="{{ $item->image_url ?? asset('images/hero.jpg') }}" class="news-card-img" alt="{{ $item->title }}">
                                </a>
                            </div>
                            <div class="news-card-body p-3">
                                <div class="news-date small d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $item->created_at?->translatedFormat('d M Y') ?? now()->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="badge bg-primary rounded-pill">{{ $item->category ?? 'Berita Umum' }}</span>
                                </div>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}">
                                    <h3 class="news-title fs-6">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt small mb-3">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 80) }}</p>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}" class="news-read-more small mt-auto">
                                    Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        @if($loop->iteration > 4)
                            <div class="position-absolute top-0 bottom-0 start-0 end-0" style="background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,0.8) 70%, rgba(255,255,255,1) 100%); z-index: 5; border-radius: var(--bs-border-radius, 0.375rem); pointer-events: none;"></div>
                        @endif
                    </div>
                    @empty
                    <div class="col-12 reveal-on-scroll">
                        <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                            <div class="mb-3">
                                <i class="fa-solid fa-bullhorn" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">Berita Belum Tersedia</h5>
                            <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">Belum ada berita terbaru seputar rumah sakit saat ini. Kami akan segera memperbarui informasi terkini untuk Anda.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
                <div class="text-center mt-5">
                    <a href="{{{ route('news.index') }}}" class="btn btn-bouncing px-5 py-3 rounded-pill fw-bold shadow-lg" aria-label="Lihat Semua Berita">
                        Lihat Semua Berita <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- Jam Kunjungan & Ketentuan Pengunjung -->
        <section id="customer-info">
            <div class="container">
                <!-- Judul utama -->
                <div class="section-title text-center mb-3">
                    <span class="ci-eyebrow mb-3"><i class="fa-solid fa-hospital-user"></i> Informasi Pelanggan</span>
                    <h2 class="display-8 fw-bold">Jam Kunjungan Pasien</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">Untuk menjaga ketenangan dan kenyamanan pasien, kami mohon Anda dapat melakukan kunjungan sesuai dengan ketentuan berikut:</p>
                </div>

                <!-- Kartu jam kunjungan -->
                <div class="row g-4 justify-content-center mb-5">
                    <!-- Senin - Sabtu -->
                    <div class="col-md-5">
                        <div class="ci-visit-card p-4 p-md-5 text-center">
                            <div class="ci-visit-icon mx-auto mb-3">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <h4 class="fw-bold mb-4" style="color: var(--secondary-darker-color);">Senin – Sabtu</h4>
                            <div class="d-flex flex-column gap-2">
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-regular fa-sun text-warning"></i> Pagi</span>
                                    <span class="ci-time-value">09.30 – 11.00</span>
                                </div>
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-solid fa-cloud-sun text-info"></i> Sore</span>
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
                            <h4 class="fw-bold mb-4" style="color: var(--secondary-darker-color);">Minggu & Hari Libur</h4>
                            <div class="d-flex flex-column gap-2">
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-regular fa-sun text-warning"></i> Pagi</span>
                                    <span class="ci-time-value">09.30 – 11.30</span>
                                </div>
                                <div class="ci-time-row">
                                    <span class="ci-time-label"><i class="fa-solid fa-cloud-sun text-info"></i> Sore</span>
                                    <span class="ci-time-value">16.30 – 18.30</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ketentuan Pengunjung -->
                <div class="section-title text-center mb-4">
                    <h3 class="fw-bold" style="color: var(--secondary-darker-color);">Ketentuan Pengunjung</h3>
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
                                    <p class="mb-0 text-muted">Demi kesehatan Anda, kami menyarankan untuk <strong>tidak melakukan kunjungan</strong> terlebih dahulu apabila kondisi badan sedang tidak fit.</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="ci-rule-card p-4 d-flex flex-column h-100 text-center">
                                    <div class="ci-rule-icon mx-auto mb-3">
                                        <i class="fa-solid fa-volume-xmark"></i>
                                    </div>
                                    <p class="mb-0 text-muted">Mohon untuk <strong>tidak berbicara keras</strong> dan berkunjung secara bergantian (<strong>maksimal 2 pengunjung</strong> untuk tiap pasien).</p>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="ci-rule-card p-4 d-flex flex-column h-100 text-center">
                                    <div class="ci-rule-icon mx-auto mb-3">
                                        <i class="fa-solid fa-child-reaching"></i>
                                    </div>
                                    <p class="mb-0 text-muted">Anak-anak <strong>di bawah usia 12 tahun</strong> tidak diizinkan berkunjung.</p>
                                </div>
                            </div>
                        </div>

                        <div class="ci-thankyou text-center p-4 mt-4">
                            <p class="mb-0 fst-italic mx-auto" style="max-width: 750px;"><i class="fa-solid fa-heart me-2"></i>Terima kasih atas kesediaan Anda dalam membantu kami menjaga ketenangan dan kenyamanan pasien selama dirawat di <span class="d-inline-block">Rumah Sakit St. Elisabeth Semarang.</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">95+</div>
                            <div class="stat-text">Tahun Pengalaman</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-number">150+</div>
                            <div class="stat-text">Dokter Ahli</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <div class="stat-item">
                            <div class="stat-number">Paripurna</div>
                            <div class="stat-text">Akreditasi KARS</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-text">Pelayanan Prima</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="app-section">
            <!-- Decorative background elements -->
            <div class="app-bg-shape app-bg-shape-1"></div>
            <div class="app-bg-shape app-bg-shape-2"></div>
            
            <div class="container position-relative z-1">
                <div class="row align-items-center mb-5 pb-lg-4">
                    <div class="col-lg-6 mb-5 mb-lg-0 app-content pe-lg-5">
                        <span class="badge bg-primary text-white mb-3 px-3 py-2 rounded-pill fw-semibold shadow-sm">Elisameds App</span>
                        <h2 class="display-5 mb-4">Kemudahan Dalam Genggaman Anda</h2>
                        <p class="lead mb-4" style="color: #4a5568;">Aplikasi <strong>Elisameds</strong> hadir untuk menyederhanakan layanan kesehatan Anda di RS St. Elisabeth Semarang. Dari pendaftaran hingga riwayat medis, semuanya lebih praktis.</p>

                        <ul class="list-unstyled mb-5">
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>Reservasi dokter dan klinik secara online</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>Akses riwayat kesehatan dengan aman</span></li>
                            <li class="mb-3 d-flex align-items-center"><i class="fas fa-check-circle me-3 fs-5" style="color: var(--primary-color);"></i> <span>Info antrean dan jadwal dokter secara real-time</span></li>
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
                                    <strong>Reservasi Mudah</strong>
                                    <span>Tanpa antre lama</span>
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
                            <li class="footer-list"><a href="{{{ route("tentang-kami.index") }}}"><i class="fa-solid fa-caret-right"></i> Tentang Kami</a></li>
                            <li class="footer-list"><a href="{{{ route("news.index") }}}"><i class="fa-solid fa-caret-right"></i> Elisanews</a></li>
                            {{-- <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Artikel</a></li> --}}
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Hubungi Kami</a></li>
                            {{-- <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Rekanan</a></li> --}}
                            <li class="footer-list"><a href="{{{ route("glossary.index") }}}"><i class="fa-solid fa-caret-right"></i> Perpustakaan Online</a></li>
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
        'resources/js/promotions/promotions.js',
        'resources/js/components/back-to-top.js'
    ])
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Hospital",
      "name": "RS St. Elisabeth Semarang",
      "image": "{{ asset('images/logo.png') }}",
      "@@id": "{{ url('/') }}",
      "url": "{{ url('/') }}",
      "telephone": "(024) 8502244",
      "address": {
        "@@type": "PostalAddress",
        "streetAddress": "Jl. Kawi No.1",
        "addressLocality": "Semarang",
        "addressRegion": "Jawa Tengah",
        "postalCode": "50252",
        "addressCountry": "ID"
      }
    }
    </script>
  </body>
</html>