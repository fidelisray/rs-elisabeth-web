@props([
    'title' => null,
    'subtitle' => null,
    'headingTag' => 'h1',
    'breadcrumbs' => null,
    'customClass' => '',
    'rightCol' => null,
])

@php
    // Default breadcrumbs jika tidak diberikan: Beranda > [Title]
    if ($breadcrumbs === null && !empty($title)) {
        $breadcrumbs = [
            ['label' => __('Beranda'), 'url' => url('/')],
            ['label' => $title],
        ];
    }
@endphp

<section id="hero-section" class="{{ $customClass }}">
    <div class="container">
        @if(!empty($breadcrumbs))
            <nav class="hero-breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb flex-wrap">
                    @foreach($breadcrumbs as $crumb)
                        @if($loop->last || empty($crumb['url']))
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        @if(!empty($title) || !empty($slot) || !empty($rightCol))
            <div class="row align-items-center">
                {{-- Kolom Kiri: Judul, Subjudul, Konten Tambahan --}}
                <div class="{{ !empty($rightCol) ? 'col-12 col-lg-6' : 'col-12 col-lg-8' }}">
                    @if(!empty($title))
                        <{{ $headingTag }} class="hero-title">{{ $title }}</{{ $headingTag }}>
                    @endif

                    @if(!empty($subtitle))
                        <p class="hero-subtitle">{{ $subtitle }}</p>
                    @endif

                    {{ $slot ?? '' }}
                </div>

                {{-- Kolom Kanan Opsional (misal: Grid Abjad A-Z di Glosarium) --}}
                @if(!empty($rightCol))
                    <div class="col-12 col-lg-6 mt-4 mt-lg-0 d-flex flex-column align-items-start align-items-lg-end">
                        {{ $rightCol }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
