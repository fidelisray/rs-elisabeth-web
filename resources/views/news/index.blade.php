<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => __('ElisaNews - RS St. Elisabeth Semarang')])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
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
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Beranda') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Berita & Artikel') }}</li>
                    </ol>
                </nav>

                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">ElisaNews</h1>
                        <p class="hero-subtitle">{{ __('Ikuti perkembangan terbaru, inovasi medis, dan informasi kesehatan dari Rumah Sakit St. Elisabeth Semarang.') }}</p>
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
                                    <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">{{ $item->category ?? __('Berita Umum') }}</span>
                                </div>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}">
                                    <h3 class="news-title">{{ $item->title }}</h3>
                                </a>
                                <p class="news-excerpt">{{ Str::limit(strip_tags($item->excerpt ?? $item->content ?? ''), 80) }}</p>
                                <a href="{{{ route('news.show', ['slug' => $item->slug]) }}}" class="news-read-more">
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
            </div>
        </section>

        @include('components.footer')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
        @vite([
            'resources/js/navbar/navbar.js',
            'resources/js/navbar/navbar-dropdown.js'
        ])
        @include('components.json-ld')
    </body>
</html>
