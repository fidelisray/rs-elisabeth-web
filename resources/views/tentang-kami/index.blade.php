<!doctype html>
<html lang="id">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      @include('components.seo-meta', ['title' => 'Tentang Kami | St. Elisabeth Hospital'])
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
      @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/hero.css',
        'resources/css/news.css',
        'resources/css/stats-section.css',
        'resources/css/app-section.css',
        'resources/css/tentang-kami.css',
        'resources/css/top-bar.css'
      ])
  </head>
  <body>

    @include('components.navbar')

<main style="background: radial-gradient(circle 360px at 90% 15%, rgba(183, 228, 229, 0.35), rgba(183, 228, 229, 0) 70%), linear-gradient(180deg, #ffffff 0%, #f1f8fd 100%);">
        <!-- Hero Section -->
        <section id="hero-section">
            <div class="container">
                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tentang Kami</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">Tentang Kami</h1>
                        <p class="hero-subtitle">Mengenal lebih dekat Rumah Sakit St. Elisabeth Semarang, sejarah, visi, dan misi kami dalam memberikan pelayanan kesehatan terbaik.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="motto" class="py-5">
            <div class="container">
                <!-- Motto -->
                <div class="row justify-content-center mb-5 pb-3">
                    <div class="col-md-10 text-center motto-section">
                        <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 fs-6">Motto</span>
                        <h2 class="display-6">"Pancaran Cintanya Menyembuhkan Derita Sesama"</h2>
                    </div>
                </div>
        </section>

        <section id="sejarah-singkat">
            <div class="container">
                <!-- Sejarah Singkat -->
                <div class="row justify-content-center mb-5 pb-4">
                    <div class="col-lg-10">
                        <div class="card history-card border-0 shadow-sm">
                            <div class="card-body p-4 p-md-5">
                                <div class="d-flex align-items-start mb-4">
                                    <div class="history-icon-wrapper me-4 flex-shrink-0">
                                        <i class="fa-solid fa-building-user"></i>
                                    </div>
                                    <div>
                                        <h3 class="fw-bold mb-1" style="color: var(--secondary-darker-color);">Sejarah Singkat</h3>
                                        <p class="text-muted mb-0 fw-medium">Berdiri sejak 18 Oktober 1927</p>
                                    </div>
                                </div>
                                <div class="history-content ps-md-5 ms-md-4">
                                    <p class="mb-3 fs-5 lh-base text-secondary">
                                        Rumah Sakit St. Elisabeth Semarang adalah rumah sakit swasta tipe B non Pendidikan yang didirikan oleh <strong>Kongregasi Suster Santo Fransiskus (OSF)</strong> yang terpanggil untuk mendirikan Rumah Sakit dikarenakan wabah kolera yang melanda masyarakat Semarang.
                                    </p>
                                    <p class="mb-0 fs-5 lh-base text-secondary">
                                        Rumah Sakit St. Elisabeth Semarang diresmikan pada tanggal <strong>18 Oktober 1927</strong> dengan kapasitas awal 50 tempat tidur, dan terus berkembang hingga saat ini menjadi salah satu rumah sakit swasta terkemuka di Jawa Tengah.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="visi-dan-misi">
            <div class="container">
                <!-- Visi -->
                <div class="row justify-content-center mb-5 pb-4">
                    <div class="col-lg-10 text-center">
                        <span class="badge bg-primary px-3 py-2 rounded-pill mb-4 fs-6">Visi</span>
                        <div class="card vision-card border-0 shadow-sm">
                            <div class="card-body p-4 p-md-5">
                                <div class="vision-icon mb-4">
                                    <i class="fa-solid fa-eye"></i>
                                </div>
                                <h3 class="vision-text fw-bold mb-0">
                                    Menjadi Rumah Sakit yang Mengutamakan Keselamatan, Mutu, dan Terpercaya serta sebagai Sarana Kehadiran Cinta dan Kuasa Allah.
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Misi -->
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-12 text-center">
                        <span class="badge bg-primary px-3 py-2 rounded-pill mb-4 fs-6">Misi</span>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card mission-card border-start-0 border-end-0 border-bottom-0 shadow-sm p-4 text-center">
                            <div class="mission-icon mb-3">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                            <p class="mb-0 fw-medium">Menyediakan layanan kesehatan yang <strong>bermutu dan profesional</strong> kepada masyarakat.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card mission-card border-start-0 border-end-0 border-bottom-0 shadow-sm p-4 text-center">
                            <div class="mission-icon mb-3">
                                <i class="fa-solid fa-user-nurse"></i>
                            </div>
                            <p class="mb-0 fw-medium">Memberi pelayanan yang berpusat pada pasien sebagai <strong>"Tamu Ilahi"</strong>.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card mission-card border-start-0 border-end-0 border-bottom-0 shadow-sm p-4 text-center">
                            <div class="mission-icon mb-3">
                                <i class="fa-solid fa-people-group"></i>
                            </div>
                            <p class="mb-0 fw-medium">Membangun <strong>persaudaraan sejati</strong> di antara pelayan kesehatan, pasien, dan masyarakat tanpa membedakan status sosial, golongan, dan agama.</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card mission-card border-start-0 border-end-0 border-bottom-0 shadow-sm p-4 text-center">
                            <div class="mission-icon mb-3">
                                <i class="fa-solid fa-leaf"></i>
                            </div>
                            <p class="mb-0 fw-medium">Melestarikan rumah sakit sebagai <strong>"Heritage & Green Hospital"</strong>.</p>
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
