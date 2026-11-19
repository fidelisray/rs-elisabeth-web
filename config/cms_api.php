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
    'timeout' => (int) env('CMS_API_TIMEOUT', 10),
    'upload_timeout' => (int) env('CMS_API_UPLOAD_TIMEOUT', 30),

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
        'default'                 => (int) env('CMS_CACHE_TTL_DEFAULT', 600),
        'news'                    => (int) env('CMS_CACHE_TTL_NEWS', 600),
        'articles'                => (int) env('CMS_CACHE_TTL_ARTICLES', 600),
        'promotions'              => (int) env('CMS_CACHE_TTL_PROMOTIONS', 600),
        'facilities'              => (int) env('CMS_CACHE_TTL_FACILITIES', 600),
        'room_facilities'         => (int) env('CMS_CACHE_TTL_ROOM_FACILITIES', 600),
        'banner_promotions'       => (int) env('CMS_CACHE_TTL_BANNER_PROMOTIONS', 600),
        'doctors'                 => (int) env('CMS_CACHE_TTL_DOCTORS', 600),
        'online_libraries'        => (int) env('CMS_CACHE_TTL_ONLINE_LIBRARIES', 600),
        'dynamic_forms'           => (int) env('CMS_CACHE_TTL_DYNAMIC_FORMS', 600),
        'dynamic_forms_quota_ttl' => (int) env('CMS_CACHE_TTL_DYNAMIC_FORMS_QUOTA', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional Legacy Form Slug Mapping
    |--------------------------------------------------------------------------
    | Jika diisi slug formulir dinamis CMS, rute lama (/kritik-dan-saran dan
    | /kuesioner/*) mendelegasikan render & submit ke DynamicFormController.
    */
    'legacy_form_slugs' => [
        'feedback'    => env('CMS_LEGACY_SLUG_FEEDBACK') ?: null,
        'igd'         => env('CMS_LEGACY_SLUG_IGD') ?: null,
        'rawat-jalan' => env('CMS_LEGACY_SLUG_RAWAT_JALAN') ?: null,
        'rawat-inap'  => env('CMS_LEGACY_SLUG_RAWAT_INAP') ?: null,
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
