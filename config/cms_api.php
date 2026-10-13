<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CMS API Connection Details
    |--------------------------------------------------------------------------
    | Konfigurasi ini digunakan oleh Consumer (Web) untuk menghubungi CMS.
    */
    'base_url' => env('CMS_API_BASE_URL', 'http://127.0.0.1:2302/api/v1/cms'),

    /*
    |--------------------------------------------------------------------------
    | CMS API Credentials (HMAC)
    |--------------------------------------------------------------------------
    | Kredensial untuk melakukan otentikasi HMAC saat melakukan request ke CMS.
    */
    'cons_id' => env('CMS_API_CONSID_WEB', ''),
    'secret_key' => env('CMS_API_SECRET_WEB', ''),
    
    /*
    |--------------------------------------------------------------------------
    | CMS API Timeout
    |--------------------------------------------------------------------------
    */
    'timeout' => 10,

    /*
    |--------------------------------------------------------------------------
    | CMS API Cache TTL (detik)
    |--------------------------------------------------------------------------
    | Lama penyimpanan respons API CMS di dalam cache, dikelompokkan per modul.
    | Menggunakan pola fallback env() dengan nilai default 600 detik (10 menit).
    | Memberikan fleksibilitas tuning antar-environment (misal: testing lokal)
    | tanpa mengubah kode, sekaligus menjamin keamanan nilai default di production.
    */
    'cache_ttl' => [
        'default'           => (int) env('CMS_CACHE_TTL_DEFAULT', 600),
        'news'              => (int) env('CMS_CACHE_TTL_NEWS', 600),
        'articles'          => (int) env('CMS_CACHE_TTL_ARTICLES', 600),
        'promotions'        => (int) env('CMS_CACHE_TTL_PROMOTIONS', 600),
        'facilities'        => (int) env('CMS_CACHE_TTL_FACILITIES', 600),
        'room_facilities'   => (int) env('CMS_CACHE_TTL_ROOM_FACILITIES', 600),
        'banner_promotions' => (int) env('CMS_CACHE_TTL_BANNER_PROMOTIONS', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | CMS Webhook Cache Invalidation Configuration
    |--------------------------------------------------------------------------
    | Konfigurasi keamanan untuk endpoint internal webhook cache invalidation.
    | CMS mengirim request dengan header X-Cons-ID, X-Timestamp, dan X-Signature.
    */
    'webhook' => [
        'enabled'           => filter_var(env('CMS_WEBHOOK_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'cons_id'           => env('CMS_WEBHOOK_CONSID', ''),
        'secret_key'        => env('CMS_WEBHOOK_SECRET', ''),
        'tolerance_seconds' => (int) env('CMS_WEBHOOK_TOLERANCE', 300), // 5 menit
    ],
];
