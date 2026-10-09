<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Informasi Pelanggan') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/customer-information.css'
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
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Informasi Pelanggan') }}</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">{{ __('Informasi Pelanggan') }}</h1>
                    </div>
                </div>
            </div>
        </section>

        <section id="customer-info" class="py-5" style="background: radial-gradient(circle 340px at 10% 15%, rgba(183, 228, 229, 0.45), rgba(183, 228, 229, 0) 70%), linear-gradient(180deg, #ffffff 0%, #eef7fd 55%, #f8fafc 100%);">
            <div class="container">
                <!-- JAM KUNJUNGAN PASIEN -->
                <div class="section-heading text-center mb-5">
                    <h2 class="d-inline-block position-relative pb-2 border-bottom border-primary border-2">{{ __('Jam Kunjungan Pasien') }}</h2>
                    <p class="mt-3 text-muted">{{ __('Untuk menjaga ketenangan dan kenyamanan pasien, kami mohon Anda dapat melakukan kunjungan sesuai dengan ketentuan berikut:') }}</p>
                </div>
                
                <div class="row g-4 justify-content-center mb-5">
                    <!-- Senin - Sabtu -->
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-white">
                            <div class="icon-circle mx-auto mb-3 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #008fd7;">
                                <i class="fa-solid fa-calendar-day fs-3"></i>
                            </div>
                            <h4 class="fw-bold mb-4">{{ __('Senin – Sabtu') }}</h4>
                            <div class="d-flex justify-content-between align-items-center px-4 mb-2">
                                <span class="text-muted"><i class="fa-regular fa-sun text-warning me-2"></i> {{ __('Pagi') }}</span>
                                <span class="fw-medium">09.30 – 11.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-4">
                                <span class="text-muted"><i class="fa-solid fa-cloud-sun text-info me-2"></i> {{ __('Sore') }}</span>
                                <span class="fw-medium">17.00 – 18.30</span>
                            </div>
                        </div>
                    </div>
                    <!-- Minggu & Hari Libur -->
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center bg-white">
                            <div class="icon-circle mx-auto mb-3 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #008fd7;">
                                <i class="fa-regular fa-calendar-check fs-3"></i>
                            </div>
                            <h4 class="fw-bold mb-4">{{ __('Minggu & Hari Libur') }}</h4>
                            <div class="d-flex justify-content-between align-items-center px-4 mb-2">
                                <span class="text-muted"><i class="fa-regular fa-sun text-warning me-2"></i> {{ __('Pagi') }}</span>
                                <span class="fw-medium">09.30 – 11.30</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-4">
                                <span class="text-muted"><i class="fa-solid fa-cloud-sun text-info me-2"></i> {{ __('Sore') }}</span>
                                <span class="fw-medium">16.30 – 18.30</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KETENTUAN PENGUNJUNG -->
                <div class="section-heading text-center mb-5 mt-5">
                    <h2 class="d-inline-block position-relative pb-2 border-bottom border-primary border-2">{{ __('Ketentuan Pengunjung') }}</h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-heart-pulse fs-5"></i>
                                </div>
                                <p class="mb-0 text-muted">{{ __('Demi Kesehatan Anda, kami menyarankan untuk') }} <strong>{{ __('tidak melakukan kunjungan') }}</strong> {{ __('terlebih dahulu apabila kondisi badan sedang tidak fit.') }}</p>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 p-2 mb-3 bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-volume-xmark fs-5"></i>
                                </div>
                                <p class="mb-0 text-muted">{{ __('Mohon untuk') }} <strong>{{ __('tidak berbicara keras') }}</strong> {{ __('dan berkunjung secara bergantian (') }}<strong>{{ __('maksimal 2 pengunjung') }}</strong> {{ __('untuk tiap pasien).') }}</p>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
                            <div class="card-body d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-4 flex-shrink-0" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-child-reaching fs-5"></i>
                                </div>
                                <p class="mb-0 text-muted">{{ __('Anak-anak') }} <strong>{{ __('di bawah usia 12 tahun') }}</strong> {{ __('tidak diizinkan berkunjung.') }}</p>
                            </div>
                        </div>
                        <div class="text-center mt-5 text-muted fst-italic">
                            <p>{{ __('Terima kasih atas kesediaan Anda dalam membantu kami menjaga ketenangan dan kenyamanan pasien selama di rawat di Rumah Sakit St. Elisabeth Semarang.') }}</p>
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
        'resources/js/navbar/navbar-dropdown.js'
    ])
    @include('components.json-ld')
    </body>
</html>
