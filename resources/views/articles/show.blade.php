<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('components.seo-meta', ['title' => '{{ $article->title }} - RS St. Elisabeth Semarang'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
            'resources/css/style.css',
            'resources/css/btn-accent.css',
            'resources/css/hero.css',
            'resources/css/news.css',
            'resources/css/navbar-dropdown.css',
            'resources/css/top-bar.css',
            'resources/css/search-and-quick-access.css',
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
                        <li class="breadcrumb-item"><a href="{{{ route('articles.index') }}}">Berita & Artikel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($article->title, 30) }}</li>
                    </ol>
                </nav>
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <h1 class="hero-title">{{ $article->title }}</h1>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="py-5" style="background: radial-gradient(circle 340px at 85% 10%, rgba(183, 228, 229, 0.35), rgba(183, 228, 229, 0) 70%), linear-gradient(180deg, #ffffff 0%, #eef7fd 100%);">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="article-header text-center">
                            <div class="article-meta justify-content-center">
                                <span><i class="fa-regular fa-calendar me-2"></i> {{ $article->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</span>
                                <span><i class="fa-regular fa-folder-open me-2"></i> Rumah Sakit St. Elisabeth</span>
                            </div>
                        </div>
                        
                        <img src="{{ $article->image_url ?? asset('images/hero.jpg') }}" class="article-featured-image" alt="{{ $article->title }}">
                        
                        <div class="article-content">
                            {!! $article->content !!}
                        </div>
                        
                        <div class="mt-5 border-top pt-4 text-center">
                            <a href="{{{ route('articles.index') }}}" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-bold">
                                <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Artikel
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
            'resources/js/navbar/navbar-dropdown.js'
        ])
        @include('components.json-ld')
    </body>
</html>
