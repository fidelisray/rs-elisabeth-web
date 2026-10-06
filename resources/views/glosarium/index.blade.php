{{-- {{ dd($glossary); }} --}}

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => 'Kamus Medis'])
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
            'resources/css/search-and-quick-access.css',
            'resources/css/top-bar.css'
        ])
    </head>
    <body>

    @include('components.navbar')

    <section id="hero-section">
            <div class="container">

                <!-- Breadcrumb -->
                <nav class="hero-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb flex-wrap">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        @if (preg_match('/^[A-Z]$/', $activeLetter))
                            <li class="breadcrumb-item"><a href="{{{ route('glossary.index') }}}">Kamus Medis</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Awalan '{{ $activeLetter }}'</li>
                        @else    
                            <li class="breadcrumb-item active"><a href="{{{ route('glossary.index') }}}">Kamus Medis</a></li>
                        @endif
                    </ol>
                </nav>

                <div class="row">
                    <!-- Kolom kiri: Judul, subjudul, search -->
                    <div class="col-12 col-lg-6">
                        <h1 class="hero-title">Ensiklopedia Istilah Medis & Kesehatan</h1>
                        <p class="hero-subtitle">Temukan penjelasan komprehensif dan mudah dipahami mengenai berbagai istilah medis, nama penyakit, dan nama gangguan kesehatan lainnya</p>

                        <p class="search-label fw-medium mb-2">Pencarian Istilah Medis</p>
                        {{-- <div class="search-box d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                            <form id="glossarySearchForm" role="search" autocomplete="off" onsubmit="return false;">
                                <input
                                    type="text"
                                    id="glossarySearchInput"
                                    name="q"
                                    class="form-control"
                                    placeholder="Cari istilah medis..."
                                    minlength="2"
                                    autocomplete="off"
                                >
                            </form>
                        </div> --}}


                        <div class="search-box d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                            <form action="{{{ route('glossary.index') }}}" method="GET" id="glossarySearchForm" role="search" autocomplete="off" class="d-flex w-100 align-items-center gap-2">
                                <input
                                    type="text"
                                    id="glossarySearchInput"
                                    name="q"
                                    value="{{ request('q') }}"
                                    class="form-control"
                                    placeholder="Cari istilah medis..."
                                    minlength="2"
                                    autocomplete="off"
                                >
                                @if(request('q'))
                                    <a href="{{{ route('glossary.index') }}}" id="resetSearchBtn" class="btn btn-outline-danger">
                                        Reset
                                    </a>
                                @else
                                    <button type="submit" class="btn btn-outline-success">
                                        Cari
                                    </button>
                                @endif
                            </form>
                        </div>



                    </div>
                    
                    <!-- Kolom kanan: Grid huruf A-Z -->
                    <div class="col-12 col-lg-6 mt-4 mt-lg-0 d-flex flex-column align-items-start align-items-lg-end">
                        <div class="letter-panel-label fw-medium mb-2">Telusuri Cepat Berdasarkan Abjad</div>
                            <div class="letter-grid">
                                {{-- <a href="#" class="letter-btn active">A</a> --}}
                                {{-- <a href="{{{ route('glossary.index') }}}"
                                    class="letter-btn {{ $activeLetter === 'ALL' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    All
                                </a> --}}
                                @foreach(range('A', 'Z') as $letter)
                                    @php $hasItems = in_array($letter, $availableLetters); @endphp
                                    <a href="{{ $hasItems ? route('glossary.index', ['letter' => $letter]) : '#' }}"
                                    class="letter-btn
                                        {{ $activeLetter === $letter ? 'active' : (!$hasItems ? 'btn-muted' : '') }}"
                                        {{ !$hasItems ? 'aria-disabled=true' : '' }}>
                                        {{ $letter }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
        
        
        
        <div id="defaultGlossaryContent" class="mt-4" style="background: radial-gradient(circle 360px at 10% 40%, rgba(0, 143, 215, 0.07), rgba(0, 143, 215, 0) 70%), linear-gradient(180deg, #f1f8fd 0%, #eef7fd 100%);">
            @if ($mode === 'explore')
                <section id="explore-services" class="py-5">
                    <div class="container" style="max-width: 1100px;">
                        <div class="row g-4">
                            <!-- Baris 1 -->
                            <div class="col-lg-7">
                                <a href="#" class="explore-bento-card bento-large d-flex flex-column justify-content-between text-decoration-none">
                                    <div class="bento-content position-relative z-2">
                                        <div class="icon-wrapper mb-4">
                                            <i class="fa-solid fa-stethoscope"></i>
                                        </div>
                                        <h2 class="bento-title mb-3">Deteksi Gejala Mandiri</h2>
                                        <p class="bento-desc mb-4">Ketahui kemungkinan penyebab keluhan Anda secara dini dan kapan waktu yang tepat untuk mencari pertolongan medis profesional.</p>
                                        <div class="bento-action mt-auto font-weight-bold">
                                            Mulai Pengecekan <i class="fa-solid fa-arrow-right ms-2 transition-icon"></i>
                                        </div>
                                    </div>
                                    <!-- Dekorasi latar belakang khusus untuk card ini -->
                                    <div class="bento-decor decor-1 z-1"></div>
                                </a>
                            </div>
                            
                            <div class="col-lg-5">
                                <a href="#" class="explore-bento-card bento-small d-flex flex-column h-100 text-decoration-none">
                                    <div class="icon-wrapper mb-4">
                                        <i class="fa-solid fa-pills"></i>
                                    </div>
                                    <h3 class="bento-title-sm mb-3">Info Obat & Tindakan</h3>
                                    <p class="bento-desc-sm mb-4">Cari tahu detail pengobatan dan prosedur medis berdasarkan diagnosis penyakit dengan sumber terpercaya.</p>
                                    <div class="bento-action mt-auto">
                                        Telusuri <i class="fa-solid fa-arrow-right ms-2 transition-icon"></i>
                                    </div>
                                </a>
                            </div>

                            <!-- Baris 2 -->
                            <div class="col-lg-4">
                                <a href="#" class="explore-bento-card bento-small d-flex flex-column h-100 text-decoration-none">
                                    <div class="icon-wrapper mb-4">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <h3 class="bento-title-sm mb-3">Komunitas & Dukungan</h3>
                                    <p class="bento-desc-sm mb-4">Temukan dukungan dan berbagilah pengalaman dalam grup komunitas pemulihan kami.</p>
                                    <div class="bento-action mt-auto">
                                        Gabung <i class="fa-solid fa-arrow-right ms-2 transition-icon"></i>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="col-lg-8">
                                <a href="#" class="explore-bento-card bento-large bento-premium d-flex flex-column justify-content-between text-decoration-none h-100">
                                    <div class="row h-100 align-items-center position-relative z-2">
                                        <div class="col-md-7 bento-content py-3">
                                            <div class="brand-badge mb-3 d-inline-block">
                                                <i class="fa-brands fa-google-play me-2"></i>
                                            </div>
                                            <h2 class="bento-title text-white mb-3">Aplikasi Mobile Elisameds</h2>
                                            <p class="bento-desc text-white-50 mb-4">Akses layanan pendaftaran jadwal dokter, riwayat rekam medis, hingga layanan pelanggan langsung dari genggaman Anda.</p>
                                            <div class="bento-action text-white">
                                                Unduh Sekarang <i class="fa-solid fa-arrow-right ms-2 transition-icon"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center position-relative h-100">
                                            <!-- Kita menggunakan icon hp besar sebagai ilustrasi -->
                                            <i class="fa-solid fa-mobile-screen-button display-1 text-white opacity-25" style="font-size: 10rem; position: absolute; right: 2rem;"></i>
                                        </div>
                                    </div>
                                    <div class="bento-decor decor-2 z-1"></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            @elseif ($mode === 'glosarium')
                <div class="container mt-4"></div>
                <section id="glosarium">
                    <div class="container">
                        <div class="title text-center">
                            <h2 class="display-8 fw-bold section-title">Kamus Medis Elisabeth</h2>
                        </div>
                        <div class="glosarium-container py-5">
                            <section class="content-body">
                                @if(count($glossary) > 0)
                                    @foreach($glossary as $prefix => $items)

                                        {{-- Header grup: "Ba", "Bi", "Ca", dst --}}
                                        <h4 class="fw-bold mt-4 mb-2 border-bottom pb-1">{{ $prefix }}</h4>

                                        @foreach($items as $item)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <a href="{{ route('glossary.show', urlencode($item['istilah'])) }}"
                                                class="text-decoration-none text-dark fs-6">
                                                    {{ $item['istilah'] }}
                                                </a>
                                            </div>
                                        @endforeach

                                    @endforeach
                                @else
                                    <div class="alert alert-info mt-3 fs-5">
                                        @if(isset($keyword) && $keyword !== '')
                                            Mohon maaf data yang anda inputkan <strong>{{ $keyword }}</strong> saat ini belum tersedia.
                                        @else
                                            Tidak ada istilah medis untuk huruf <strong>{{ $activeLetter }}</strong>.
                                        @endif
                                    </div>
                                @endif
                            </section>
                            <section class="content-side">

                            </section>
                        </div>
                    </div>
                </section>

            @endif
           
        </div>


        <section id="glosarium-search-result">
            <div class="container">
                {{-- <div id="glossarySearchResults" class="list-group"> --}}
                    {{-- Hasil pencarian akan di-render di sini oleh JS --}}
                {{-- </div> --}}
                <div id="glossarySearchResults" class="my-4" style="display: none;"></div>
            </div>






            <div class="modal fade" id="termDetailModal" tabindex="-1" aria-labelledby="termDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="termDetailModalLabel">Detail Istilah Medis</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body">
                            <h4 id="modalTermName" class="mb-3 text-dark fw-bold"></h4>
                            <p id="modalTermDescription" class="text-secondary" style="line-height: 1.6;"></p>
                        </div>
                        
                        <div class="modal-footer border-top-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                        
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