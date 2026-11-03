<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Fasilitas & Layanan') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/facilities-and-services.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
</head>
<body>


    @include('components.navbar')

    <main class="facilities-page-canvas">
        <h1 class="visually-hidden">{{ __('Fasilitas dan Layanan RS Santa Elisabeth Semarang') }}</h1>

        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-hospital',
            'badgeText' => __('Fasilitas & Layanan Unggulan'),
            'title' => __('Fasilitas & Layanan Unggulan'),
            'subtitle' => __('Kami menyediakan fasilitas dan layanan berteknologi canggih demi memberikan pelayanan yang berkualitas dan paripurna kepada setiap pasien.'),
            'headingTag' => 'h2',
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Fasilitas & Layanan')],
            ],
        ])

        @php
            $categories = collect($facilities)
                ->pluck('category')
                ->filter(fn($cat) => is_string($cat) && trim($cat) !== '')
                ->map(fn($cat) => trim($cat))
                ->unique()
                ->values();
            $totalFacilities = count($facilities);
        @endphp

        {{-- ===== FASILITAS UTAMA: DISCOVERY BAR + MASTER-DETAIL WORKSPACE ===== --}}
        <section id="facilities-main" aria-label="{{ __('Eksplorasi Fasilitas dan Layanan') }}">
            <div class="container">

                @if($totalFacilities > 0)
                {{-- ---- Discovery Bar: Live Search, Category Filter & Counter ---- --}}
                <div class="facility-discovery-bar" id="facilities-discovery">
                    <div class="discovery-top-row">
                        <div class="facility-search-box">
                            <i class="fa-solid fa-magnifying-glass facility-search-icon" aria-hidden="true"></i>
                            <input
                                type="search"
                                id="facility-search-input"
                                class="facility-search-input"
                                placeholder="{{ __('Cari nama fasilitas atau layanan...') }}"
                                aria-label="{{ __('Cari nama fasilitas atau layanan...') }}"
                                autocomplete="off"
                            >
                            <button
                                type="button"
                                id="facility-search-clear"
                                class="facility-search-clear"
                                aria-label="{{ __('Reset Pencarian') }}"
                                hidden
                            >
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="facility-counter-pill" aria-live="polite">
                            <i class="fa-solid fa-hospital-user" aria-hidden="true"></i>
                            <span>{{ __('Menampilkan') }} <strong id="facility-visible-count">{{ $totalFacilities }}</strong> {{ __('Fasilitas') }}</span>
                        </div>
                    </div>

                    @if($categories->isNotEmpty())
                    <div class="facility-category-strip" role="group" aria-label="{{ __('Filter Kategori Fasilitas') }}">
                        <button type="button" class="facility-cat-pill active" data-category="all" aria-pressed="true">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                            <span>{{ __('Semua Kategori') }}</span>
                        </button>
                        @foreach($categories as $category)
                        <button type="button" class="facility-cat-pill" data-category="{{ \Illuminate\Support\Str::lower($category) }}" aria-pressed="false">
                            <span>{{ $category }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="row g-4 align-items-start" id="facilities-workspace">

                    {{-- ---- Kolom Kiri: Daftar Fasilitas (Mini Rich Cards) ---- --}}
                    <div class="col-12 col-lg-4">
                        <div class="facility-sidebar" id="facility-sidebar-nav">
                            <div class="facility-sidebar-header">
                                <div class="sidebar-header-title">
                                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                    <span>{{ __('Pilih Fasilitas') }}</span>
                                </div>
                                <span class="sidebar-count-badge" id="sidebar-count-badge">{{ $totalFacilities }}</span>
                            </div>

                            <div class="facility-list" role="tablist" aria-label="{{ __('Pilih Fasilitas') }}">
                                @foreach($facilities as $index => $facility)
                                @php
                                    $targetKey = 'facility-' . ($facility->slug ?? $facility->id);
                                    $thumbUrl = $facility->image_url ?? asset('images/placeholder.jpg');
                                    $catRaw = is_string($facility->category) ? trim($facility->category) : '';
                                    $searchableDesc = \Illuminate\Support\Str::lower(strip_tags($facility->short_description ?? $facility->description ?? ''));
                                @endphp
                                <button
                                    type="button"
                                    class="btn-facility {{ $index === 0 ? 'active' : '' }}"
                                    id="tab-{{ $targetKey }}"
                                    role="tab"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-controls="{{ $targetKey }}"
                                    tabindex="{{ $index === 0 ? '0' : '-1' }}"
                                    data-target="{{ $targetKey }}"
                                    data-name="{{ \Illuminate\Support\Str::lower($facility->name) }}"
                                    data-category="{{ \Illuminate\Support\Str::lower($catRaw) }}"
                                    data-keywords="{{ $searchableDesc }}"
                                >
                                    <span class="facility-btn-thumb">
                                        <img src="{{ $thumbUrl }}" alt="" loading="lazy">
                                    </span>
                                    <span class="facility-btn-info">
                                        @if($catRaw !== '')
                                        <span class="facility-btn-cat">{{ $catRaw }}</span>
                                        @endif
                                        <span class="facility-btn-title">{{ $facility->name }}</span>
                                    </span>
                                    <span class="facility-btn-arrow" aria-hidden="true">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- ---- Kolom Kanan: Detail Panel (Refined Classic Stacked Card) ---- --}}
                    <div class="col-12 col-lg-8">
                        <div class="facility-detail-panel" id="facility-detail-stage">

                            @foreach ($facilities as $index => $facility)
                            @php
                                $targetKey = 'facility-' . ($facility->slug ?? $facility->id);
                            @endphp
                            <article
                                class="facility-detail-item {{ $index === 0 ? 'active' : '' }}"
                                id="{{ $targetKey }}"
                                role="tabpanel"
                                aria-labelledby="tab-{{ $targetKey }}"
                                @if($index !== 0) hidden @endif
                            >
                                <div class="facility-img-wrapper">
                                    <img
                                        src="{{ $facility->image_url ?? asset('images/placeholder.jpg') }}"
                                        alt="{{ $facility->name }} RS St. Elisabeth Semarang"
                                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                    >
                                </div>
                                <div class="facility-content-body">
                                    @if(!empty($facility->category))
                                    <span class="facility-tag">{{ $facility->category }}</span>
                                    @endif

                                    <h3 class="facility-name">{{ $facility->name }}</h3>
                                    <hr class="facility-divider">

                                    <div class="facility-desc-wrapper facility-desc">
                                        {!! $facility->description !!}
                                    </div>

                                    @if(!empty($facility->highlights) && is_array($facility->highlights))
                                    <div class="facility-highlights">
                                        @foreach($facility->highlights as $highlight)
                                        @php
                                            $highlightLabel = is_array($highlight) ? ($highlight['name'] ?? '') : $highlight;
                                        @endphp
                                        @if(!empty($highlightLabel))
                                        <span class="facility-highlight-badge">
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            <span>{{ $highlightLabel }}</span>
                                        </span>
                                        @endif
                                        @endforeach
                                    </div>
                                    @endif

                                    @if(!empty($facility->wa_action_url) || $facility->has_appointment_cta)
                                    <div class="facility-cta">
                                        @if(!empty($facility->wa_action_url))
                                        <a href="{{ $facility->wa_action_url }}" target="_blank" rel="noopener" class="btn-primary-facility">
                                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                            <span>{{ $facility->wa_link_text ?: __('Hubungi Kami') }}</span>
                                        </a>
                                        @endif

                                        @if($facility->has_appointment_cta)
                                        <a href="https://regonline.rs-elisabeth.com" target="_blank" rel="noopener" class="btn-outline-facility">
                                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                            <span>{{ __('Buat Janji') }}</span>
                                        </a>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </article>
                            @endforeach

                            {{-- Empty Filter State (ketika pencarian/kategori tidak menemukan hasil) --}}
                            <div class="facility-empty-state" id="facility-filter-empty" hidden>
                                <div class="facility-empty-icon">
                                    <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
                                </div>
                                <h3 class="facility-empty-title">{{ __('Tidak Ada Fasilitas yang Cocok') }}</h3>
                                <p class="facility-empty-desc">
                                    {{ __('Tidak ada fasilitas atau layanan yang sesuai dengan kata kunci atau kategori yang Anda pilih.') }}
                                </p>
                                <button type="button" class="btn-reset-facility" id="btn-reset-facility-filter">
                                    <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                    <span>{{ __('Tampilkan Semua Fasilitas') }}</span>
                                </button>
                            </div>

                        </div>{{-- END .facility-detail-panel --}}
                    </div>{{-- END kolom kanan --}}

                </div>{{-- END .row --}}
                @else
                {{-- Empty Data State ketika CMS belum mengembalikan fasilitas --}}
                <div class="facility-empty-state facility-empty-cms">
                    <div class="facility-empty-icon">
                        <i class="fa-solid fa-hospital" aria-hidden="true"></i>
                    </div>
                    <h3 class="facility-empty-title">{{ __('Belum ada fasilitas yang ditambahkan.') }}</h3>
                    <p class="facility-empty-desc">
                        {{ __('Informasi fasilitas dan layanan saat ini sedang diperbarui. Silakan hubungi tim layanan pelanggan kami untuk informasi lebih lanjut.') }}
                    </p>
                    <a href="https://wa.me/6285600600870?text={{ urlencode(__('Halo, saya ingin melakukan konsultasi')) }}" target="_blank" rel="noopener" class="btn-reset-facility">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <span>{{ __('Hubungi via WhatsApp') }}</span>
                    </a>
                </div>
                @endif

            </div>{{-- END .container --}}
        </section>

    </main>

    {{-- ===== FOOTER ===== --}}
    @include('components.footer')

    @include('components.floating-buttons')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        'resources/js/facilities-and-services/facilities.js'
    ])
    @include('components.json-ld')
    </body>
</html>
