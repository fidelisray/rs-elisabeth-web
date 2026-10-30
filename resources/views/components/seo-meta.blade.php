@php
    // Sumber kebenaran pemetaan locale aplikasi → locale Open Graph (xx_YY).
    // Menambah bahasa baru cukup menambah entri di sini tanpa mengubah markup.
    $ogLocales = ['id' => 'id_ID', 'en' => 'en_US'];

    $activeLocale = app()->getLocale();
    $ogLocale = $ogLocales[$activeLocale] ?? ($ogLocales['id'] ?? 'id_ID');
    $alternateLocales = array_values(array_diff($ogLocales, [$ogLocale]));
@endphp
<meta name="description" content="{{ __('Rumah Sakit St. Elisabeth Semarang mengedepankan keselamatan, mutu, dan terpercaya serta menjadi sarana kehadiran cinta dan kuasa Allah, terakreditasi Paripurna oleh Komite Akreditasi Rumah Sakit KARS, dengan layanan IGD 24 Jam, Stroke Terpadu, dan Klinik Neurologi.') }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach ($alternateLocales as $alternateLocale)
<meta property="og:locale:alternate" content="{{ $alternateLocale }}">
@endforeach
<link rel="icon" type="image/png" href="{{ Vite::asset('resources/images/web-icon.webp') }}">
<title>{{ $title ?? 'RS St. Elisabeth Semarang' }}</title>
