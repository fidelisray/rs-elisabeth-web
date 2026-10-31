<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.seo-meta', ['title' => __('Dokter Kami') . ' - RS St. Elisabeth Semarang'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/dokter.css',
        'resources/css/jadwal_dokter.css'
    ])
</head>
<body>

    @include('components.navbar')

    <main class="doctor-page-canvas">
        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-user-doctor',
            'badgeText' => __('Tenaga Medis Profesional'),
            'title' => __('Dokter Kami'),
            'subtitle' => __('Tim Dokter Berpengalaman kami akan selalu siap sedia untuk memberikan pelayanan kesehatan terbaik dan professional untuk anda dan keluarga.'),
            'headingTag' => 'h2',
        ])
    {{-- Data spesialisasi untuk mapping dan pencarian client-side --}}
    <script id="specialties-json" type="application/json">@json($spesialisasi ?? [])</script>

    <div class="doctor-layout main-content py-4">
        <section id="dokter-container" aria-label="{{ __('Daftar dokter') }}">
            <div class="doctor-grid">

                <!-- ========================================== -->
                <!-- SIDEBAR FILTER                             -->
                <!-- Mobile <768: bottom sheet | 768-1199: drawer | >=1200: sidebar sticky -->
                <!-- ========================================== -->
                <aside class="doctor-sidebar-col" aria-label="{{ __('Filter dokter') }}">
                    <div class="offcanvas-xl offcanvas-start doctor-filter-panel" tabindex="-1" id="doctorFilterOffcanvas" aria-labelledby="doctorFilterOffcanvasLabel">
                        <div class="doctor-sheet-handle d-md-none" aria-hidden="true"></div>
                        <div class="offcanvas-header doctor-sheet-header">
                            <h2 class="offcanvas-title doctor-sheet-title" id="doctorFilterOffcanvasLabel">
                                <i class="fas fa-sliders-h" aria-hidden="true"></i>
                                {{ __('Filter Dokter') }}
                            </h2>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#doctorFilterOffcanvas" aria-label="{{ __('Tutup') }}"></button>
                        </div>

                        <div class="offcanvas-body">
                            <form class="doctor-filter-card" role="search" id="doctorFilterForm" novalidate>
                                <header class="doctor-filter-head">
                                    <div>
                                        <h2 class="doctor-filter-title">{{ __('Cari Dokter') }}</h2>
                                        <p class="doctor-filter-count" id="sidebarResultCount" role="status" aria-live="polite"></p>
                                    </div>
                                    <button type="button" id="btnResetAll" class="btn-reset-all" hidden>
                                        <i class="fas fa-rotate-left" aria-hidden="true"></i>
                                        {{ __('Reset semua') }}
                                    </button>
                                </header>

                                <!-- Pencarian nama dokter / klinik / spesialisasi -->
                                <div class="doctor-field">
                                    <label for="searchKeyword" class="doctor-label">{{ __('Nama dokter atau klinik') }}</label>
                                    <div class="doctor-search-field">
                                        <i class="fas fa-search doctor-search-icon" aria-hidden="true"></i>
                                        <input type="search" id="searchKeyword" class="doctor-search-input"
                                               placeholder="{{ __('Contoh: dr. Budi, Poli Gigi') }}"
                                               autocomplete="off" enterkeyhint="search">
                                        <button type="button" id="btnClearSearch" class="doctor-search-clear" hidden aria-label="{{ __('Hapus pencarian') }}">
                                            <i class="fas fa-circle-xmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Chips filter aktif -->
                                <ul class="doctor-chips" data-chips aria-label="{{ __('Filter aktif') }}" hidden></ul>

                                <!-- Spesialisasi -->
                                <fieldset class="doctor-specialty">
                                    <legend class="doctor-label doctor-legend">
                                        <span>{{ __('Spesialisasi') }}</span>
                                        <span id="specialtyCountBadge" class="doctor-badge" hidden>0</span>
                                    </legend>

                                    <label for="specialtySearchInput" class="visually-hidden">{{ __('Cari spesialisasi') }}</label>
                                    <div class="doctor-search-field doctor-search-field--sm">
                                        <i class="fas fa-search doctor-search-icon" aria-hidden="true"></i>
                                        <input type="search" id="specialtySearchInput" class="doctor-search-input"
                                               placeholder="{{ __('Cari spesialisasi...') }}" autocomplete="off">
                                    </div>

                                    <div class="doctor-specialty-actions">
                                        <button type="button" id="btnSelectAllSpecs" class="doctor-link-btn">{{ __('Pilih semua yang tampil') }}</button>
                                        <button type="button" id="btnClearSpecs" class="doctor-link-btn doctor-link-btn--muted">{{ __('Hapus pilihan') }}</button>
                                    </div>

                                    <div id="specialtyCheckboxes" class="doctor-specialty-list">
                                        @foreach ($spesialisasi as $spesialis)
                                        <div class="specialty-option" data-code="{{ $spesialis->Code }}">
                                            <input class="specialty-checkbox"
                                                   type="checkbox"
                                                   value="{{ $spesialis->Code }}"
                                                   id="spec_{{ $spesialis->Code }}"
                                                   data-name="{{ ucwords(strtolower($spesialis->Name)) }}">
                                            <label class="specialty-label" for="spec_{{ $spesialis->Code }}">
                                                <span class="specialty-name">{{ ucwords(strtolower($spesialis->Name)) }}</span>
                                                <span class="specialty-count" data-count aria-hidden="true"></span>
                                            </label>
                                        </div>
                                        @endforeach
                                        <p class="doctor-specialty-empty" id="specialtyEmpty" hidden>{{ __('Spesialisasi tidak ditemukan.') }}</p>
                                    </div>
                                </fieldset>

                                <!-- Tombol utama: hanya di bottom sheet / drawer -->
                                <footer class="doctor-filter-foot">
                                    <button type="button" id="btnShowResults" class="btn btn-primary-custom doctor-show-btn"
                                            data-bs-dismiss="offcanvas" data-bs-target="#doctorFilterOffcanvas">
                                        {{ __('Tampilkan') }} <span id="resultCountBtn">0</span> {{ __('dokter') }}
                                    </button>
                                </footer>
                            </form>
                        </div>
                    </div>
                </aside>

                <!-- ========================================== -->
                <!-- DAFTAR DOKTER                              -->
                <!-- ========================================== -->
                <div class="doctor-content-col">
                    <!-- Toolbar filter (hanya di bawah 1200px) -->
                    <div class="doctor-toolbar d-xl-none">
                        <button class="doctor-toolbar-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#doctorFilterOffcanvas" aria-controls="doctorFilterOffcanvas">
                            <i class="fas fa-sliders-h" aria-hidden="true"></i>
                            <span>{{ __('Filter') }}</span>
                            <span id="mobileFilterCount" class="doctor-badge" hidden>0</span>
                        </button>
                        <span class="doctor-toolbar-hint">{{ __('Cari dokter, klinik, atau spesialisasi') }}</span>
                    </div>

                    <!-- Chips filter aktif (terlihat meski panel filter tertutup) -->
                    <ul class="doctor-chips doctor-chips--content" data-chips aria-label="{{ __('Filter aktif') }}" hidden></ul>

                    <!-- Ringkasan Hasil Pencarian -->
                    <div id="search-summary-container" class="doctor-summary d-none" role="status" aria-live="polite">
                        <h3 class="doctor-summary-text">
                            <span id="search-summary-label">{{ __('Hasil Pencarian:') }}</span>
                            <span class="doctor-summary-keyword" id="search-summary-keyword"></span>
                        </h3>
                        <span class="doctor-summary-badge">
                            {{ __('Ditemukan') }} <span id="search-summary-count">0</span> {{ __('dokter') }}
                        </span>
                    </div>

                    <!-- Wadah Kartu Dokter Dinamis -->
                    <div id="daftar-dokter">

                    </div>
                </div>
            </div>
        </section>
    </div>
    </main>

    @include('components.footer')


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
                                        <h5 class="mb-1 fw-bold text-white" id="namaDokter">
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
                                        {{ __('Tentang') }}
                                    </button>
                                </li>

                                <li class="nav-item flex-fill text-center">
                                    <button
                                        class="nav-link w-100"
                                        id="jadwal-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#jadwal-pane"
                                        type="button">
                                        {{ __('Jadwal') }}
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
                                                        {{ __('Tentang Dokter') }}
                                                    </div>
                                                    <p id="doctor-profile-status" class="text-secondary" role="status" aria-live="polite"></p>
                                                    <p id="doctor-profile-bio" class="text-secondary" style="white-space: pre-line;"></p>
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
                                                            <div class="info-label">{{ __('Lokasi') }}</div>
                                                            <div id="doctor-profile-location" style="white-space: pre-line;"></div>
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
                                                            <div class="info-label">{{ __('Pengalaman') }}</div>
                                                            <strong id="doctor-profile-experience" style="white-space: pre-line;"></strong>
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
                                                            <div class="info-label">{{ __('Pendidikan') }}</div>
                                                            <strong id="doctor-profile-education" style="white-space: pre-line;"></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- CTA -->
                                            <div class="info-button row">
                                                <div class="button-group col-lg-12">
                                                    <button type="button" class="cta-btn mt-4 button-janji">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                        {{ __('Buat Janji Dokter') }}
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
                                                        {{ __('Jadwal Praktik') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-schedule-content">
                                                <div id="modal-schedule-cards" class="row">

                                                </div>
                                            </div>

                                            <div class="info-button row">
                                                <div class="button-group col-lg-12">
                                                    <button type="button" class="cta-btn mt-4 button-janji">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                        {{ __('Buat Janji Dokter') }}
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

            <!-- ================================================= -->
            <!-- ---------Booking Confirmation Modal-------------- -->
            <!-- ================================================= -->
            <div class="modal fade" id="bookingConfirmModal" tabindex="-1" aria-labelledby="bookingModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <div class="booking-icon-wrapper rounded-circle p-2 bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-calendar-check fs-5"></i>
                                </div>
                                <h5 class="modal-title fw-bold" id="bookingModalTitle">{{ __('Konfirmasi Janji Dokter') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-3">
                            <p class="text-muted small mb-3">
                                {{ __('Anda akan diarahkan ke portal Registrasi Online RS St. Elisabeth dengan rincian jadwal berikut yang terisi otomatis:') }}
                            </p>

                            <div class="booking-summary-card p-3 rounded-3 bg-light border mb-3">
                                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                    <div id="bookingModalDoctorPhoto" class="rounded-circle overflow-hidden flex-shrink-0 border" style="width: 56px; height: 56px;">
                                        <img src="" alt="" class="w-100 h-100" style="object-fit: cover; object-position: top center;">
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark" id="bookingModalDoctorName">-</h6>
                                        <span class="badge bg-primary text-white" id="bookingModalDoctorSpec">-</span>
                                    </div>
                                </div>

                                <div class="row g-2 small">
                                    <div class="col-12">
                                        <span class="text-muted d-block">{{ __('Poliklinik') }}</span>
                                        <strong class="text-dark" id="bookingModalPoliName">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block">{{ __('Jam Praktik') }}</span>
                                        <strong class="text-dark" id="bookingModalTime">-</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted d-block">{{ __('Sesi') }}</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" id="bookingModalSession">-</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <span class="text-muted d-block">{{ __('Tanggal Periksa') }}</span>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <strong class="text-primary fs-6" id="bookingModalDateFormatted">-</strong>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fa-solid fa-circle-check me-1"></i>{{ __('Jadwal Terdekat') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 px-3 small d-flex align-items-start gap-2 mb-0">
                                <i class="fa-solid fa-circle-info mt-1 text-info flex-shrink-0"></i>
                                <span>{{ __('Di portal RegOnline, Anda cukup memverifikasi NIK / No. Rekam Medis untuk menyelesaikan pendaftaran.') }}</span>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">{{ __('Batal') }}</button>
                            <a href="#" id="bookingModalConfirmBtn" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>{{ __('Lanjut ke Pendaftaran') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

    @include('components.floating-buttons')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        'resources/js/dokter/script.js'
    ])
    @include('components.json-ld')
    </body>
</html>
