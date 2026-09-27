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
];
