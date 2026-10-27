<?php

return [
    'enabled' => (bool) env('RS_API_ENABLED', true),
    'base_url' => env('RS_API_BASE_URL'),
    'api_key' => env('RS_API_KEY'),
    'timeout' => env('RS_API_TIMEOUT'),
    'medin_endpoint' => env('RS_MEDIN_ENDPOINT'),
    'medin_consid' => env('RS_MEDIN_API_CONSID'),
    'medin_secretkey' => env('RS_MEDIN_API_SECRETKEY'),

    // Daftar kode spesialisasi yang di-blacklist (bermasalah atau tidak aktif di API MedInfras)
    'blacklist_specialties' => array_filter(
        array_map('trim', explode(',', (string) env('RS_API_BLACKLIST_SPECIALTIES', 'KSM 37')))
    ),

    // Jeda delay (microsecond) per request spesialisasi saat warm-up (150000 = 150ms)
    'fetch_delay_microseconds' => (int) env('RS_API_FETCH_DELAY_MICROSECONDS', 150000),

    // Kredensial untuk generate token
    'auth' => [
        'endpoint' => env('RS_API_AUTH_ENDPOINT'),
        'username' => env('RS_API_USERNAME'),
        'password' => env('RS_API_PASSWORD'),
    ],

    'token_ttl_buffer' => env('RS_API_TOKEN_BUFFER', 60),

    'cache_ttl' => [
        // External RS API
        'doctors_fresh'        => (int) env('RS_API_CACHE_FRESH', 3600),   // 1 jam fresh window
        'doctors_stale'        => (int) env('RS_API_CACHE_STALE', 86400),  // 24 jam stale window toleransi
        'dokter'               => 3600,
        'units'                => 3600,
        'specialty'            => 3600,
        'dokter_by_speciality' => 3600,
        'staff'                => 3600,
        'jadwal'               => 1800,
        'klinik'               => 1800,
        'promotions'           => 1800,
        'glosarium'            => 86400,
        'articles'             => 1800,
        'elisanews'            => 1800,
    ],
];
