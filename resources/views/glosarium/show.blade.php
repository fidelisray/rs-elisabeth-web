<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => ($item['istilah'] ?? __('Kamus Medis')) . ' - ' . __('Kamus Medis')])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        @vite([
            'resources/css/footer.css',
            'resources/js/components/back-to-top.js',
            'resources/css/style.css',
            'resources/css/btn-accent.css',
            'resources/css/hero.css',
            'resources/css/glossarium.css',
            'resources/css/navbar-dropdown.css',
            'resources/css/top-bar.css',
            'resources/css/search-and-quick-access.css',
        ])
    </head>
    <body>

        @include('components.navbar')

        @php
            $letter = $activeLetter ?? strtoupper(substr($item['istilah'] ?? 'A', 0, 1));
            $prefix = $activePrefix ?? ucfirst(strtolower(substr($item['istilah'] ?? 'Aa', 0, 2)));
        @endphp

        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-file-medical',
            'badgeText' => __('Definisi Istilah Medis'),
            'title' => $item['istilah'] ?? '',
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Kamus Medis'), 'url' => route('glossary.index')],
                ['label' => __('Awalan') . " '{$letter}'", 'url' => route('glossary.index', ['letter' => $letter])],
                ['label' => $item['istilah'] ?? ''],
            ],
        ])

        <section class="glosarium-detail py-4 py-lg-5">
            <div class="container">
                <div class="glossary-detail-shell">

                    <!-- 1. Main Centered Editorial Article Card -->
                    <article class="glossary-article-card gsap-detail-reveal">
                        <div class="glossary-article-topbar">
                            <div class="article-category-pill">
                                <i class="fa-solid fa-book-medical"></i>
                                <span>{{ __('Ensiklopedia Medis') }}</span>
                                <span class="pill-divider">/</span>
                                <strong>{{ __('Sub-Awalan') }} "{{ $prefix }}"</strong>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('glossary.index', ['letter' => $letter]) }}" class="article-back-link">
                                    <i class="fa-solid fa-arrow-left"></i>
                                    <span>{{ __('Daftar Huruf') }} {{ $letter }}</span>
                                </a>
                                <a href="{{ route('glossary.index') }}" class="article-home-link" title="{{ __('Menu Utama Kamus Medis') }}">
                                    <i class="fa-solid fa-compass"></i>
                                    <span class="d-none d-sm-inline">{{ __('Semua Abjad') }}</span>
                                </a>
                            </div>
                        </div>

                        <header class="glossary-article-header">
                            <div class="article-letter-avatar" aria-hidden="true">
                                {{ $letter }}
                            </div>
                            <div class="article-heading-wrap">
                                <h1 class="glossary-article-title">
                                    {{ $item['istilah'] ?? __('Istilah Tidak Ditemukan') }}
                                </h1>
                                <p class="glossary-article-subtitle mb-0">
                                    {{ __('Ringkasan definisi klinis dan penjelasan edukasi kesehatan RS St. Elisabeth Semarang') }}
                                </p>
                            </div>
                        </header>

                        <!-- Lead Definition Callout Box -->
                        <div class="glossary-lead-box gsap-detail-paragraph">
                            <div class="lead-box-label">
                                <i class="fa-solid fa-stethoscope"></i>
                                <span>{{ __('Intisari Definisi Medis') }}</span>
                            </div>
                            <p class="lead-box-text mb-0">
                                {{ !empty($leadParagraph) ? $leadParagraph : ($item['deskripsi'] ?? __('Deskripsi tidak tersedia untuk istilah ini.')) }}
                            </p>
                        </div>

                        <!-- Structured Body Paragraphs (Optimal 68ch Reading Measure) -->
                        @if(!empty($bodyParagraphs))
                            <div class="glossary-article-body">
                                @foreach($bodyParagraphs as $paragraph)
                                    <p class="glossary-body-paragraph gsap-detail-paragraph">
                                        {{ $paragraph }}
                                    </p>
                                @endforeach
                            </div>
                        @endif

                        <!-- Reference Source & Medical Disclaimer Footer -->
                        <footer class="glossary-article-footer gsap-detail-paragraph">
                            @if(!empty($item['source']))
                                @php
                                    $sourceHost = parse_url($item['source'], PHP_URL_HOST) ?: $item['source'];
                                    $sourceHost = preg_replace('/^www\./i', '', $sourceHost);
                                @endphp
                                <div class="glossary-source-box">
                                    <div class="source-icon" aria-hidden="true">
                                        <i class="fa-solid fa-shield-heart"></i>
                                    </div>
                                    <div class="source-info">
                                        <span class="source-label">{{ __('Sumber Referensi Medis') }}</span>
                                        <a href="{{ $item['source'] }}" target="_blank" rel="noopener noreferrer" class="source-link">
                                            <span class="source-domain">{{ $sourceHost }}</span>
                                            <span class="source-url d-none d-md-inline">({{ \Illuminate\Support\Str::limit($item['source'], 58) }})</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <div class="glossary-disclaimer-box">
                                <i class="fa-solid fa-circle-info disclaimer-icon" aria-hidden="true"></i>
                                <p class="mb-0">
                                    {{ __('Penjelasan istilah medis ini disediakan sebagai sarana edukasi informasi kesehatan masyarakat dan bukan pengganti diagnosis, konsultasi, maupun tindakan medis dari dokter profesional.') }}
                                </p>
                            </div>
                        </footer>
                    </article>

                    <!-- 2. Sequential Prev / Next Term Bento Navigation -->
                    @if(!empty($prevItem) || !empty($nextItem))
                        <nav class="glossary-seq-grid" aria-label="{{ __('Navigasi istilah berurutan') }}">
                            @if(!empty($prevItem))
                                <a href="{{ route('glossary.show', urlencode($prevItem['slug'])) }}" class="glossary-seq-card seq-prev gsap-detail-reveal">
                                    <span class="seq-arrow" aria-hidden="true">
                                        <i class="fa-solid fa-arrow-left"></i>
                                    </span>
                                    <div class="seq-meta">
                                        <span class="seq-label">{{ __('Istilah Sebelumnya') }}</span>
                                        <strong class="seq-title">{{ $prevItem['istilah'] }}</strong>
                                    </div>
                                </a>
                            @else
                                <div class="glossary-seq-card seq-disabled d-none d-md-flex">
                                    <div class="seq-meta">
                                        <span class="seq-label">{{ __('Awal Direktori') }}</span>
                                        <strong class="seq-title text-muted">{{ __('Ini adalah istilah pertama') }}</strong>
                                    </div>
                                </div>
                            @endif

                            @if(!empty($nextItem))
                                <a href="{{ route('glossary.show', urlencode($nextItem['slug'])) }}" class="glossary-seq-card seq-next gsap-detail-reveal">
                                    <div class="seq-meta text-md-end">
                                        <span class="seq-label">{{ __('Istilah Selanjutnya') }}</span>
                                        <strong class="seq-title">{{ $nextItem['istilah'] }}</strong>
                                    </div>
                                    <span class="seq-arrow" aria-hidden="true">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </a>
                            @else
                                <div class="glossary-seq-card seq-disabled d-none d-md-flex justify-content-end">
                                    <div class="seq-meta text-end">
                                        <span class="seq-label">{{ __('Akhir Direktori') }}</span>
                                        <strong class="seq-title text-muted">{{ __('Ini adalah istilah terakhir') }}</strong>
                                    </div>
                                </div>
                            @endif
                        </nav>
                    @endif

                    <!-- 3. Related Medical Terms Bento Card -->
                    @if(!empty($relatedItems))
                        <section class="glossary-related-card gsap-detail-reveal" aria-labelledby="relatedTermsHeading">
                            <div class="glossary-group-header">
                                <div class="glossary-group-badge" aria-hidden="true">
                                    {{ $letter }}
                                </div>
                                <div class="glossary-group-info">
                                    <h2 id="relatedTermsHeading" class="glossary-group-title">
                                        {{ __('Istilah Medis Terkait Awalan') }} "{{ $letter }}"
                                    </h2>
                                    <span class="glossary-group-sub">
                                        {{ __('Pelajari istilah kesehatan lainnya yang berkaitan pada kelompok abjad yang sama') }}
                                    </span>
                                </div>
                                <a href="{{ route('glossary.index', ['letter' => $letter]) }}" class="glossary-view-all-link">
                                    <span>{{ __('Lihat Semua') }}</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="glossary-terms-grid">
                                @foreach($relatedItems as $rel)
                                    <a href="{{ route('glossary.show', urlencode($rel['slug'])) }}" class="glossary-term-card gsap-related-item">
                                        <span class="term-card-dot" aria-hidden="true"></span>
                                        <span class="term-card-name">{{ $rel['istilah'] }}</span>
                                        <span class="term-card-arrow" aria-hidden="true">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <!-- 4. Medical Consultation & Health Articles CTA Bento Grid -->
                    <div class="glossary-detail-cta-grid gsap-detail-reveal">
                        <a href="{{ route('dokter.index') }}" class="cta-card cta-primary text-decoration-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="cta-icon"><i class="fa-solid fa-user-doctor"></i></div>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">{{ __('Butuh Konsultasi Dokter?') }}</h3>
                                    <p class="mb-0 small text-white-50">{{ __('Diskusikan gejala atau keluhan medis Anda langsung bersama dokter spesialis kami.') }}</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </a>

                        <a href="{{ route('articles.index') }}" class="cta-card cta-outline text-decoration-none">
                            <div class="d-flex align-items-center gap-3">
                                <div class="cta-icon"><i class="fa-solid fa-newspaper"></i></div>
                                <div>
                                    <h3 class="h6 fw-bold mb-1">{{ __('Artikel Kesehatan Terkait') }}</h3>
                                    <p class="mb-0 small text-muted">{{ __('Temukan tips pencegahan dan panduan gaya hidup sehat dari tim medis kami.') }}</p>
                                </div>
                            </div>
                            <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </a>
                    </div>

                </div>
            </div>
        </section>

        @include('components.footer')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
        @vite([
            'resources/js/navbar/navbar.js',
            'resources/js/navbar/navbar-dropdown.js',
            'resources/js/glosarium/glosarium.js'
        ])
        @include('components.json-ld')
    </body>
</html>
