<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => __('Perpustakaan Online - RS St. Elisabeth Semarang')])
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
                'badgeIcon' => 'fa-solid fa-book-open-reader',
                'badgeText' => __('Pusat Dokumen & Edukasi'),
                'title' => __('Perpustakaan Online'),
                'subtitle' => __('Akses dan unduh koleksi dokumen resmi, panduan kesehatan, modul edukasi pasien, serta publikasi dari Rumah Sakit St. Elisabeth Semarang.'),
                'breadcrumbs' => [
                    ['label' => __('Beranda'), 'url' => url('/')],
                    ['label' => __('Perpustakaan Online')],
                ],
            ])

            <section class="library-section">
                <div class="container">
                    @if(count($documentsList) > 0)
                        <!-- Toolbar Pencarian & Filter Kategori -->
                        <div class="library-toolbar">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-lg-5">
                                    <div class="library-search-wrapper">
                                        <i class="fa-solid fa-magnifying-glass library-search-icon"></i>
                                        <input
                                            type="search"
                                            id="librarySearchInput"
                                            class="form-control library-search-input"
                                            placeholder="{{ __('Cari judul atau deskripsi dokumen...') }}"
                                            aria-label="{{ __('Cari judul atau deskripsi dokumen...') }}"
                                        >
                                    </div>
                                </div>
                                <div class="col-12 col-lg-7">
                                    <div class="library-category-pills" role="group" aria-label="{{ __('Filter Kategori Dokumen') }}">
                                        <button type="button" class="library-category-btn active" data-category="all">
                                            <i class="fa-solid fa-layer-group"></i>
                                            <span>{{ __('Semua Kategori') }}</span>
                                        </button>
                                        @foreach($categories as $category)
                                            <button type="button" class="library-category-btn" data-category="{{ Str::lower($category) }}">
                                                <i class="fa-regular fa-folder"></i>
                                                <span>{{ $category }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-4" id="libraryGrid">
                        @forelse($documentsList as $item)
                            @php
                                $formattedSize = $item->formattedFileSize();
                                $categoryLabel = $item->category ?: __('Dokumen Umum');
                                $excerptText = Str::limit(strip_tags($item->description ?? ''), 120);
                            @endphp
                            <div
                                class="col-12 col-md-6 col-lg-4 library-item-col"
                                data-title="{{ Str::lower($item->title) }}"
                                data-desc="{{ Str::lower(strip_tags($item->description ?? '')) }}"
                                data-category="{{ Str::lower($item->category ?? '') }}"
                            >
                                <article class="library-card">
                                    <div class="library-card-visual">
                                        <a href="{{ route('online-library.show', ['slug' => $item->slug]) }}" aria-label="{{ $item->title }}">
                                            <div class="library-pdf-emblem">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                        </a>
                                        <div class="library-card-badges">
                                            <span class="library-category-badge">
                                                <i class="fa-regular fa-folder-open"></i>
                                                {{ $categoryLabel }}
                                            </span>
                                            @if($formattedSize)
                                                <span class="library-size-badge">
                                                    <i class="fa-solid fa-weight-hanging"></i>
                                                    {{ $formattedSize }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="library-card-body">
                                        <div class="library-date">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span>{{ $item->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</span>
                                        </div>

                                        <a href="{{ route('online-library.show', ['slug' => $item->slug]) }}">
                                            <h3 class="library-title">{{ $item->title }}</h3>
                                        </a>

                                        <p class="library-excerpt">
                                            {{ $excerptText !== '' ? $excerptText : __('Klik untuk membaca detail dan melihat isi dokumen PDF selengkapnya.') }}
                                        </p>

                                        <div class="library-card-actions">
                                            <a href="{{ route('online-library.show', ['slug' => $item->slug]) }}" class="btn-library-read">
                                                <i class="fa-solid fa-book-open"></i>
                                                <span>{{ __('Baca & Detail') }}</span>
                                            </a>
                                            @if($item->file_path || $item->file_url)
                                                <a
                                                    href="{{ route('online-library.file', ['slug' => $item->slug, 'download' => 1]) }}"
                                                    class="btn-library-download"
                                                    title="{{ __('Unduh PDF') }}"
                                                    aria-label="{{ __('Unduh PDF') }} - {{ $item->title }}"
                                                >
                                                    <i class="fa-solid fa-download"></i>
                                                    <span class="d-none d-sm-inline">{{ __('Unduh') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @empty
                            <div class="col-12 reveal-on-scroll">
                                <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                                    <div class="mb-3">
                                        <i class="fa-solid fa-book-open-reader" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                                    </div>
                                    <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Dokumen Perpustakaan Belum Tersedia') }}</h5>
                                    <p class="text-muted mb-0 mx-auto" style="max-width: 500px;">{{ __('Kami sedang menyiapkan koleksi dokumen dan panduan kesehatan terbaru untuk Anda. Silakan kunjungi kembali halaman ini nanti.') }}</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if(count($documentsList) > 0)
                        <!-- Empty State saat Pencarian/Filter Tidak Menemukan Hasil -->
                        <div id="libraryFilterEmptyState" class="d-none mt-2">
                            <div class="empty-state-card p-5 text-center rounded-4" style="background-color: rgba(2, 97, 153, 0.03); border: 2px dashed rgba(2, 97, 153, 0.2);">
                                <div class="mb-3">
                                    <i class="fa-solid fa-file-circle-question" style="font-size: 3rem; color: rgba(2, 97, 153, 0.4);"></i>
                                </div>
                                <h5 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">{{ __('Dokumen Tidak Ditemukan') }}</h5>
                                <p class="text-muted mb-3 mx-auto" style="max-width: 500px;">{{ __('Tidak ada dokumen yang sesuai dengan kata kunci atau kategori yang Anda pilih.') }}</p>
                                <button type="button" id="libraryResetFilterBtn" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                                    <i class="fa-solid fa-rotate-left me-1"></i> {{ __('Tampilkan Semua Dokumen') }}
                                </button>
                            </div>
                        </div>
                    @endif
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
        @if(count($documentsList) > 0)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const searchInput = document.getElementById('librarySearchInput');
                    const categoryButtons = document.querySelectorAll('.library-category-btn');
                    const items = document.querySelectorAll('.library-item-col');
                    const emptyFilterState = document.getElementById('libraryFilterEmptyState');
                    const resetBtn = document.getElementById('libraryResetFilterBtn');

                    let activeCategory = 'all';

                    function filterDocuments() {
                        const keyword = (searchInput ? searchInput.value : '').trim().toLowerCase();
                        let visibleCount = 0;

                        items.forEach(function (col) {
                            const title = col.getAttribute('data-title') || '';
                            const desc = col.getAttribute('data-desc') || '';
                            const cat = col.getAttribute('data-category') || '';

                            const matchesKeyword = keyword === '' || title.includes(keyword) || desc.includes(keyword);
                            const matchesCategory = activeCategory === 'all' || cat === activeCategory;

                            if (matchesKeyword && matchesCategory) {
                                col.classList.remove('d-none');
                                visibleCount++;
                            } else {
                                col.classList.add('d-none');
                            }
                        });

                        if (emptyFilterState) {
                            emptyFilterState.classList.toggle('d-none', visibleCount > 0);
                        }
                    }

                    if (searchInput) {
                        searchInput.addEventListener('input', filterDocuments);
                    }

                    categoryButtons.forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            categoryButtons.forEach(function (b) { b.classList.remove('active'); });
                            btn.classList.add('active');
                            activeCategory = btn.getAttribute('data-category') || 'all';
                            filterDocuments();
                        });
                    });

                    if (resetBtn) {
                        resetBtn.addEventListener('click', function () {
                            if (searchInput) {
                                searchInput.value = '';
                            }
                            activeCategory = 'all';
                            categoryButtons.forEach(function (b) {
                                b.classList.toggle('active', b.getAttribute('data-category') === 'all');
                            });
                            filterDocuments();
                        });
                    }
                });
            </script>
        @endif
        @include('components.json-ld')
    </body>
</html>
