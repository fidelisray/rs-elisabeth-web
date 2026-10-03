<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Temukan dokter spesialis terbaik di RS St. Elisabeth Semarang. Cari berdasarkan spesialisasi, nama, atau klinik dan lihat jadwal praktik dokter.">
    <title>Dokter Kami - RS St. Elisabeth Semarang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/dokter.css',
        'resources/css/jadwal_dokter.css',
        'resources/css/search-and-quick-access.css'
    ])
</head>
<body>

    @include('components.navbar')

<main>
        <section id="hero-section">
            <div class="container">
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dokter Kami</li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h2 class="hero-title">Dokter Kami</h2>
                        <p class="hero-subtitle">Tim Dokter Berpengalaman kami akan selalu siap sedia untuk memberikan pelayanan kesehatan terbaik dan professional untuk anda dan keluarga.</p>
                    </div>
                </div>
            </div>
        </section>
    <section id="doctor-filter-selection">
        <!-- Toolbar / Search Widget -->
        <div class="container">
            <div class="search-widget shadow-sm">
                <ul class="nav nav-tabs" id="searchTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('klinik') ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#doctor"
                            type="button"><i class="fas fa-user-md me-2"></i>Cari Spesialisasi & Dokter</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ request('klinik') ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#clinic" type="button"><i
                                class="fas fa-hospital me-2"></i>Cari Klinik</button>
                    </li>
                </ul>
                <div class="tab-content" id="searchTabsContent">
                    <!-- Tab Spesialisasi & Dokter -->
                    <div class="tab-pane fade {{ request('klinik') ? '' : 'show active' }}" id="doctor">
                        <div class="row g-3 align-items-center">
                            <!-- Dropdown Spesialisasi -->
                            <div class="col-md-4">
                                <div class="dropdown w-100">
                                    <button class="btn form-select text-start" style="height: 45px;" type="button" id="clinicDropdown" data-bs-toggle="dropdown" data-selected="">
                                        Pilih Spesialisasi
                                    </button>
                                    <div class="dropdown-menu p-0 w-100">
                                        <div class="p-2 border-bottom">
                                            <input type="text" id="clinicSearch" class="form-control" placeholder="Cari Spesialisasi...">
                                        </div>
                                        <div id="clinicList" class="clinic-list" style="max-height: 250px; overflow-y: auto;">
                                            @foreach ($spesialisasi as $spesialis)
                                            <button type="button" class="dropdown-item clinic-option" data-value="{{ $spesialis->Name }}" data-code="{{ $spesialis->Code }}">
                                                {{ ucwords(strtolower($spesialis->Name)) }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Search Nama Dokter -->
                            <div class="col-md-4">
                                <input type="text" id="searchKeyword" class="form-control" style="height: 45px;" placeholder="Nama Dokter">
                            </div>
                            <!-- Buttons -->
                            <div class="col-md-4 d-flex gap-2">
                                <button type="button" class="btn btn-primary-custom-sm flex-grow-1"><i class="fas fa-search me-2"></i>Cari</button>
                                <button type="button" id="btnReset" class="btn btn-outline-secondary px-3" style="border-radius: 50px;" title="Reset Pencarian"><i class="fas fa-undo"></i></button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Klinik -->
                    <div class="tab-pane fade {{ request('klinik') ? 'show active' : '' }}" id="clinic">
                        <form class="row g-3 align-items-center" action="{{{ route('dokter.index') }}}" method="GET">
                            <div class="col-md-8">
                                <input type="text" name="klinik" class="form-control" style="height: 45px;" placeholder="Nama Klinik" value="{{ request('klinik') }}">
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button class="btn btn-primary-custom-sm flex-grow-1" type="submit"><i class="fas fa-search me-2"></i>Cari</button>
                                <a href="{{{ route('dokter.index') }}}" class="btn btn-outline-secondary px-3 d-flex align-items-center justify-content-center" style="border-radius: 50px;" title="Reset Pencarian"><i class="fas fa-undo"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid main-content py-3">
        <section class="container" id="dokter-container">
            
            <div id="search-summary-container" class="d-none mb-4">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm border-start border-4 border-primary">
                    <h6 class="mb-0 fw-semibold text-muted"><span id="search-summary-label">Hasil Pencarian:</span> <span class="text-dark fw-bold" id="search-summary-keyword"></span></h6>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6">
                        Ditemukan <span id="search-summary-count" class="fw-bold">0</span> dokter
                    </span>
                </div>
            </div>

            <div id="daftar-dokter">

            </div>

            <!-- ================================================= -->
            <!-- ---------------Detail Dokter Modal--------------- -->
            <!-- ================================================= -->
    
            <div class="modal fade" id="detailDokter" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dokter">
                    <div class="modal-content border-0 bg-transparent">
                        <div class="doctor-card">
                            <!-- HEADER -->
                            <div class="profile-header">
                                <button 
                                    type="button"
                                    class="btn-close mb-2 float-end close-btn"
                                    data-bs-dismiss="modal">
                                    <i class="fa-solid fa-xmark" style="color: #fff"></i>
                                </button>
                                <div class="d-flex align-items-center gap-3">
                                    <div id="foto-dokter" class="doctor-photo">
                                        
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold" id="namaDokter">
                                        </h5>
                                        <span class="speciality-badge">
                                            <p class="speciality-title fw-bold"></p>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <!-- TABS -->
                            <ul class="nav nav-tabs justify-content-center mt-2" id="doctorTabs" role="tablist">
                                <li class="nav-item flex-fill text-center">
                                    <button
                                        class="nav-link active w-100"
                                        id="tentang-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#tentang-pane"
                                        type="button">
                                        Tentang
                                    </button>
                                </li>

                                <li class="nav-item flex-fill text-center">
                                    <button
                                        class="nav-link w-100"
                                        id="jadwal-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#jadwal-pane"
                                        type="button">
                                        Jadwal
                                    </button>
                                </li>
                            </ul>
                            <!-- CONTENT -->
                            <div class="content-section">
                                <div class="tab-content">
    
                                    <div
                                        class="tab-pane fade show active"
                                        id="tentang-pane">
    
                                        <div class="section-tentang col-lg">
                                            <div class="section-header row">
                                                <div class="col-12">
                                                    <div class="section-title">
                                                        Tentang Dokter
                                                    </div>
                                                    <p class="text-secondary">
                                                        Dokter berpengalaman yang siap memberikan pelayanan
                                                        kesehatan terbaik.
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="dokter-info row gx-3 gy-3">
                                                <!-- Location -->
                                                <div class="col-12 col-lg-4">
                                                    <div class="info-box">
                                                        <div class="info-icon">
                                                            <i class="bi bi-geo-alt"></i>
                                                        </div>
                                                        <div>
                                                            <div class="info-label">Lokasi</div>
                                                            <div>Semarang, Indonesia</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Experience -->
                                                <div class="col-12 col-lg-4">
                                                    <div class="info-box">
                                                        <div class="info-icon">
                                                            <i class="bi bi-briefcase"></i>
                                                        </div>
                                                        <div>
                                                            <div class="info-label">Pengalaman</div>
                                                            <strong>Lebih Dari 5 Tahun</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Education -->
                                                <div class="col-12 col-lg-4">
                                                    <div class="info-box">
                                                        <div class="info-icon">
                                                            <i class="bi bi-mortarboard"></i>
                                                        </div>
                                                        <div>
                                                            <div class="info-label">Pendidikan</div>
                                                            <strong>Universitas Terkemuka</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- CTA -->
                                            <div class="info-button row">
                                                <div class="button-group col-lg-12">
                                                    <button type="button" class="cta-btn mt-4 button-janji">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                        Buat Janji Dokter
                                                        <i class="bi bi-chevron-right float-end"></i>
                                                    </button>
                                                </div>
                                            </div>
    
                                        </div>
    
                                    </div>
    
                                    <!-- SECTION JADWAL -->
                                    <div
                                        class="tab-pane fade"
                                        id="jadwal-pane">
    
                                        <!-- isi jadwal di bawah -->
                                        <div class="section-jadwal col-lg">
                                            
                                            <div class="section-header row">
                                                <div class="col-12">
                                                    <div class="section-title">
                                                        <i class="bi bi-clock me-2"></i>
                                                        Jadwal Praktik
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-schedule-content">
                                                <div id="modal-schedule-cards" class="row">

                                                </div>
                                            </div>

                                            {{-- <div class="schedule-card d-none">
                                                <div class="day-badge"></div>
    
                                                <div>
                                                    <div class="schedule-day"></div>
                                                    <span class="schedule-time">
                                                    </span>
                                                </div>
                                            </div> --}}
                                            
                                            <div class="info-button row">
                                                <div class="button-group col-lg-12">
                                                    <button type="button" class="cta-btn mt-4 button-janji">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                        Buat Janji Dokter
                                                        <i class="bi bi-chevron-right float-end"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
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
        'resources/js/dokter/script.js'
    ])
</body>
</html>