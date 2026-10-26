<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => __('Kamus Medis')])
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

        @include('components.hero-section', [
            'badgeIcon' => 'fa-solid fa-file-medical',
            'badgeText' => __('Definisi Istilah Medis'),
            'title' => $item['istilah'] ?? '',
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Kamus Medis'), 'url' => route('glossary.index')],
                ['label' => $item['istilah'] ?? ''],
            ],
        ])

        
        
        <section class="glosarium-detail py-5" style="background: radial-gradient(circle 340px at 12% 20%, rgba(0, 143, 215, 0.08), rgba(0, 143, 215, 0) 70%), linear-gradient(180deg, #ffffff 0%, #f1f8fd 100%);">
            <div class="container">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="title mb-4 fw-bold" style="color: #0b5c96;">
                            {{ $item['istilah'] ?? __('Istilah Tidak Ditemukan') }}
                        </h1>
                        <hr class="mb-4">
                        <div class="deskripsi-text fs-5 text-secondary" style="line-height: 1.8;">
                            {{ $item['deskripsi'] ?? __('Deskripsi tidak tersedia untuk istilah ini.') }}
                        </div>

                        @if(!empty($item['source']))
                            <div class="mt-4">
                                <small class="text-muted">
                                    {{ __('Sumber') }}:
                                    <a href="{{ $item['source'] }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                        {{ $item['source'] }}
                                    </a>
                                </small>
                            </div>
                        @endif

                        <div class="mt-5">
                            <a href="{{{ route('glossary.index') }}}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-arrow-left me-2"></i> {{ __('Kembali ke Kamus Medis') }}
                            </a>
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