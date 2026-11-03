<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Ruang Perawatan') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/style.css',
        'resources/css/footer.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/ruang-perawatan.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
</head>
<body>

    @include('components.navbar')

    <main class="ruang-perawatan-canvas">
        <h1 class="visually-hidden">{{ __('Ruang Perawatan RS Santa Elisabeth Semarang') }}</h1>

        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-bed-pulse',
            'badgeText' => __('Akomodasi & Rawat Inap'),
            'title' => __('Ruang Perawatan'),
            'subtitle' => __('Kenyamanan dan kesembuhan Anda adalah prioritas kami. Pilih ruang perawatan yang sesuai dengan kebutuhan, dari suite eksklusif hingga kelas yang terjangkau, semua dengan standar pelayanan paripurna.'),
            'headingTag' => 'h2',
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Ruang Perawatan')],
            ],
        ])

        @php
            $totalRooms = count($roomsData);
        @endphp

        <section id="inpatient-room-facilities" aria-label="{{ __('Pilihan Ruang Rawat Inap') }}">
            <div class="container">

                @if($totalRooms > 0)
                {{-- ===== DISCOVERY BAR (LIVE SEARCH + COUNTER) ===== --}}
                <div class="room-discovery-bar" id="room-discovery-bar">
                    <div class="room-search-box">
                        <i class="fa-solid fa-magnifying-glass room-search-icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            id="room-search-input"
                            class="room-search-input"
                            placeholder="{{ __('Cari nama ruang perawatan atau fasilitas kamar (misal: VIP, Smart TV, 1 Bed)...') }}"
                            aria-label="{{ __('Cari nama ruang perawatan atau fasilitas kamar (misal: VIP, Smart TV, 1 Bed)...') }}"
                            autocomplete="off"
                        >
                        <button
                            type="button"
                            id="room-search-clear"
                            class="room-search-clear"
                            aria-label="{{ __('Reset Pencarian') }}"
                            hidden
                        >
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="room-counter-pill" aria-live="polite">
                        <i class="fa-solid fa-bed" aria-hidden="true"></i>
                        <span>{{ __('Menampilkan') }} <strong id="room-visible-count">{{ $totalRooms }}</strong> {{ __('Ruang Perawatan') }}</span>
                    </div>
                </div>

                {{-- ===== ROOM CARDS GRID ===== --}}
                <div class="row g-4" id="room-cards-grid">
                    @foreach($roomsData as $room)
                        @php
                            $isPremium = strtolower((string) $room->category) === 'premium';
                            $badgeLabel = $isPremium ? __('Premium & Eksklusif') : __('Ruang Rawat Inap');
                            $badgeIcon = $isPremium ? 'fa-solid fa-crown' : 'fa-solid fa-bed';

                            $specsSearch = implode(' ', array_filter([
                                $room->room_size ? $room->room_size . ' m2' : '',
                                $room->bed_count ? $room->bed_count . ' bed' : '',
                                $room->max_companion ? $room->max_companion . ' penunggu' : '',
                                $badgeLabel,
                            ]));

                            $tagsCollector = [];
                            if (!empty($room->highlight_tags) && is_array($room->highlight_tags)) {
                                foreach ($room->highlight_tags as $tag) {
                                    if (!empty($tag['label'])) {
                                        $tagsCollector[] = $tag['label'];
                                    }
                                }
                            }
                            if (!empty($room->amenities) && is_array($room->amenities)) {
                                foreach ($room->amenities as $group) {
                                    foreach ($group['items'] ?? [] as $amenityItem) {
                                        $tagsCollector[] = is_array($amenityItem) ? ($amenityItem['name'] ?? '') : $amenityItem;
                                    }
                                }
                            }
                            $tagsSearch = \Illuminate\Support\Str::lower(implode(' ', array_filter($tagsCollector)));
                            $descSearch = \Illuminate\Support\Str::lower(strip_tags(($room->tagline ?? '') . ' ' . ($room->description ?? '')));
                        @endphp
                        <div
                            class="col-12 col-md-6 col-lg-4 room-card-col"
                            data-name="{{ \Illuminate\Support\Str::lower($room->name) }}"
                            data-specs="{{ \Illuminate\Support\Str::lower($specsSearch) }}"
                            data-desc="{{ $descSearch }}"
                            data-tags="{{ $tagsSearch }}"
                        >
                            <article class="inpatient-room-card {{ $isPremium ? 'is-premium' : 'is-standard' }}">
                                <div class="card-accent-bar"></div>
                                <div class="room-thumb-wrapper">
                                    <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="room-thumb-link" aria-label="{{ __('Lihat Detail') }} {{ $room->name }}">
                                        <img
                                            src="{{ $room->image_url ?? Vite::asset('resources/images/feature.jfif') }}"
                                            alt="{{ $room->name }} RS St. Elisabeth Semarang"
                                            loading="lazy"
                                        >
                                    </a>
                                    <span class="room-class-badge {{ $isPremium ? 'badge-tier-premium' : 'badge-tier-standard' }}">
                                        <i class="{{ $badgeIcon }}" aria-hidden="true"></i>
                                        <span>{{ $badgeLabel }}</span>
                                    </span>
                                </div>

                                <div class="room-card-body">
                                    <h3 class="room-name">
                                        <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="room-name-link">
                                            {{ $room->name }}
                                        </a>
                                    </h3>

                                    @if($room->room_size || $room->bed_count || $room->max_companion)
                                    <div class="room-specs">
                                        @if($room->room_size)
                                        <span class="spec-chip">
                                            <i class="fa-solid fa-vector-square" aria-hidden="true"></i>
                                            <span>{{ $room->room_size }} m²</span>
                                        </span>
                                        @endif
                                        @if($room->bed_count)
                                        <span class="spec-chip">
                                            <i class="fa-solid fa-bed" aria-hidden="true"></i>
                                            <span>{{ $room->bed_count }} Bed</span>
                                        </span>
                                        @endif
                                        @if($room->max_companion)
                                        <span class="spec-chip">
                                            <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                                            <span>{{ __('Maks.') }} {{ $room->max_companion }} {{ __('Penunggu') }}</span>
                                        </span>
                                        @endif
                                    </div>
                                    @endif

                                    @if($room->tagline || $room->description)
                                    <p class="room-description">
                                        {{ $room->tagline ?? \Illuminate\Support\Str::limit(strip_tags($room->description), 145) }}
                                    </p>
                                    @endif

                                    @if(!empty($room->highlight_tags) && is_array($room->highlight_tags))
                                    <div class="room-tags">
                                        @foreach(array_slice($room->highlight_tags, 0, 4) as $tag)
                                            @if(!empty($tag['label']))
                                            <span class="tag-pill">
                                                @if(!empty($tag['icon']))
                                                    <i class="{{ $tag['icon'] }}" aria-hidden="true"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                @endif
                                                <span>{{ $tag['label'] }}</span>
                                            </span>
                                            @endif
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="room-card-cta">
                                        <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="btn-detail">
                                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                            <span>{{ __('Lihat Detail') }}</span>
                                        </a>
                                        @php
                                            $waText = $room->whatsapp_text ?? __('Halo, saya ingin informasi ruangan :name', ['name' => $room->name]);
                                        @endphp
                                        <a
                                            href="https://wa.me/6285600600870?text={{ urlencode($waText) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="btn-wa-icon"
                                            title="{{ __('Tanya Ketersediaan') }} {{ $room->name }}"
                                            aria-label="{{ __('Tanya Ketersediaan') }} {{ $room->name }}"
                                        >
                                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                {{-- ===== EMPTY FILTER STATE ===== --}}
                <div class="empty-state-card" id="room-filter-empty" hidden>
                    <div class="empty-icon-wrapper">
                        <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
                    </div>
                    <h3 class="empty-title">{{ __('Tidak Ada Ruang Perawatan yang Cocok') }}</h3>
                    <p class="empty-desc">
                        {{ __('Tidak ditemukan ruang perawatan yang sesuai dengan kata kunci pencarian Anda. Silakan coba kata kunci lain atau tampilkan kembali seluruh kamar.') }}
                    </p>
                    <button type="button" class="btn-reset-room" id="btn-reset-room-search">
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        <span>{{ __('Tampilkan Semua Kamar') }}</span>
                    </button>
                </div>

                @else
                {{-- ===== EMPTY CMS STATE ===== --}}
                <div class="empty-state-card">
                    <div class="empty-icon-wrapper">
                        <i class="fa-solid fa-bed-pulse" aria-hidden="true"></i>
                    </div>
                    <h3 class="empty-title">{{ __('Data Ruangan Sedang Diperbarui') }}</h3>
                    <p class="empty-desc">
                        {{ __('Informasi pilihan ruang perawatan saat ini sedang disinkronisasi. Silakan hubungi layanan pelanggan kami untuk informasi ketersediaan kamar secara langsung.') }}
                    </p>
                    <a
                        href="https://wa.me/6285600600870?text={{ urlencode(__('Halo, saya ingin menanyakan ketersediaan Ruang Perawatan RS St. Elisabeth')) }}"
                        target="_blank"
                        rel="noopener"
                        class="btn-reset-room"
                    >
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                        <span>{{ __('Hubungi via WhatsApp') }}</span>
                    </a>
                </div>
                @endif

            </div>
        </section>

        {{-- ===== MODERN FLOATING RESERVATION CTA BANNER ===== --}}
        <section id="room-cta-banner" aria-label="{{ __('Siap Melakukan Reservasi?') }}">
            <div class="container">
                <div class="room-cta-card" id="room-cta-card">
                    <div class="room-cta-content">
                        <h2 class="cta-title">{{ __('Siap Melakukan Reservasi?') }}</h2>
                        <p class="cta-subtitle">
                            {{ __('Hubungi tim kami sekarang untuk memeriksa ketersediaan kamar dan mendapatkan informasi lebih lanjut tentang biaya perawatan.') }}
                        </p>
                        <div class="room-cta-actions">
                            <a
                                href="https://wa.me/6285600600870?text={{ urlencode(__('Halo, saya ingin reservasi ruang perawatan')) }}"
                                target="_blank"
                                rel="noopener"
                                class="btn-cta-white"
                            >
                                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                <span>{{ __('Hubungi via WhatsApp') }}</span>
                            </a>
                            <a
                                href="https://regonline.rs-elisabeth.com"
                                target="_blank"
                                rel="noopener"
                                class="btn-cta-outline"
                            >
                                <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                <span>{{ __('Daftar Online') }}</span>
                            </a>
                            <a href="tel:0248502244" class="btn-cta-outline">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                <span>(024) 8502244</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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
        'resources/js/ruang-perawatan/ruang-perawatan.js',
        'resources/js/components/back-to-top.js'
    ])
    @include('components.json-ld')
</body>
</html>
