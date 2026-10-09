<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HospitalApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;

    // protected const TOKEN_CACHE_KEY = null;
    protected const TOKEN_CACHE_KEY = 'hospital_api_token';

    public function __construct()
    {
        $this->baseUrl = config('rsapi.base_url') ?? '';
        $this->apiKey  = config('rsapi.api_key') ?? '';
        $this->timeout = (int) config('rsapi.timeout', 5);
    }

    /**
     * Ambil token yang valid.
     * Jika token di cache masih ada → pakai itu.
     * Jika tidak ada / expired → generate baru dari API.
     */
    protected function getValidToken(): string
    {
        // Coba ambil dari cache dulu
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cachedToken != null) {
            return $cachedToken;
        } else {
            // Tidak ada di cache → generate baru
            return $this->generateNewToken();
        }

    }

    /**
     * Hit endpoint auth untuk mendapatkan token baru,
     * lalu simpan ke cache.
     */
    protected function generateNewToken(): string
    {
        $authEndpoint = config('rsapi.auth.endpoint');
        $buffer       = config('rsapi.token_ttl_buffer', 60);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post($this->baseUrl . $authEndpoint, [
                'username' => config('rsapi.auth.username'),
                'password' => config('rsapi.auth.password'),
            ]);

            // cek token yang berhasil di-generate
            // dd($response->json());

            if ($response->successful()) {
                $data = $response->json();

                
                // Sesuaikan key ini dengan response auth API kamu
                // Setelah dd($response->json()) kamu tahu struktur pastinya
                // $token     = $data['token']      ?? $data['access_token'] ?? null;
                $token = $data['X-Token'] ?? null;
                
                // dd($token);
                
                if (!$token) {
                    Log::error('X-Token tidak ditemukan di response', [
                        'response' => $data
                    ]);
                    return $this->apiKey;
                }
                        
                // if (!$token) {
                //     Log::error('Token tidak ditemukan di response auth', [
                //         'response' => $data
                //     ]);
                //     // Fallback ke api_key statis dari .env
                //     return $this->apiKey;
                // }
                                
                // Simpan ke cache dengan TTL dikurangi buffer
                // Contoh: expires_in = 300 detik, buffer = 60 detik
                // Maka cache selama 240 detik → token pasti masih valid saat dipakai
                $expiresIn = 300; // default 5 menit
                $cacheTtl = max(1, $expiresIn - $buffer);

                Cache::put(self::TOKEN_CACHE_KEY, $token, $cacheTtl);

                Log::info('Token baru berhasil di-generate', [
                    'expires_in' => $expiresIn,
                    'cached_for' => $cacheTtl . ' detik',
                ]);

                return $token;
            }

            Log::error('Gagal generate token baru', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            // Fallback ke api_key statis
            return $this->apiKey;

        } catch (\Exception $e) {
            Log::error('Exception saat generate token', [
                'error' => $e->getMessage()
            ]);

            return $this->apiKey;
        }
    }

    /**
     * Paksa invalidate token di cache dan generate baru.
     * Dipanggil ketika response API mengembalikan 401 Unauthorized.
     */
    protected function refreshToken(): string
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
        return $this->generateNewToken();
    }

    // =========================================================
    // HTTP CLIENT
    // =========================================================

    protected function apiRequest(): \Illuminate\Http\Client\PendingRequest
    {   
        $token = $this->getValidToken();

        // dd($token);

        return Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $this->apiKey,
            'X-Token' => $token,
            'Accept'  => 'application/json',
            'Accept-Language' => app()->getLocale(),
        ])->timeout($this->timeout);
    }

    /**
     * Wrapper request dengan auto-retry jika token expired (401).
     * Mencoba maksimal 2 kali:
     *   Percobaan 1 → pakai token dari cache
     *   Percobaan 2 → generate token baru lalu coba lagi
     */
    protected function requestWithRetry(string $method, string $endpoint, array $params = []): array
    {
        $maxRetry = 2;

        for ($attempt = 1; $attempt <= $maxRetry; $attempt++) {
            try {
                $request = $this->apiRequest();

                $response = match(strtoupper($method)) {
                    'GET'  => $request->get($this->baseUrl . $endpoint, $params),
                    'POST' => $request->post($this->baseUrl . $endpoint, $params),
                    default => $request->get($this->baseUrl . $endpoint, $params),
                };

                // dd($response);
                // Sukses → kembalikan data
                if ($response->successful()) {
                    return $response->json() ?? [];
                }

                // 401 Unauthorized → token expired, refresh dan coba lagi
                if ($response->status() === 401 && $attempt < $maxRetry) {
                    Log::warning("Token expired (401), mencoba refresh token... [attempt {$attempt}]");
                    $this->refreshToken();
                    continue; // lanjut ke iterasi berikutnya (attempt 2)
                }

                // Error lain (404, 500, dll)
                Log::warning('API request gagal', [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'attempt'  => $attempt,
                ]);

                return [];

            } catch (\Exception $e) {
                Log::error('Exception pada API request', [
                    'endpoint' => $endpoint,
                    'attempt'  => $attempt,
                    'error'    => $e->getMessage(),
                ]);

                if ($attempt >= $maxRetry) {
                    return [];
                }
            }
        }

        return [];
    }

    /**
     * Ambil daftar promoitions, 
     * setup cache 
     */
    public function getPromotionsList(string $category = ''): array
    {
        /*
        // ----- MASTER API LAMA (JANGAN DIHAPUS, UNCOMMENT JIKA INGIN KEMBALI) -----
        $cacheKey = 'promotions_' . md5(serialize($category));
        $ttl      = config('rsapi.cache_ttl.promotions');

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () use ($category) {
            try {
                $response = $this->apiRequest()
                    ->withBody(json_encode([
                        "category" => $category
                    ]), 'application/json')
                    ->get("{$this->baseUrl}/ads");

                // Dump struktur response, lalu stop eksekusi
                // dd($response->json());

                if ($response->successful()) {
                    return $response->json('Data', []);
                }

                Log::warning('API promotions gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return [];
            }
        });
        */

        // ----- LOCAL CMS API BARU -----
        $cacheKey = "local_cms_promotions_";
        $ttl      = config('rsapi.cache_ttl.promotions', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/promotions'));

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data promotions dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }
    
    public function getGlosarium(array $filters = []): array
    {
        $cacheKey = 'glosarium_';
        $ttl      = config('rsapi.cache_ttl.glosarium');

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () use ($filters) {
            $response = $this->requestWithRetry('GET', '/glossarium');

            $data = $response['Data'] ?? [];

            usort($data, fn($a, $b) => strcasecmp($a['istilah'], $b['istilah']));

            return $data ?? [];
        });
    }

    /**
     * Paksa refresh cache kamus medis (dipanggil manual / via Artisan).
     */
    public function refreshGlossaryCache(): array
    {
        // 1. Coba ambil data baru langsung dari API (bypass cache)
        try {
            $response = $this->requestWithRetry('GET', '/glossarium');
            $data = $response['Data'] ?? [];

            // 2. Jika data berhasil ditarik dan TIDAK kosong, timpa cache lama
            if (!empty($data)) {
                usort($data, fn($a, $b) => strcasecmp($a['istilah'], $b['istilah']));
                
                $ttl = config('rsapi.cache_ttl.glosarium');
                Cache::put('glosarium_' . app()->getLocale(), $data, $ttl);
                
                Log::info('Glossary cache refreshed successfully with fresh API data.');
                return $data;
            }
        } catch (\Exception $e) {
            Log::error('Failed to connect to API during glossary cache refresh: ' . $e->getMessage());
        }

        // 3. Jika API gagal / kosong, jangan hapus cache lama!
        Log::warning('Failed to refresh glossary cache: API offline or returned empty. Keeping old cache.');
        
        // Kembalikan cache lama yang masih ada agar command tidak error
        return Cache::get('glosarium_' . app()->getLocale()) ?? [];
    }


    public function getCachedGlosarium(): array
    {
        return Cache::get('glosarium_' . app()->getLocale(), []);
    }

    /**
     * Filter glossary berdasarkan huruf awal.
     * Proses di PHP, tidak perlu request API lagi.
     */
    public function getGlossaryByLetter(string $letter): array
    {
        $all = $this->getCachedGlosarium();

        if ($letter === 'ALL') {
            return $all;
        }

        return array_values(
            array_filter($all, fn($item) =>
                strtoupper(substr($item['istilah'], 0, 1)) === strtoupper($letter)
            )
        );
    }

    /**
     * Ambil glossary dikelompokkan berdasarkan 2 huruf pertama.
     * Memanfaatkan getGlossaryByLetter() yang sudah ada.
     */
    public function getGlossaryGrouped(string $letter = 'ALL'): array
    {
        $items   = $this->getGlossaryByLetter($letter); // pakai method lama
        $grouped = [];

        foreach ($items as $item) {
            $prefix            = strtoupper(substr($item['istilah'], 0, 2));
            $grouped[$prefix][] = $item;
        }

        ksort($grouped); // urutkan prefix-nya (Ba, Bi, Bu, dst)
        return $grouped;
    }

    /**
     * Tentukan file JSON kamus medis berdasarkan locale aktif.
     * Mendukung internasionalisasi (en / id) dengan fallback ke 'en'.
     */
    protected function getLocalGlossaryJsonPath(): string
    {
        $locale = app()->getLocale();

        // Pastikan hanya locale yang kita dukung yang dipetakan ke file yang benar.
        $supported = ['en', 'id'];

        if (!in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        return storage_path("app/json/glossarium/{$locale}_medical_glossary.json");
    }

    /**
     * Ambil seluruh data kamus medis dari file JSON lokal.
     * Data dinormalisasi ke struktur: slug, istilah, deskripsi, source, category.
     *
     * Cache per-locale menggunakan mekanisme Cache (TTL dari config cache_ttl.glosarium).
     * Cache hanya dianggap valid bila file JSON-nya tidak berubah (mtime disertakan
     * di dalam key cache) sehingga data selalu sinkron saat file diperbarui.
     */
    public function getLocalGlossary(): array
    {
        $path = $this->getLocalGlossaryJsonPath();

        if (!file_exists($path)) {
            Log::error('File JSON kamus medis lokal tidak ditemukan', ['path' => $path]);
            return [];
        }

        $locale = app()->getLocale();
        $mtime  = filemtime($path);
        $ttl    = (int) config('rsapi.cache_ttl.glosarium', 86400);

        $cacheKey = "local_glossary_{$locale}_{$mtime}";

        return Cache::remember($cacheKey, $ttl, function () use ($path) {
            $raw = file_get_contents($path);

            if ($raw === false) {
                Log::error('Gagal membaca file JSON kamus medis lokal', ['path' => $path]);
                return [];
            }

            $decoded = json_decode($raw, true);

            if (!is_array($decoded) || !isset($decoded['data']) || !is_array($decoded['data'])) {
                Log::error('Struktur file JSON kamus medis lokal tidak valid', ['path' => $path]);
                return [];
            }

            return array_values(array_filter(array_map(
                function ($entry) {
                    if (!is_array($entry) || empty($entry['name'])) {
                        return null;
                    }

                    return [
                        'slug'       => $entry['name'],
                        'istilah'    => ucwords(str_replace('-', ' ', (string) $entry['name'])),
                        'deskripsi'  => $entry['description'] ?? '',
                        'source'     => $entry['source'] ?? '',
                        'category'   => strtoupper((string) ($entry['category'] ?? substr((string) $entry['name'], 0, 1))),
                    ];
                },
                $decoded['data']
            )));
        });
    }

    /**
     * Ambil kamus medis lokal yang sudah dikelompokkan berdasarkan 2 huruf awal.
     * Struktur hasil identik dengan getGlossaryGrouped() sehingga dapat dipakai
     * langsung oleh view tanpa perubahan.
     */
    public function getLocalGlossaryGrouped(string $letter = 'ALL'): array
    {
        $items = $this->getLocalGlossary();

        if ($letter !== 'ALL') {
            $items = array_values(array_filter(
                $items,
                fn($item) => strtoupper(substr($item['istilah'], 0, 1)) === strtoupper($letter)
            ));
        }

        $grouped = [];

        foreach ($items as $item) {
            $prefix            = strtoupper(substr($item['istilah'], 0, 2));
            $grouped[$prefix][] = $item;
        }

        ksort($grouped);

        return $grouped;
    }


    /**
     * Ambil Data Artikel Kesehatan (Kategori: artikel)
     */
    public function getArticles(): array
    {
        /*
        // ----- MASTER API LAMA (JANGAN DIHAPUS, UNCOMMENT JIKA INGIN KEMBALI) -----
        $cacheKey = "articles_";
        $ttl      = config('rsapi.cache_ttl.articles');

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = $this->apiRequest()
                    ->withBody(json_encode([
                        "category" => "artikel"
                    ]), 'application/json')
                    ->get("{$this->baseUrl}/articles");

                return $response->successful()
                    ? $response->json('Data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data artikel', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
        */

        // ----- LOCAL CMS API BARU -----
        $cacheKey = "local_cms_articles_";
        $ttl      = config('rsapi.cache_ttl.articles', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                // Fetch dari lokal API CMS yang baru kita buat
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/articles'));

                // dd($response->json());

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data artikel dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    /**
     * Ambil Data Berita / ElisaNews (Kategori: berita)
     */
    public function getNews(): array
    {
        /*
        // ----- MASTER API LAMA (JANGAN DIHAPUS, UNCOMMENT JIKA INGIN KEMBALI) -----
        $cacheKey = "elisanews_";
        $ttl      = config('rsapi.cache_ttl.elisanews');

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = $this->apiRequest()
                    ->withBody(json_encode([
                        "category" => "berita"
                    ]), 'application/json')
                    ->get("{$this->baseUrl}/articles");

                return $response->successful()
                    ? $response->json('Data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data berita', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
        */

        // ----- LOCAL CMS API BARU -----
        $cacheKey = "local_cms_news_";
        $ttl      = config('rsapi.cache_ttl.elisanews', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/news'));

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data berita dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    /**
     * Ambil Data Ruang Perawatan dari Local CMS API.
     * Menggunakan pola yang sama dengan getArticles().
     */
    public function getRoomFacilities(): array
    {
        $cacheKey = "local_cms_room_facilities_";
        $ttl      = config('rsapi.cache_ttl.room_facilities', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/room-facilities'));

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data room facilities dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    /**
     * Ambil Data Banner Promotions (Carousel Halaman Utama) dari Local CMS API.
     * Hanya mengambil banner yang is_active = true, diurutkan berdasarkan sort_order.
     */
    public function getBannerPromotions(): array
    {
        $cacheKey = 'local_cms_banner_promotions_';
        $ttl      = config('rsapi.cache_ttl.banner_promotions', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/banner-promotions'));

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data banner promotions dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    /**
     * Ambil Data Facility Services dari Local CMS API.
     */
    public function getFacilityServices(): array
    {
        $cacheKey = 'local_cms_facility_services_';
        $ttl      = config('rsapi.cache_ttl.facility_services', 60);

        return Cache::remember($cacheKey . '_' . app()->getLocale(), $ttl, function () {
            try {
                $response = Http::withHeaders(['Accept-Language' => app()->getLocale()])->get(url('/api/cms/facilities'));

                return $response->successful()
                    ? $response->json('data', [])
                    : [];
            } catch (\Exception $e) {
                Log::error('Gagal ambil data facility services dari lokal CMS API', [
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }
}