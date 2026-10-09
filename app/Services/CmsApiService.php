<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CmsApiService
{
    protected string $baseUrl;
    protected string $consId;
    protected string $secretKey;
    protected int $timeout;

    public function __construct()
    {
        // Membaca dari config/cms_api.php yang telah kita rombak di Tahap 4
        // Menambahkan type casting untuk menghindari warning Intelephense/PHPStan
        $this->baseUrl = (string) config('cms_api.base_url', '');
        $this->consId = (string) config('cms_api.cons_id', '');
        $this->secretKey = (string) config('cms_api.secret_key', '');
        $this->timeout = (int) config('cms_api.timeout', 10);
    }

    /**
     * Membuat instance HTTP Request bawaan Laravel (PendingRequest) 
     * yang sudah di-inject dengan header HMAC secara otomatis.
     */
    protected function apiRequest(): \Illuminate\Http\Client\PendingRequest
    {   
        date_default_timezone_set('Asia/Jakarta');

        $consid = $this->consId;
        $secretKey = $this->secretKey;

        // Logika pembuatan X-Signature yang identik dengan DoctorApiService
        $tStamp = strval(time()); // Unix timestamp — strtotime('1970-01-01') selalu 0
        $signature = hash_hmac('sha256', $tStamp . $consid, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        return Http::withHeaders([
            'X-Cons-ID' => $consid,
            'X-Timestamp' => $tStamp,
            'X-Signature' => $encodedSignature,
            'Accept'  => 'application/json',
            'Accept-Language' => app()->getLocale(),
        ])->timeout($this->timeout);
    }

    /**
     * =========================================================================
     * ENDPOINT METHODS
     * Berikut adalah contoh method untuk menarik data dari Headless CMS.
     * =========================================================================
     */

    /**
     * Mengambil daftar berita (News).
     * Menerapkan Cache agar web frontend tidak membanjiri API CMS.
     */
    public function getNews(array $filters = []): array
    {
        $locale = app()->getLocale();
        // Parameter filters dan locale disertakan pada cache key agar spesifik
        $cacheKey = "cms_news_{$locale}_" . md5(serialize($filters));
        $ttl = 600; // TTL Cache: 10 menit

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                // Endpoint target: http://172.17.12.17:2302/api/v1/cms/news
                $response = $this->apiRequest()->get("{$this->baseUrl}/news", $filters);

                if ($response->successful()) {
                    // Tergantung pada format response API CMS, biasanya dibungkus key 'data'
                    return $response->json('data', []);
                }

                Log::warning('CMS API - Gagal mengambil daftar News', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [];

            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch News', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Mengambil single berita berdasarkan slug.
     */
    public function getNewsBySlug(string $slug): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_news_slug_{$locale}_{$slug}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($slug) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/news/{$slug}");

                if ($response->successful()) {
                    return $response->json('data', null);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch News Slug {$slug}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil daftar artikel (Articles).
     */
    public function getArticles(array $filters = []): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_articles_{$locale}_" . md5(serialize($filters));
        $ttl = 600; 

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/articles", $filters);

                if ($response->successful()) {
                    return $response->json('data', []);
                }

                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Articles', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Mengambil single artikel berdasarkan ID.
     */
    public function getArticleById(string $id): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_article_{$locale}_{$id}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($id) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/articles/{$id}");

                if ($response->successful()) {
                    return $response->json('data', null);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Article ID {$id}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil single artikel berdasarkan slug.
     * PERBAIKAN: Digunakan oleh ArticleController::show() agar tidak perlu
     * fetch semua artikel hanya untuk menemukan satu artikel.
     */
    public function getArticleBySlug(string $slug): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_article_slug_{$locale}_{$slug}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($slug) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/articles/{$slug}");

                if ($response->successful()) {
                    return $response->json('data', null);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Article Slug {$slug}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil daftar promosi (Promotions).
     */
    public function getPromotions(array $filters = []): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_promotions_{$locale}_" . md5(serialize($filters));
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/promotions", $filters);
                if ($response->successful()) return $response->json('data', []);
                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Promotions', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Mengambil single promosi berdasarkan ID.
     */
    public function getPromotionById(string $id): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_promotion_{$locale}_{$id}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($id) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/promotions/{$id}");
                if ($response->successful()) return $response->json('data', null);
                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Promotion ID {$id}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil daftar fasilitas rumah sakit (Facility Services).
     */
    public function getFacilities(array $filters = []): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_facilities_{$locale}_" . md5(serialize($filters));
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/facilities", $filters);
                if ($response->successful()) return $response->json('data', []);
                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Facilities', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Mengambil single fasilitas rumah sakit berdasarkan slug.
     */
    public function getFacilityBySlug(string $slug): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_facility_{$locale}_{$slug}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($slug) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/facilities/{$slug}");
                if ($response->successful()) return $response->json('data', null);
                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Facility Slug {$slug}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil daftar fasilitas ruangan (Room Facilities).
     */
    public function getRoomFacilities(array $filters = []): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_room_facilities_{$locale}_" . md5(serialize($filters));
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/room-facilities", $filters);
                if ($response->successful()) return $response->json('data', []);
                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Room Facilities', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Mengambil single fasilitas ruangan berdasarkan slug.
     */
    public function getRoomFacilityBySlug(string $slug): array|null
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_room_facility_{$locale}_{$slug}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () use ($slug) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/room-facilities/{$slug}");
                if ($response->successful()) return $response->json('data', null);
                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Room Facility Slug {$slug}", ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Mengambil daftar Banner Promotions (Carousel Utama).
     */
    public function getBannerPromotions(): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_banner_promotions_{$locale}";
        $ttl = 600;

        return Cache::remember($cacheKey, $ttl, function () {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/banner-promotions");
                if ($response->successful()) return $response->json('data', []);
                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Banner Promotions', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }
}
