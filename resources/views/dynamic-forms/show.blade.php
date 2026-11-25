<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($noindex))
        <meta name="robots" content="noindex, nofollow">
    @endif
    @include('components.seo-meta', ['title' => ($form?->title ?? __('Formulir Tidak Tersedia')) . ' - RS St. Elisabeth Semarang'])
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

    @php
        $heroBreadcrumbs = $breadcrumbs ?? [
            ['label' => __('Beranda'), 'url' => url('/')],
            ['label' => __('Formulir'), 'url' => route('dynamic-forms.index')],
            ['label' => $form?->title ?? __('Formulir')],
        ];
    @endphp

    <main class="dynamic-form-page">
        @if($loadError)
            @include('components.hero-section', [
                'badgeIcon' => 'fa-solid fa-triangle-exclamation',
                'badgeText' => __('Layanan Sementara Terganggu'),
                'title' => __('Formulir Belum Dapat Dimuat'),
                'subtitle' => __('Terjadi gangguan sementara saat menghubungi server formulir kami. Silakan muat ulang halaman beberapa saat lagi.'),
                'breadcrumbs' => $heroBreadcrumbs,
            ])
        @else
            @php
                $safeFormDescription = \App\Support\RichTextSanitizer::sanitize($form->description);
            @endphp
            @include('components.hero-section', [
                'badgeIcon' => $form->mode === 'feedback' ? 'fa-solid fa-comment-medical' : 'fa-solid fa-clipboard-check',
                'badgeText' => $form->mode === 'feedback' ? __('Kritik & Saran') : __('Kuesioner & Survei'),
                'title' => $form->title,
                'subtitle' => null,
                'breadcrumbs' => $heroBreadcrumbs,
            ])
        @endif

        <section class="dynamic-form-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-9">
                        @if($loadError)
                            <div class="df-state-card" role="alert">
                                <div class="df-state-icon is-error" aria-hidden="true">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </div>
                                <h2 class="h4 fw-bold mb-2" style="color: var(--secondary-darker-color, #124f8c);">
                                    {{ __('Gagal Memuat Formulir') }}
                                </h2>
                                <p class="mb-4 mx-auto" style="max-width: 540px; color: #475569;">
                                    {{ __('Sistem tidak dapat mengambil skema formulir dari server saat ini. Mohon coba muat ulang halaman.') }}
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
                        @else
                            @if($form->locale !== app()->getLocale())
                                <div class="df-lang-notice" role="status">
                                    <i class="fa-solid fa-language fs-5 mt-1 flex-shrink-0" aria-hidden="true"></i>
                                    <div>
                                        @if(app()->getLocale() === 'en')
                                            {{ __('Language note: The questions in this form are presented in Indonesian as authored, while navigation controls and system messages follow your selected site language.') }}
                                        @else
                                            {{ __('Catatan bahasa: Pertanyaan pada formulir ini disajikan dalam Bahasa Inggris sesuai pengaturan penyusun, sedangkan tombol dan pesan sistem mengikuti bahasa situs.') }}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if(!$availability['is_accepting_responses'])
                                <div class="df-state-card" role="status">
                                    <div class="df-state-icon is-closed" aria-hidden="true">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                    <h2 class="h4 fw-bold mb-2" style="color: var(--secondary-darker-color, #124f8c);">
                                        {{ __('Pengisian Formulir Ditutup') }}
                                    </h2>
                                    <p class="mb-3 mx-auto" style="max-width: 560px; color: #475569;">
                                        {{ $availability['closed_message'] ?: __('Formulir ini sedang tidak menerima pengisian.') }}
                                    </p>

                                    @if(($availability['closed_reason'] ?? null) === 'not_started' && $form->starts_at)
                                        <p class="small fw-semibold mb-4" style="color: #124f8c;">
                                            {{ __('Dibuka mulai:') }} {{ $form->starts_at->timezone(config('app.timezone', 'Asia/Jakarta'))->translatedFormat('d M Y, H:i') }} WIB
                                        </p>
                                    @endif

                                    <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                                        <a href="{{ route('dynamic-forms.index') }}" class="df-btn-secondary">
                                            {{ __('Lihat Daftar Formulir') }}
                                        </a>
                                        <a href="{{ url('/') }}" class="df-btn-primary">
                                            {{ __('Kembali ke Beranda') }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="df-sheet-card">
                                    @if(!empty($safeFormDescription))
                                        <div class="df-form-intro df-rich-text">
                                            {!! $safeFormDescription !!}
                                        </div>
                                    @endif

                                    <noscript>
                                        <div class="df-alert-banner is-error" role="alert">
                                            <strong>{{ __('JavaScript Diperlukan') }}</strong>
                                            <span>{{ __('Mohon aktifkan JavaScript pada browser Anda untuk mengisi dan mengirim formulir ini.') }}</span>
                                        </div>
                                    </noscript>

                                    <div id="dynamicFormApp" aria-live="polite">
                                        <div class="df-state-card" role="status">
                                            <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                                            <p class="mb-0 fw-semibold" style="color: #475569;">
                                                {{ __('Memuat pertanyaan formulir...') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <script type="application/json" id="dynamicFormConfig">
                                    {!! json_encode($rendererConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}
                                </script>
                            @endif
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
    @vite(array_values(array_filter([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        (!$loadError && ($availability['is_accepting_responses'] ?? false)) ? 'resources/js/dynamic-form-renderer.js' : null,
    ])))
    @include('components.json-ld')
</body>
</html>
