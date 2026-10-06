<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => '{{ $room->name }} - Ruang Perawatan RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/ruang-perawatan.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/top-bar.css'
    ])
</head>
<body>


    @include('components.navbar')

<main>
        <h1 class="visually-hidden">Detail Ruang Perawatan {{ $room->name }} - RS Santa Elisabeth Semarang</h1>

        {{-- ===== HERO SECTION & BREADCRUMBS ===== --}}
        <section id="hero-section" style="padding: 2rem 0;">
            <div class="container">
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('ruang-perawatan.index') }}">Ruang Perawatan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $room->name }}</li>
                    </ol>
                </nav>
            </div>
        </section>

        {{-- ===== DETAIL CONTENT ===== --}}
        <section id="room-detail" class="py-5" style="background: radial-gradient(circle 340px at 88% 10%, rgba(183, 228, 229, 0.35), rgba(183, 228, 229, 0) 70%), linear-gradient(180deg, #ffffff 0%, #eef7fd 100%);">
            <div class="container">
                <div class="row g-5">
                    
                    {{-- KIRI: Gambar Utama & Deskripsi --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
                            <img src="{{ $room->image_url ?? asset('images/feature.jpg') }}" class="w-100 object-fit-cover" alt="{{ $room->name }}" style="height: 400px;">
                            <div class="card-body p-4 p-md-5">
                                <span class="badge bg-primary px-3 py-2 rounded-pill fw-normal mb-3 text-uppercase" style="letter-spacing: 1px;">
                                    {{ $room->category === 'premium' ? 'Premium & Eksklusif' : 'Standar' }}
                                </span>
                                <h2 class="fw-bold mb-3" style="color: var(--secondary-color);">{{ $room->name }}</h2>
                                @if($room->tagline)
                                    <h5 class="text-muted mb-4 fst-italic">"{{ $room->tagline }}"</h5>
                                @endif
                                
                                <div class="d-flex flex-wrap gap-3 mb-4">
                                    @if($room->room_size)
                                        <div class="bg-light px-3 py-2 rounded border">
                                            <i class="fa-solid fa-vector-square text-primary me-2"></i> {{ $room->room_size }}
                                        </div>
                                    @endif
                                    @if($room->bed_count)
                                        <div class="bg-light px-3 py-2 rounded border">
                                            <i class="fa-solid fa-bed text-primary me-2"></i> {{ $room->bed_count }} Bed
                                        </div>
                                    @endif
                                    @if($room->max_companion)
                                        <div class="bg-light px-3 py-2 rounded border">
                                            <i class="fa-solid fa-user-group text-primary me-2"></i> Maks. {{ $room->max_companion }} Penunggu
                                        </div>
                                    @endif
                                </div>

                                <hr class="mb-4">

                                <div class="room-description fs-5" style="line-height: 1.8; color: #4b5563;">
                                    {!! $room->description !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: Fasilitas Khusus (Amenities) & CTA --}}
                    <div class="col-lg-4">
                        <div class="sticky-top" style="top: 100px; z-index: 10;">
                            
                            {{-- CTA Card --}}
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 text-center">
                                    <h4 class="fw-bold mb-3">Tertarik dengan ruangan ini?</h4>
                                    <p class="text-muted small mb-4">Hubungi kami untuk menanyakan ketersediaan kamar {{ $room->name }} atau informasi lebih lanjut.</p>
                                    
                                    @php
                                        $waText = $room->whatsapp_text ?? 'Halo, saya ingin menanyakan ketersediaan ruang perawatan ' . $room->name;
                                    @endphp
                                    
                                    <a href="https://wa.me/6285600600870?text={{ urlencode($waText) }}" target="_blank" class="btn btn-success w-100 rounded-pill py-3 fw-bold mb-3 d-flex justify-content-center align-items-center gap-2">
                                        <i class="fa-brands fa-whatsapp fs-4"></i> Tanya via WhatsApp
                                    </a>
                                    
                                    <a href="https://regonline.rs-elisabeth.com" target="_blank" class="btn btn-outline-primary w-100 rounded-pill py-3 fw-bold d-flex justify-content-center align-items-center gap-2">
                                        <i class="fa-regular fa-calendar-check fs-5"></i> Daftar Online
                                    </a>
                                </div>
                            </div>

                            {{-- Amenities & Highlights --}}
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold mb-4 border-bottom pb-2">Fasilitas Khusus</h5>
                                    
                                    @if($room->highlight_tags && is_array($room->highlight_tags))
                                        <div class="d-flex flex-wrap gap-2 mb-4">
                                            @foreach($room->highlight_tags as $tag)
                                                <span class="badge bg-info text-dark bg-opacity-10 border border-info rounded-pill px-3 py-2 fw-normal">
                                                    @if(isset($tag['icon'])) <i class="{{ $tag['icon'] }} me-1 text-info"></i> @endif
                                                    {{ $tag['label'] ?? '' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($room->amenities && is_array($room->amenities))
                                        <div class="accordion accordion-flush" id="accordionAmenities">
                                            @foreach($room->amenities as $index => $amenityGroup)
                                                <div class="accordion-item bg-transparent">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }} bg-transparent fw-bold px-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAmenity{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapseAmenity{{ $index }}">
                                                            {{ $amenityGroup['group'] ?? $amenityGroup['group_name'] ?? 'Fasilitas' }}
                                                        </button>
                                                    </h2>
                                                    <div id="collapseAmenity{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#accordionAmenities">
                                                        <div class="accordion-body px-0 pt-0 pb-3">
                                                            @php
                                                                $items = $amenityGroup['items'] ?? [];
                                                            @endphp
                                                            @if(!empty($items))
                                                                <ul class="list-unstyled mb-0">
                                                                    @foreach($items as $item)
                                                                        <li class="mb-2 d-flex align-items-start">
                                                                            <i class="fa-solid fa-check text-success mt-1 me-2"></i> 
                                                                            <span class="text-secondary">{{ is_array($item) ? $item['name'] : trim($item) }}</span>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

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
        'resources/js/ruang-perawatan/ruang-perawatan.js'
    ])
    @include('components.json-ld')
    </body>
</html>
