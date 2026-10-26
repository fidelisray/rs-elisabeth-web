<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    @include('components.seo-meta', ['title' => __('Kuesioner Kepuasan Pasien') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/questionnaire.css'
    ])
</head>
<body>

    @include('components.navbar')

    <main class="questionnaire-page-wrapper">

        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-clipboard-check',
            'badgeText' => __('Evaluasi Mutu Pelayanan'),
            'title' => __('Kuesioner Kepuasan Pasien'),
            'subtitle' => __('Pelanggan yang terhormat, demi perbaikan mutu pelayanan kami, mohon Bapak/Ibu/Sdr dapat meluangkan waktu untuk mengisi kuesioner kepuasan pasien berikut ini. Terima kasih atas partisipasinya.'),
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Kuesioner Kepuasan Pasien')],
            ],
        ])

        <!-- 2. Portal Hub Cards Section -->
        <section class="questionnaire-container">
            <div class="container">
                <div class="row justify-content-center g-4">
                    @foreach($units as $unit)
                    <div class="col-md-6 col-lg-4">
                        <div class="hub-service-card">
                            <div>
                                <div class="hub-card-icon-wrap" style="background-color: {{ $unit['color'] }}15; color: {{ $unit['color'] }};">
                                    <i class="{{ $unit['icon'] }}"></i>
                                </div>
                                <h3 class="fs-5 fw-bold text-dark mb-2">{{ __($unit['title']) }}</h3>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">
                                    {{ __($unit['description']) }}
                                </p>
                            </div>

                            <div>
                                <div class="hub-card-meta">
                                    <span><i class="fa-regular fa-clock me-1 text-primary"></i> {{ __($unit['time_estimate']) }}</span>
                                    <span>&bull;</span>
                                    <span><i class="fa-regular fa-circle-question me-1 text-primary"></i> {{ $unit['questions_count'] }} {{ __('Pertanyaan') }}</span>
                                </div>

                                <a href="{{ $unit['route'] }}" class="btn btn-outline-primary rounded-pill w-100 fw-semibold mt-3 py-2 d-flex align-items-center justify-content-center gap-2">
                                    <span>{{ __('Mulai Kuesioner') }}</span>
                                    <i class="fa-solid fa-arrow-right fs-6"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="text-center mt-5">
                    <p class="text-muted small mb-0">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i>
                        {{ __('Seluruh data penilaian dan identitas kuesioner Anda dijamin kerahasiaannya oleh Manajemen RS St. Elisabeth Semarang.') }}
                    </p>
                </div>
            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9HjB+Zp6Gvpepeco+dpeP4bF+rPz5mJ5+F5u5b5Fk5b5Fk5b5" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>
