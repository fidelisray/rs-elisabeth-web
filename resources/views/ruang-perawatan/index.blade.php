<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Temukan pilihan ruang perawatan RS St. Elisabeth Semarang — dari President Suite mewah hingga Kelas III, semua dengan pelayanan prima dan fasilitas berstandar tinggi.">
    <title>Ruang Perawatan - RS St. Elisabeth Semarang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/ruang-perawatan.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
    <style>
        /* Reveal animation helper — inline to avoid extra file */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal-on-scroll.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-on-scroll:nth-child(2) { transition-delay: 0.1s; }
        .reveal-on-scroll:nth-child(3) { transition-delay: 0.2s; }
        .reveal-on-scroll:nth-child(4) { transition-delay: 0.3s; }
    </style>
</head>
<body>


    @include('components.navbar')

<main>
        <h1 class="visually-hidden">Ruang Perawatan RS Santa Elisabeth Semarang</h1>

        {{-- ===== HERO SECTION ===== --}}
        <section id="hero-section">
            <div class="container">
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ruang Perawatan</li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h2 class="hero-title">Ruang Perawatan</h2>
                        <p class="hero-subtitle">Kenyamanan dan kesembuhan Anda adalah prioritas kami. Pilih ruang perawatan yang sesuai dengan kebutuhan, dari suite eksklusif hingga kelas yang terjangkau — semua dengan standar pelayanan paripurna.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===== INTRO ===== --}}
        <section id="room-intro">
            <div class="container text-center">
                <div class="section-title text-center mb-4">
                    <h2 class="fw-bold">Temukan Ruangan yang Tepat untuk Anda</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">RS St. Elisabeth Semarang menyediakan berbagai pilihan ruang perawatan untuk memenuhi kebutuhan dan kenyamanan setiap pasien. Setiap kamar dirancang dengan memperhatikan detail untuk memastikan lingkungan penyembuhan yang optimal.</p>
                </div>

                {{-- Filter Tabs --}}
                <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                    <button class="btn btn-sm px-4 py-2 rounded-pill fw-600 active"
                            data-room-filter="all"
                            style="background:#008fd7;color:#fff;border:none;font-weight:600;transition:all .2s;">
                        <i class="fa-solid fa-table-cells-large me-1"></i> Semua Kelas
                    </button>
                    <button class="btn btn-sm px-4 py-2 rounded-pill fw-600"
                            data-room-filter="premium"
                            style="background:#1a2740;color:#c9a84c;border:1px solid rgba(201,168,76,.35);font-weight:600;transition:all .2s;">
                        <i class="fa-solid fa-crown me-1"></i> Premium &amp; Eksklusif
                    </button>
                    <button class="btn btn-sm px-4 py-2 rounded-pill fw-600"
                            data-room-filter="standard"
                            style="background:#f0f7ff;color:#026199;border:1px solid #d0e8f5;font-weight:600;transition:all .2s;">
                        <i class="fa-solid fa-bed me-1"></i> Standar
                    </button>
                </div>
            </div>
        </section>

        {{-- ===== PREMIUM ROOMS (President Suite / Suites / Executive) ===== --}}
        <section id="premium-rooms">
            <div class="container position-relative">
                <div class="premium-section-header">
                    <div class="premium-badge">
                        <i class="fa-solid fa-crown"></i> Koleksi Eksklusif
                    </div>
                    <h2 class="premium-title">Ruang Perawatan Premium</h2>
                    <p class="premium-subtitle">Rasakan pengalaman perawatan setara hotel bintang lima dengan privasi penuh, desain interior elegan, dan layanan personal yang tak tertandingi.</p>
                </div>

                {{-- Grid: Dynamic Premium Cards --}}
                <div class="row g-4 pb-5">
                    @forelse($premiumRooms as $room)
                        <div class="col-12 col-lg-4 reveal-on-scroll">
                            <div class="premium-room-card {{ $loop->first ? 'card-featured' : '' }} h-100">
                                @if($loop->first)
                                    <div class="premium-ribbon"></div>
                                @endif
                                <div class="premium-img-wrapper">
                                    <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="d-block w-100 h-100">
                                        <img src="{{ $room->image_url ?? asset('images/feature.jpg') }}" alt="{{ $room->name }} RS St. Elisabeth Semarang">
                                    </a>
                                    <span class="room-category-label">{{ $room->name }}</span>
                                </div>
                                <div class="premium-card-body">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        @if($room->room_size || $room->bed_count)
                                        <span class="room-size-chip">
                                            <i class="fa-solid fa-vector-square"></i> {{ $room->room_size }} m² @if($room->room_size && $room->bed_count) • @endif {{ $room->bed_count }} Bed
                                        </span>
                                        @endif
                                        @if($room->max_companion)
                                        <span class="room-size-chip">
                                            <i class="fa-solid fa-user-group"></i> Maks. {{ $room->max_companion }} Penunggu
                                        </span>
                                        @endif
                                    </div>
                                    <h3 class="room-title">{{ $room->name }}</h3>
                                    <p class="room-tagline">{{ $room->tagline ?? $room->description }}</p>
                                    <hr class="gold-divider">
                                    
                                    @if($room->amenities && is_array($room->amenities))
                                        <div class="premium-amenities">
                                            @foreach($room->amenities as $amenityGroup)
                                                <div class="premium-amenity-group">
                                                    <h6>{{ $amenityGroup['group'] ?? $amenityGroup['group_name'] ?? 'Fasilitas' }}</h6>
                                                    @php
                                                        $items = $amenityGroup['items'] ?? [];
                                                    @endphp
                                                    @if(!empty($items))
                                                        <ul>
                                                            @foreach($items as $item)
                                                                <li>{{ is_array($item) ? $item['name'] : trim($item) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($room->highlight_tags && is_array($room->highlight_tags))
                                        <div class="premium-highlight-tags">
                                            @foreach($room->highlight_tags as $tag)
                                                <span class="htag">
                                                    @if(isset($tag['icon'])) <i class="{{ $tag['icon'] }}"></i> @endif
                                                    {{ $tag['label'] ?? '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="premium-card-cta">
                                        <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="btn-gold-outline w-100 mb-2 d-flex align-items-center justify-content-center gap-2">
                                            <i class="fa-solid fa-circle-info"></i> Lihat Detail Lengkap
                                        </a>
                                        <div class="d-flex w-100 gap-2">
                                            @php
                                                $waText = $room->whatsapp_text ?? 'Halo, saya ingin informasi ruangan ' . $room->name;
                                            @endphp
                                            <a href="https://wa.me/6285600600870?text={{ urlencode($waText) }}" target="_blank" class="btn-gold flex-grow-1 d-flex align-items-center justify-content-center gap-1">
                                                <i class="fa-brands fa-whatsapp"></i> Tanya
                                            </a>
                                            <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-gold-outline d-flex align-items-center justify-content-center">
                                                <i class="fa-regular fa-calendar-check"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 reveal-on-scroll">
                            <div class="empty-state-premium p-5 text-center rounded-4" style="background: linear-gradient(145deg, #1a2740, #131c2e); border: 1px solid rgba(201, 168, 76, 0.2); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                <div class="mb-4">
                                    <div class="icon-wrapper mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(201, 168, 76, 0.05); border: 1px solid rgba(201, 168, 76, 0.3);">
                                        <i class="fa-solid fa-crown" style="font-size: 2.2rem; color: #c9a84c;"></i>
                                    </div>
                                </div>
                                <h4 class="fw-bold mb-3" style="color: #c9a84c; letter-spacing: 0.5px;">Ruang Perawatan Premium Sedang Kami Siapkan</h4>
                                <p class="mb-4 mx-auto" style="max-width: 600px; color: #a8b2c1; font-size: 1.05rem; line-height: 1.6;">
                                    Informasi detail mengenai fasilitas dan layanan Ruang Perawatan Premium kami saat ini sedang dalam tahap pembaruan untuk memberikan pengalaman visual terbaik dan informasi paling akurat untuk Anda.
                                </p>
                                <a href="https://wa.me/6285600600870?text=Halo,%20saya%20ingin%20menanyakan%20ketersediaan%20dan%20informasi%20Ruang%20Perawatan%20Premium%20RS%20St.%20Elisabeth" target="_blank" class="btn px-4 py-2 rounded-pill fw-bold" style="background: #c9a84c; color: #1a2740; transition: all 0.3s ease;">
                                    <i class="fa-brands fa-whatsapp me-2"></i> Hubungi Personal Assistant Kami
                                </a>
                            </div>
                        </div>
                    @endforelse

                    @if(false) {{-- OLD HARDCODED PREMIUM ROOMS --}}
                    {{-- 1. PRESIDENT SUITE --}}
                    <div class="col-12 col-lg-4 reveal-on-scroll">
                        <div class="premium-room-card card-featured h-100">
                            <div class="premium-ribbon"></div>
                            <div class="premium-img-wrapper">
                                <img src="{{ asset('images/feature.jpg') }}" alt="President Suite RS St. Elisabeth Semarang">
                                <span class="room-category-label">President Suite</span>
                            </div>
                            <div class="premium-card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-vector-square"></i> ~40 m² · 1 Tempat Tidur
                                    </span>
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-user-group"></i> Max 2 Penunggu
                                    </span>
                                </div>
                                <h3 class="room-title">President Suite</h3>
                                <p class="room-tagline">Kemewahan tertinggi dengan privasi absolut. Kamar perawatan terluas kami, dirancang bagi pasien yang menginginkan pengalaman perawatan di lingkungan yang eksklusif dan tenang.</p>
                                <hr class="gold-divider">
                                <div class="premium-amenities">
                                    <div class="premium-amenity-group">
                                        <h6>Kamar &amp; Ruangan</h6>
                                        <ul>
                                            <li>Ruang tamu &amp; sofa mewah</li>
                                            <li>Kamar mandi suite dengan bathtub</li>
                                            <li>Ruang tunggu keluarga private</li>
                                            <li>Pantry &amp; mini bar</li>
                                            <li>AC inverter individual</li>
                                        </ul>
                                    </div>
                                    <div class="premium-amenity-group">
                                        <h6>Layanan Eksklusif</h6>
                                        <ul>
                                            <li>Perawat personal 24 jam</li>
                                            <li>Menu makan pilihan premium</li>
                                            <li>Konsultasi dokter spesialis</li>
                                            <li>TV LED 55" + streaming</li>
                                            <li>Wi-Fi dedicated broadband</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="premium-highlight-tags">
                                    <span class="htag"><i class="fa-solid fa-crown"></i> Kamar Terluas</span>
                                    <span class="htag"><i class="fa-solid fa-utensils"></i> Makan 3x Sehari</span>
                                    <span class="htag"><i class="fa-solid fa-shield-heart"></i> Perawat Personal</span>
                                    <span class="htag"><i class="fa-solid fa-wifi"></i> Wi-Fi Premium</span>
                                </div>
                                <div class="premium-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20President%20Suite" target="_blank" class="btn-gold">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                    <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-gold-outline">
                                        <i class="fa-regular fa-calendar-check"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. SUITES --}}
                    <div class="col-12 col-lg-4 reveal-on-scroll">
                        <div class="premium-room-card h-100">
                            <div class="premium-ribbon"></div>
                            <div class="premium-img-wrapper">
                                <img src="{{ asset('images/F1671680659.jpg') }}" alt="Suites RS St. Elisabeth Semarang">
                                <span class="room-category-label">Suites</span>
                            </div>
                            <div class="premium-card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-vector-square"></i> ~30 m² · 1 Tempat Tidur
                                    </span>
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-user-group"></i> Max 2 Penunggu
                                    </span>
                                </div>
                                <h3 class="room-title">Suites</h3>
                                <p class="room-tagline">Ruang perawatan premium dengan suasana seperti kamar hotel berbintang. Nikmati privasi penuh dalam ruangan yang luas dan nyaman dengan berbagai fasilitas lengkap.</p>
                                <hr class="gold-divider">
                                <div class="premium-amenities">
                                    <div class="premium-amenity-group">
                                        <h6>Kamar &amp; Ruangan</h6>
                                        <ul>
                                            <li>Sofa bed untuk penunggu</li>
                                            <li>Kamar mandi dalam shower cabin</li>
                                            <li>Lemari pakaian built-in</li>
                                            <li>Meja kerja &amp; kursi</li>
                                            <li>AC individual thermostat</li>
                                        </ul>
                                    </div>
                                    <div class="premium-amenity-group">
                                        <h6>Layanan Eksklusif</h6>
                                        <ul>
                                            <li>Perawat dedicated satu shift</li>
                                            <li>Menu makan pilihan RS</li>
                                            <li>TV LED 43"</li>
                                            <li>Wi-Fi kecepatan tinggi</li>
                                            <li>Kulkas &amp; dispenser air</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="premium-highlight-tags">
                                    <span class="htag"><i class="fa-solid fa-star"></i> Privasi Penuh</span>
                                    <span class="htag"><i class="fa-solid fa-utensils"></i> Makan 3x Sehari</span>
                                    <span class="htag"><i class="fa-solid fa-tv"></i> Smart TV</span>
                                    <span class="htag"><i class="fa-solid fa-snowflake"></i> AC Individual</span>
                                </div>
                                <div class="premium-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20Suites" target="_blank" class="btn-gold">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                    <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-gold-outline">
                                        <i class="fa-regular fa-calendar-check"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. EXECUTIVE --}}
                    <div class="col-12 col-lg-4 reveal-on-scroll">
                        <div class="premium-room-card h-100">
                            <div class="premium-ribbon"></div>
                            <div class="premium-img-wrapper">
                                <img src="{{ asset('images/F1671680579.jpg') }}" alt="Executive RS St. Elisabeth Semarang">
                                <span class="room-category-label">Executive</span>
                            </div>
                            <div class="premium-card-body">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-vector-square"></i> ~24 m² · 1 Tempat Tidur
                                    </span>
                                    <span class="room-size-chip">
                                        <i class="fa-solid fa-user-group"></i> Max 1 Penunggu
                                    </span>
                                </div>
                                <h3 class="room-title">Executive</h3>
                                <p class="room-tagline">Pilihan ideal bagi pasien yang mengutamakan privasi dengan anggaran lebih terjangkau. Ruangan bersih, modern, dan dilengkapi fasilitas yang cukup untuk kenyamanan selama perawatan.</p>
                                <hr class="gold-divider">
                                <div class="premium-amenities">
                                    <div class="premium-amenity-group">
                                        <h6>Kamar &amp; Ruangan</h6>
                                        <ul>
                                            <li>Kamar rawat private 1 bed</li>
                                            <li>Kamar mandi dalam</li>
                                            <li>Sofa/kursi untuk penunggu</li>
                                            <li>Lemari pakaian</li>
                                            <li>AC individual</li>
                                        </ul>
                                    </div>
                                    <div class="premium-amenity-group">
                                        <h6>Layanan</h6>
                                        <ul>
                                            <li>Pelayanan perawat standar</li>
                                            <li>Makan 3x sehari dari RS</li>
                                            <li>TV LED 32"</li>
                                            <li>Wi-Fi tersedia</li>
                                            <li>Dispenser air minum</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="premium-highlight-tags">
                                    <span class="htag"><i class="fa-solid fa-lock"></i> Privasi Terjaga</span>
                                    <span class="htag"><i class="fa-solid fa-utensils"></i> Makan Termasuk</span>
                                    <span class="htag"><i class="fa-solid fa-wifi"></i> Wi-Fi</span>
                                    <span class="htag"><i class="fa-solid fa-bed"></i> 1 Tempat Tidur</span>
                                </div>
                                <div class="premium-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20Executive%20Room" target="_blank" class="btn-gold">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                    <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-gold-outline">
                                        <i class="fa-regular fa-calendar-check"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif {{-- END OLD HARDCODED PREMIUM ROOMS --}}
                </div>{{-- end row premium --}}
            </div>{{-- end container --}}
        </section>

        {{-- ===== STANDARD ROOMS (VIP / Kelas I / II / III) ===== --}}
        <section id="standard-rooms">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Ruang Perawatan Standar &amp; VIP</h2>
                    <div class="divider"></div>
                    <p class="text-muted mt-3">Dirancang untuk memberikan perawatan berkualitas dengan fasilitas yang memadai dan harga yang dapat disesuaikan dengan berbagai kebutuhan pasien.</p>
                </div>

                <div class="row g-4">
                    @forelse($standardRooms as $room)
                        <div class="col-12 col-md-6 col-xl-3 reveal-on-scroll">
                            <div class="standard-room-card room-{{ strtolower(str_replace(' ', '', $room->name)) }}">
                                <div class="card-top-bar"></div>
                                <div class="std-img-wrapper">
                                    <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="d-block w-100 h-100">
                                        <img src="{{ $room->image_url ?? asset('images/placeholder.jpg') }}" alt="{{ $room->name }} RS St. Elisabeth Semarang">
                                    </a>
                                </div>
                                <div class="std-card-body">
                                    <span class="std-class-label">
                                        <i class="fa-solid fa-bed"></i> {{ $room->name }}
                                    </span>
                                    <h3 class="std-room-name">{{ $room->name }}</h3>
                                    
                                    @if($room->room_size || $room->bed_count)
                                    <p class="std-room-size">
                                        <i class="fa-solid fa-vector-square"></i> {{ $room->room_size }} @if($room->room_size && $room->bed_count) · @endif {{ $room->bed_count }} Bed
                                    </p>
                                    @endif
                                    
                                    <p class="std-desc">{{ Str::limit(strip_tags($room->description), 180) }}</p>
                                    
                                    @if($room->highlight_tags && is_array($room->highlight_tags))
                                    <div class="std-amenity-chips">
                                        @foreach($room->highlight_tags as $tag)
                                            <span class="chip">
                                                @if(isset($tag['icon'])) <i class="{{ $tag['icon'] }}"></i> @endif
                                                {{ $tag['label'] ?? '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    @endif

                                    <div class="std-card-cta d-flex flex-column gap-2 mt-auto">
                                        <a href="{{ route('ruang-perawatan.show', $room->slug) }}" class="btn btn-outline-primary w-100 rounded-pill d-flex align-items-center justify-content-center gap-2">
                                            <i class="fa-solid fa-circle-info"></i> Lihat Detail
                                        </a>
                                        @php
                                            $waText = $room->whatsapp_text ?? 'Halo, saya ingin informasi ruangan ' . $room->name;
                                        @endphp
                                        <a href="https://wa.me/6285600600870?text={{ urlencode($waText) }}" target="_blank" class="btn-blue w-100 d-flex align-items-center justify-content-center gap-2">
                                            <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 reveal-on-scroll">
                            <div class="empty-state-standard p-5 text-center rounded-4" style="background-color: var(--bg-soft-beige, #fdf8f5); border: 2px dashed #d0e8f5;">
                                <div class="mb-3">
                                    <i class="fa-solid fa-bed-pulse" style="font-size: 3.5rem; color: #b8d4e3;"></i>
                                </div>
                                <h4 class="fw-bold mb-2" style="color: var(--secondary-darker-color, #1a2740);">Data Ruangan Sedang Diperbarui</h4>
                                <p class="text-muted mb-4 mx-auto" style="max-width: 550px;">
                                    Informasi ruang perawatan standar dan VIP saat ini sedang disinkronisasi. Silakan hubungi layanan pelanggan kami untuk mengecek ketersediaan kamar secara real-time.
                                </p>
                                <a href="https://wa.me/6285600600870?text=Halo,%20saya%20ingin%20menanyakan%20informasi%20ketersediaan%20Ruang%20Perawatan%20Standar%20di%20RS%20St.%20Elisabeth" target="_blank" class="btn btn-outline-primary px-4 py-2 rounded-pill">
                                    <i class="fa-brands fa-whatsapp me-2"></i> Tanya Ketersediaan Kamar
                                </a>
                            </div>
                        </div>
                    @endforelse

                    @if(false) {{-- OLD HARDCODED STANDARD ROOMS --}}
                    {{-- VIP --}}
                    <div class="col-12 col-md-6 col-xl-3 reveal-on-scroll">
                        <div class="standard-room-card room-vip">
                            <div class="card-top-bar"></div>
                            <div class="std-img-wrapper">
                                <img src="{{ asset('images/F1671680565.jpg') }}" alt="VIP Room RS St. Elisabeth Semarang">
                            </div>
                            <div class="std-card-body">
                                <span class="std-class-label">
                                    <i class="fa-solid fa-gem"></i> VIP
                                </span>
                                <h3 class="std-room-name">VIP</h3>
                                <p class="std-room-size">
                                    <i class="fa-solid fa-vector-square"></i> ~20 m² · 1 Tempat Tidur
                                </p>
                                <p class="std-desc">
                                    Ruang perawatan semi-privat dengan nuansa yang lebih nyaman. Cocok untuk pasien yang menginginkan ketenangan namun dengan biaya yang lebih terjangkau dari kelas Executive, dengan 1 tempat tidur dan fasilitas standar yang lengkap.
                                </p>
                                <div class="std-amenity-chips">
                                    <span class="chip"><i class="fa-solid fa-bed"></i> 1 Bed Privat</span>
                                    <span class="chip"><i class="fa-solid fa-bath"></i> Kamar Mandi Dalam</span>
                                    <span class="chip"><i class="fa-solid fa-tv"></i> TV</span>
                                    <span class="chip"><i class="fa-solid fa-snowflake"></i> AC</span>
                                    <span class="chip"><i class="fa-solid fa-wifi"></i> Wi-Fi</span>
                                    <span class="chip"><i class="fa-solid fa-utensils"></i> Makan 3x</span>
                                </div>
                                <div class="std-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20VIP%20Room" target="_blank" class="btn-blue">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kelas I --}}
                    <div class="col-12 col-md-6 col-xl-3 reveal-on-scroll">
                        <div class="standard-room-card room-kelas1">
                            <div class="card-top-bar"></div>
                            <div class="std-img-wrapper">
                                <img src="{{ asset('images/F1670914854.jpg') }}" alt="Kelas I RS St. Elisabeth Semarang">
                            </div>
                            <div class="std-card-body">
                                <span class="std-class-label">
                                    <i class="fa-solid fa-1"></i> Kelas I
                                </span>
                                <h3 class="std-room-name">Kelas I</h3>
                                <p class="std-room-size">
                                    <i class="fa-solid fa-vector-square"></i> 2 Tempat Tidur per Kamar
                                </p>
                                <p class="std-desc">
                                    Kamar bersama untuk 2 pasien dengan sekat privasi yang memadai. Dilengkapi dengan fasilitas standar yang nyaman, termasuk kamar mandi dalam, AC, dan TV. Ideal bagi pasien yang tidak memerlukan ruang isolasi penuh.
                                </p>
                                <div class="std-amenity-chips">
                                    <span class="chip"><i class="fa-solid fa-bed-pulse"></i> 2 Bed per Kamar</span>
                                    <span class="chip"><i class="fa-solid fa-bath"></i> Kamar Mandi Dalam</span>
                                    <span class="chip"><i class="fa-solid fa-tv"></i> TV</span>
                                    <span class="chip"><i class="fa-solid fa-snowflake"></i> AC</span>
                                    <span class="chip"><i class="fa-solid fa-utensils"></i> Makan 3x</span>
                                    <span class="chip"><i class="fa-solid fa-user"></i> 1 Penunggu</span>
                                </div>
                                <div class="std-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20Kelas%20I" target="_blank" class="btn-blue">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kelas II --}}
                    <div class="col-12 col-md-6 col-xl-3 reveal-on-scroll">
                        <div class="standard-room-card room-kelas2">
                            <div class="card-top-bar"></div>
                            <div class="std-img-wrapper">
                                <img src="{{ asset('images/F1670914872.jpg') }}" alt="Kelas II RS St. Elisabeth Semarang">
                            </div>
                            <div class="std-card-body">
                                <span class="std-class-label">
                                    <i class="fa-solid fa-2"></i> Kelas II
                                </span>
                                <h3 class="std-room-name">Kelas II</h3>
                                <p class="std-room-size">
                                    <i class="fa-solid fa-vector-square"></i> 3–4 Tempat Tidur per Kamar
                                </p>
                                <p class="std-desc">
                                    Kamar perawatan bersama untuk 3–4 pasien dengan fasilitas lengkap dan pelayanan medis yang sama baiknya dengan kelas lain. Pilihan ekonomis yang tetap mengutamakan kebersihan, kenyamanan, dan keselamatan pasien.
                                </p>
                                <div class="std-amenity-chips">
                                    <span class="chip"><i class="fa-solid fa-bed-pulse"></i> 3–4 Bed per Kamar</span>
                                    <span class="chip"><i class="fa-solid fa-bath"></i> Kamar Mandi Bersama</span>
                                    <span class="chip"><i class="fa-solid fa-tv"></i> TV Bersama</span>
                                    <span class="chip"><i class="fa-solid fa-snowflake"></i> AC Central</span>
                                    <span class="chip"><i class="fa-solid fa-utensils"></i> Makan 3x</span>
                                </div>
                                <div class="std-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20Kelas%20II" target="_blank" class="btn-blue">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kelas III --}}
                    <div class="col-12 col-md-6 col-xl-3 reveal-on-scroll">
                        <div class="standard-room-card room-kelas3">
                            <div class="card-top-bar"></div>
                            <div class="std-img-wrapper">
                                <img src="{{ asset('images/F1670220299.jpg') }}" alt="Kelas III RS St. Elisabeth Semarang">
                            </div>
                            <div class="std-card-body">
                                <span class="std-class-label">
                                    <i class="fa-solid fa-3"></i> Kelas III
                                </span>
                                <h3 class="std-room-name">Kelas III</h3>
                                <p class="std-room-size">
                                    <i class="fa-solid fa-vector-square"></i> 5–6 Tempat Tidur per Kamar
                                </p>
                                <p class="std-desc">
                                    Ruang perawatan umum dengan kapasitas lebih besar, tetap dijaga kebersihannya dan memiliki akses layanan medis yang sama. Tersedia untuk peserta BPJS Kesehatan. Pelayanan perawat dilakukan secara rutin dan terstandar.
                                </p>
                                <div class="std-amenity-chips">
                                    <span class="chip"><i class="fa-solid fa-bed-pulse"></i> 5–6 Bed per Kamar</span>
                                    <span class="chip"><i class="fa-solid fa-bath"></i> Kamar Mandi Bersama</span>
                                    <span class="chip"><i class="fa-solid fa-snowflake"></i> AC Central</span>
                                    <span class="chip"><i class="fa-solid fa-utensils"></i> Makan 3x</span>
                                    <span class="chip"><i class="fa-solid fa-id-card"></i> BPJS Kesehatan</span>
                                </div>
                                <div class="std-card-cta">
                                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20informasi%20Kelas%20III" target="_blank" class="btn-blue">
                                        <i class="fa-brands fa-whatsapp"></i> Tanya Ketersediaan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endif {{-- END OLD HARDCODED STANDARD ROOMS --}}
                </div>{{-- end row standard --}}
            </div>{{-- end container --}}
        </section>

        {{-- ===== COMPARISON TABLE (DYNAMIC) ===== --}}
        @php
            // Menggabungkan Premium dan Standar agar Premium selalu di depan.
            // Masing-masing kelompok sudah diurutkan berdasarkan sort_order dari controller.
            $allRooms = $premiumRooms->merge($standardRooms);
            
            // Mengumpulkan semua fitur unik yang ditandai "show_in_comparison" dari seluruh ruangan
            $allFeatures = collect();
            foreach($allRooms as $room) {
                $amenities = $room->amenities;
                if(is_array($amenities)) {
                    foreach($amenities as $group) {
                        foreach($group['items'] ?? [] as $item) {
                            if(is_array($item) && ($item['show_in_comparison'] ?? false)) {
                                $allFeatures->push($item['name']);
                            }
                        }
                    }
                }
            }
            $uniqueFeatures = $allFeatures->unique()->values();
        @endphp

        @if($allRooms->isNotEmpty() && $uniqueFeatures->isNotEmpty())
        <section id="room-comparison">
            <div class="container">
                <div class="comparison-header">
                    <h2 class="comparison-title">Perbandingan Fasilitas Ruangan</h2>
                    <p class="comparison-desc">Bandingkan semua fasilitas di setiap kelas ruang perawatan kami secara mudah.</p>
                </div>
                <div class="table-responsive">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding-left:1.25rem;">Fasilitas</th>
                                @foreach($allRooms as $room)
                                    <th class="{{ $room->category === 'premium' ? 'col-premium' : '' }}">
                                        {{ $room->name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uniqueFeatures as $featureName)
                            <tr>
                                <td>{{ $featureName }}</td>
                                @foreach($allRooms as $room)
                                    @php
                                        $val = 'no';
                                        $displayValue = '';
                                        $isPremium = $room->category === 'premium';
                                        
                                        // Cari item yang cocok di dalam amenities ruangan ini
                                        $amenities = $room->amenities;
                                        if(is_array($amenities)) {
                                            foreach($amenities as $group) {
                                                foreach($group['items'] ?? [] as $item) {
                                                    if(is_array($item) && ($item['name'] ?? '') === $featureName) {
                                                        if(empty($item['value'])) {
                                                            $val = $isPremium ? 'premium' : 'yes';
                                                        } else {
                                                            $val = 'text';
                                                            $displayValue = $item['value'];
                                                        }
                                                        break 2;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        $tdClass = $isPremium ? 'cell-premium' : '';
                                    @endphp
                                    <td class="{{ $tdClass }}">
                                        @if($val === 'yes')
                                            <i class="fa-solid fa-check check-yes"></i>
                                        @elseif($val === 'no')
                                            <i class="fa-solid fa-minus check-no"></i>
                                        @elseif($val === 'premium')
                                            <i class="fa-solid fa-check check-gold"></i>
                                        @elseif($val === 'text')
                                            <small {!! $isPremium ? 'style="color:#c9a84c"' : 'class="text-muted"' !!}>
                                                {{ $displayValue }}
                                            </small>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-center text-muted mt-3" style="font-size:0.8rem;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Fasilitas dapat berubah. Hubungi kami untuk informasi terkini dan ketersediaan kamar.
                </p>
            </div>
        </section>
        @endif

        {{-- OLD ROOM-COMPARISON SECTION FOR BACKUP --}}
        @if(false)
        <section id="room-comparison">
            <div class="container">
                <div class="comparison-header">
                    <h2 class="comparison-title">Perbandingan Fasilitas Ruangan</h2>
                    <p class="comparison-desc">Bandingkan semua fasilitas di setiap kelas ruang perawatan kami secara mudah.</p>
                </div>
                <div class="table-responsive">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding-left:1.25rem;">Fasilitas</th>
                                <th class="col-premium-top">President Suite</th>
                                <th class="col-premium">Suites</th>
                                <th class="col-premium">Executive</th>
                                <th>VIP</th>
                                <th>Kelas I</th>
                                <th>Kelas II</th>
                                <th>Kelas III</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Kamar mandi dalam (private)</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>AC individual (thermostat)</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><small class="text-muted">Central</small></td>
                                <td><small class="text-muted">Central</small></td>
                            </tr>
                            <tr>
                                <td>Televisi</td>
                                <td class="cell-premium"><small style="color:#c9a84c">55" Smart</small></td>
                                <td class="cell-premium"><small style="color:#c9a84c">43" Smart</small></td>
                                <td class="cell-premium"><small style="color:#c9a84c">32" TV</small></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><small class="text-muted">Bersama</small></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>Wi-Fi Internet</td>
                                <td class="cell-premium"><small style="color:#c9a84c">Dedicated</small></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>Ruang tamu / sofa</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>Kulkas</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>Makan pasien (3× sehari)</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                            </tr>
                            <tr>
                                <td>Perawat personal / dedicated</td>
                                <td class="cell-premium"><i class="fa-solid fa-check check-gold"></i></td>
                                <td class="cell-premium"><small style="color:#c9a84c">Per shift</small></td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                            </tr>
                            <tr>
                                <td>Jumlah bed per kamar</td>
                                <td class="cell-premium"><small style="color:#c9a84c">1 (eksklusif)</small></td>
                                <td class="cell-premium"><small style="color:#c9a84c">1 (eksklusif)</small></td>
                                <td class="cell-premium"><small style="color:#c9a84c">1 (private)</small></td>
                                <td><small>1</small></td>
                                <td><small>2</small></td>
                                <td><small>3–4</small></td>
                                <td><small>5–6</small></td>
                            </tr>
                            <tr>
                                <td>Akomodasi BPJS Kesehatan</td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td class="cell-premium"><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-minus check-no"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                                <td><i class="fa-solid fa-check check-yes"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-center text-muted mt-3" style="font-size:0.8rem;">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Fasilitas dapat berubah. Hubungi kami untuk informasi terkini dan ketersediaan kamar.
                </p>
            </div>
        </section>
        @endif

        {{-- ===== CTA BANNER ===== --}}
        <section id="room-cta-banner">
            <div class="container position-relative" style="z-index:1;">
                <h2 class="cta-title">Siap Melakukan Reservasi?</h2>
                <p class="cta-subtitle">Hubungi tim kami sekarang untuk memeriksa ketersediaan kamar dan mendapatkan informasi lebih lanjut tentang biaya perawatan.</p>
                <div class="d-flex justify-content-center flex-wrap">
                    <a href="https://wa.me/6285600600870?text=Halo%2C%20saya%20ingin%20reservasi%20ruang%20perawatan" target="_blank" class="btn-cta-white">
                        <i class="fa-brands fa-whatsapp fs-5"></i> Hubungi via WhatsApp
                    </a>
                    <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn-cta-outline">
                        <i class="fa-regular fa-calendar-check"></i> Daftar Online
                    </a>
                    <a href="tel:024850224" class="btn-cta-outline">
                        <i class="fa-solid fa-phone"></i> (024) 8502244
                    </a>
                </div>
            </div>
        </section>

    </main>

    {{-- ===== FOOTER ===== --}}
    <footer id="footer" class="pt-5">
        <div class="container-fluid col-12 col-md-12">
            <div class="container">
                <div class="row footer-body">
                    <div class="col mb-5">
                        <div class="footer-header mb-4">
                            <ul>
                                <li><h4 class="rs-name">RS St. Elisabeth Semarang</h4></li>
                                <li><p class="moto">Pancaran cintanya menyembuhkan derita sesama</p></li>
                            </ul>
                        </div>
                        <ul>
                            <li><h4 class="footer-title">Hubungi Kami</h4></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-location-dot"></i> Jl. Kawi No.1</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-phone"></i> (024) 8502244</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-phone"></i> (024) 8310076 / (024) 8310035</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-envelope"></i> sekretariat@365.rs-elisabeth.com</a></li>
                        </ul>
                        <div class="social-media d-flex gap-2">
                            <div class="insta"><i class="fa-brands fa-instagram"></i></div>
                            <div class="facebook"><i class="fa-brands fa-facebook"></i></div>
                            <div class="youtube"><i class="fa-brands fa-youtube"></i></div>
                        </div>
                    </div>
                    <div class="col">
                        <ul>
                            <li><h4 class="footer-title">Tautan Cepat</h4></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Tentang Kami</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Elisanews</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Artikel</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Hubungi Kami</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Rekanan</a></li>
                            <li class="footer-list"><a href="#"><i class="fa-solid fa-caret-right"></i> Perpustakaan Online</a></li>
                        </ul>
                    </div>
                    <div class="col">
                        <ul>
                            <li><h4 class="footer-title">Elisameds</h4></li>
                            <li class="footer-list">
                                <p class="elisameds-desc">Aplikasi Mobile Rumah Sakit St. Elisabeth Semarang untuk meningkatkan kualitas pelayanan kesehatan kepada pasien.</p>
                            </li>
                            <li><a href="https://play.google.com/store/apps/details?id=com.elisameds.app" aria-label="Unduh aplikasi Elisameds di Google Play Store"><i class="fa-brands fa-google-play"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="row footer-copyright">
                    <div class="col">
                        <p class="copyright"><i class="fa-solid fa-copyright"></i> 2026 Rumah Sakit Santa Elisabeth Semarang</p>
                    </div>
                    <div class="col d-flex justify-content-center gap-3">
                        <p class="d-inline-block">Designed By Lorem, ipsum dolor.</p>
                        <p class="d-inline-block">Developed By Lorem, ipsum.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @include('components.floating-buttons')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js',
        'resources/js/ruang-perawatan/ruang-perawatan.js'
    ])
</body>
</html>
