<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => ($document->title ?: __('Perpustakaan Online')) . ' - RS St. Elisabeth Semarang'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        @vite([
            'resources/css/footer.css',
            'resources/js/components/back-to-top.js',
            'resources/css/style.css',
            'resources/css/btn-accent.css',
            'resources/css/hero.css',
            'resources/css/online-library.css',
            'resources/css/navbar-dropdown.css',
            'resources/css/top-bar.css',
            'resources/css/search-and-quick-access.css',
        ])
    </head>
    <body>

        @include('components.navbar')

        <main>
            @include('components.hero-section', [
                'badgeIcon' => 'fa-solid fa-file-pdf',
                'badgeText' => $document->category ?: __('Dokumen Perpustakaan'),
                'title' => $document->title,
                'breadcrumbs' => [
                    ['label' => __('Beranda'), 'url' => url('/')],
                    ['label' => __('Perpustakaan Online'), 'url' => route('online-library.index')],
                    ['label' => Str::limit($document->title, 35)],
                ],
            ])

            @php
                $formattedSize = $document->formattedFileSize();
                $fileName = $document->file_name ?: ($document->slug . '.pdf');
                $hasDocumentFile = !empty($document->file_path) || !empty($document->file_url);
                $inlineFileUrl = route('online-library.file', ['slug' => $document->slug]);
                $downloadFileUrl = route('online-library.file', ['slug' => $document->slug, 'download' => 1]);
            @endphp

            <section class="library-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">

                            <!-- Kartu Metadata & Deskripsi Dokumen -->
                            <div class="library-detail-card">
                                <div class="library-meta-bar">
                                    <span class="library-meta-item">
                                        <i class="fa-regular fa-calendar text-primary"></i>
                                        {{ $document->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="library-meta-item">
                                        <i class="fa-regular fa-folder-open text-primary"></i>
                                        {{ $document->category ?: __('Dokumen Umum') }}
                                    </span>
                                    @if($formattedSize)
                                        <span class="library-meta-item">
                                            <i class="fa-solid fa-file-pdf text-danger"></i>
                                            PDF • {{ $formattedSize }}
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($document->description))
                                    <div class="library-description-content mb-4">
                                        {!! $document->description !!}
                                    </div>
                                @endif

                                <!-- Kartu Informasi File & Tombol Aksi -->
                                <div class="library-file-banner mb-0">
                                    <div class="library-file-info">
                                        <div class="library-pdf-emblem">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div>
                                            <div class="library-file-name">{{ $fileName }}</div>
                                            <p class="library-file-sub">
                                                {{ __('Format PDF') }}
                                                @if($formattedSize)
                                                    • {{ __('Ukuran Berkas:') }} <strong>{{ $formattedSize }}</strong>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    @if($hasDocumentFile)
                                        <div class="library-file-actions">
                                            <a
                                                href="{{ $inlineFileUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-outline-primary"
                                            >
                                                <i class="fa-solid fa-up-right-from-square"></i>
                                                <span>{{ __('Buka Layar Penuh') }}</span>
                                            </a>
                                            <a
                                                href="{{ $downloadFileUrl }}"
                                                class="btn btn-primary"
                                                style="background-color: var(--secondary-color, #026199); border-color: var(--secondary-color, #026199);"
                                            >
                                                <i class="fa-solid fa-download"></i>
                                                <span>{{ __('Unduh Dokumen PDF') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Penampil Isi Dokumen PDF (Embedded PDF Viewer via Same-Origin Stream) -->
                            @if($hasDocumentFile)
                                <div class="library-viewer-wrapper">
                                    <div class="library-viewer-header">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <i class="fa-solid fa-book-open text-info"></i>
                                            <span class="fw-semibold text-truncate">{{ __('Pratinjau Isi Dokumen') }}: {{ $document->title }}</span>
                                        </div>
                                        <a
                                            href="{{ $inlineFileUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-sm btn-outline-light rounded-pill px-3 flex-shrink-0"
                                        >
                                            <i class="fa-solid fa-expand me-1"></i> {{ __('Tab Baru') }}
                                        </a>
                                    </div>

                                    <!-- Panduan khusus pengguna Smartphone apabila browser mobile tidak merender PDF inline -->
                                    <div class="d-md-none bg-light border-bottom p-3 text-center">
                                        <p class="small text-secondary mb-2">
                                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                            {{ __('Jika pratinjau dokumen tidak tampil penuh pada layar ponsel Anda, silakan buka atau unduh PDF secara langsung.') }}
                                        </p>
                                        <a href="{{ $inlineFileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                                            <i class="fa-solid fa-file-pdf me-1"></i> {{ __('Buka PDF di Perangkat Anda') }}
                                        </a>
                                    </div>

                                    <iframe
                                        src="{{ $inlineFileUrl }}#toolbar=1&navpanes=0&scrollbar=1&view=FitH"
                                        class="library-pdf-frame"
                                        title="{{ $document->title }}"
                                        loading="lazy"
                                    ></iframe>
                                </div>
                            @else
                                <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                                    <div class="mb-3">
                                        <i class="fa-solid fa-file-circle-exclamation" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Berkas PDF Belum Tersedia') }}</h5>
                                    <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">{{ __('Berkas dokumen untuk halaman ini sedang diperbarui. Silakan coba kembali beberapa saat lagi.') }}</p>
                                </div>
                            @endif

                            <!-- Tombol Kembali -->
                            <div class="mt-5 border-top pt-4 text-center">
                                <a href="{{ route('online-library.index') }}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-arrow-left me-2"></i> {{ __('Kembali ke Perpustakaan Online') }}
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </main>

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
