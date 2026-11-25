<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Formulir & Survei Pasien') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/dynamic-form.css',
    ])
</head>
<body>

    @include('components.navbar')

    <main class="dynamic-form-page">
        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-clipboard-list',
            'badgeText' => __('Layanan Aspirasi & Survei'),
            'title' => __('Formulir & Survei Pasien'),
            'subtitle' => __('Daftar formulir publik, survei layanan, serta saluran kritik dan saran resmi RS St. Elisabeth Semarang yang sedang dibuka.'),
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Formulir')],
            ],
        ])

        <section class="dynamic-form-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-11">
                        <div class="df-catalog-toolbar">
                            <nav class="df-filter-group" aria-label="{{ __('Filter Jenis Formulir') }}">
                                <a
                                    href="{{ route('dynamic-forms.index') }}"
                                    class="df-filter-link {{ $activeMode === 'all' ? 'is-active' : '' }}"
                                    @if($activeMode === 'all') aria-current="page" @endif
                                >
                                    {{ __('Semua Formulir') }}
                                </a>
                                <a
                                    href="{{ route('dynamic-forms.index', ['mode' => 'questionnaire']) }}"
                                    class="df-filter-link {{ $activeMode === 'questionnaire' ? 'is-active' : '' }}"
                                    @if($activeMode === 'questionnaire') aria-current="page" @endif
                                >
                                    {{ __('Kuesioner & Survei') }}
                                </a>
                                <a
                                    href="{{ route('dynamic-forms.index', ['mode' => 'feedback']) }}"
                                    class="df-filter-link {{ $activeMode === 'feedback' ? 'is-active' : '' }}"
                                    @if($activeMode === 'feedback') aria-current="page" @endif
                                >
                                    {{ __('Kritik & Saran') }}
                                </a>
                            </nav>

                            @if(!$loadError)
                                <p class="df-catalog-count">
                                    {{ __('Menampilkan') }} {{ count($forms) }} {{ __('formulir aktif') }}
                                </p>
                            @endif
                        </div>

                        @if($loadError)
                            <div class="df-state-card" role="alert">
                                <div class="df-state-icon is-error" aria-hidden="true">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </div>
                                <h2 class="h4 fw-bold mb-2" style="color: var(--secondary-darker-color, #124f8c);">
                                    {{ __('Gagal Memuat Daftar Formulir') }}
                                </h2>
                                <p class="mb-4 mx-auto" style="max-width: 520px; color: #475569;">
                                    {{ __('Layanan formulir sedang mengalami gangguan koneksi. Silakan muat ulang halaman beberapa saat lagi.') }}
                                </p>
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <a href="{{ url()->full() }}" class="df-btn-primary">
                                        {{ __('Muat Ulang Halaman') }}
                                    </a>
                                    <a href="{{ url('/') }}" class="df-btn-secondary">
                                        {{ __('Kembali ke Beranda') }}
                                    </a>
                                </div>
                            </div>
                        @elseif(count($forms) === 0)
                            <div class="df-state-card" role="status">
                                <div class="df-state-icon is-empty" aria-hidden="true">
                                    <i class="fa-regular fa-folder-open"></i>
                                </div>
                                <h2 class="h4 fw-bold mb-2" style="color: var(--secondary-darker-color, #124f8c);">
                                    {{ __('Belum Ada Formulir Publik Terbuka') }}
                                </h2>
                                <p class="mb-4 mx-auto" style="max-width: 520px; color: #475569;">
                                    @if($activeMode !== 'all')
                                        {{ __('Tidak ada formulir aktif untuk kategori yang Anda pilih saat ini.') }}
                                    @else
                                        {{ __('Saat ini belum ada formulir publik yang sedang dibuka. Anda tetap dapat menyampaikan masukan melalui halaman Kritik & Saran.') }}
                                    @endif
                                </p>
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    @if($activeMode !== 'all')
                                        <a href="{{ route('dynamic-forms.index') }}" class="df-btn-primary">
                                            {{ __('Tampilkan Semua Formulir') }}
                                        </a>
                                    @else
                                        <a href="{{ route('feedback.index') }}" class="df-btn-primary">
                                            {{ __('Buka Kritik & Saran') }}
                                        </a>
                                    @endif
                                    <a href="{{ url('/') }}" class="df-btn-secondary">
                                        {{ __('Kembali ke Beranda') }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="row g-4">
                                @foreach($forms as $formItem)
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <article class="df-catalog-card">
                                            <div class="df-catalog-card-top">
                                                <span class="df-mode-label {{ $formItem->mode === 'feedback' ? 'is-feedback' : 'is-questionnaire' }}">
                                                    @if($formItem->mode === 'feedback')
                                                        <i class="fa-solid fa-comment-medical" aria-hidden="true"></i>
                                                        {{ __('Kritik & Saran') }}
                                                    @else
                                                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                                                        {{ __('Kuesioner & Survei') }}
                                                    @endif
                                                </span>
                                                <span class="df-lang-tag" aria-label="{{ __('Bahasa formulir') }}">
                                                    {{ strtoupper($formItem->locale) }}
                                                </span>
                                            </div>

                                            <div class="df-catalog-card-body">
                                                <h2 class="df-catalog-card-title">{{ $formItem->title }}</h2>

                                                @php
                                                    $plainDesc = $formItem->plainDescription(135);
                                                @endphp
                                                @if($plainDesc !== null)
                                                    <p class="df-catalog-card-desc">
                                                        {{ $plainDesc }}
                                                    </p>
                                                @else
                                                    <p class="df-catalog-card-desc">
                                                        {{ __('Silakan buka formulir ini untuk mengisi jawaban Anda secara online.') }}
                                                    </p>
                                                @endif

                                                @if($formItem->ends_at || $formItem->remaining_quota !== null)
                                                    <div class="df-catalog-meta">
                                                        @if($formItem->ends_at)
                                                            <span class="df-catalog-meta-item">
                                                                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                                                <span>{{ __('Batas:') }} {{ $formItem->ends_at->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y') }}</span>
                                                            </span>
                                                        @endif
                                                        @if($formItem->remaining_quota !== null)
                                                            <span class="df-catalog-meta-item">
                                                                <i class="fa-solid fa-users" aria-hidden="true"></i>
                                                                <span>{{ __('Sisa kuota:') }} {{ $formItem->remaining_quota }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                <a href="{{ route('dynamic-forms.show', ['slug' => $formItem->slug]) }}" class="df-btn-primary w-100 mt-auto">
                                                    <span>{{ __('Isi Formulir') }}</span>
                                                </a>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
    ])
    @include('components.json-ld')
</body>
</html>
