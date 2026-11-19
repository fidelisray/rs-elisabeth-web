<?php

namespace App\Services;

use App\Exceptions\CmsApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
    protected function apiRequest(string $rawBody = ''): PendingRequest
    {
        $consid = $this->consId;
        $secretKey = $this->secretKey;

        // Signature menyertakan body mentah untuk POST. Body yang ditandatangani harus
        // identik byte per byte dengan body yang dikirim, sehingga $rawBody dikirim
        // apa adanya oleh pemanggil (lihat postSigned). GET memakai $rawBody kosong.
        $tStamp = strval(time());
        $signature = hash_hmac('sha256', $tStamp.$consid.$rawBody, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        return Http::withHeaders([
            'X-Cons-ID' => $consid,
            'X-Timestamp' => $tStamp,
            'X-Signature' => $encodedSignature,
            'Accept' => 'application/json',
            'Accept-Language' => app()->getLocale(),
        ])->timeout($this->timeout);
    }

    /**
     * Kirim POST bertanda tangan HMAC yang mencakup body.
     * Body di-encode sekali, lalu string itu yang ditandatangani dan dikirim.
     */
    protected function postSigned(string $url, array $payload): Response
    {
        $rawBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return $this->apiRequest($rawBody)
            ->withBody($rawBody, 'application/json')
            ->post($url);
    }

    /**
     * Kirim multipart upload bertanda tangan HMAC dengan SHA-256 isi berkas
     * dan mempertahankan nama berkas asli untuk validasi ekstensi di CMS.
     */
    protected function postMultipartSigned(string $url, UploadedFile $file, array $fields = []): Response
    {
        $realPath = $file->getRealPath() ?: $file->getPathname();
        if (! is_string($realPath) || $realPath === '' || ! is_readable($realPath)) {
            throw new \RuntimeException('Uploaded file cannot be read for SHA-256 hashing.');
        }

        $sha256 = hash_file('sha256', $realPath);
        if (! is_string($sha256) || $sha256 === '') {
            throw new \RuntimeException('Failed to compute SHA-256 hash of uploaded file.');
        }

        $tStamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $tStamp.$this->consId.$sha256, $this->secretKey, true));
        $uploadTimeout = max(5, (int) config('cms_api.upload_timeout', 30));

        $stream = fopen($realPath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException('Failed to open uploaded file stream.');
        }

        $originalName = str_replace('\\', '/', (string) $file->getClientOriginalName());
        $safeName = basename($originalName);
        if ($safeName === '') {
            $safeName = 'upload.bin';
        }

        try {
            return Http::withHeaders([
                'X-Cons-ID' => $this->consId,
                'X-Timestamp' => $tStamp,
                'X-Signature' => $signature,
                'X-Content-SHA256' => $sha256,
                'Accept' => 'application/json',
                'Accept-Language' => app()->getLocale(),
            ])
                ->timeout($uploadTimeout)
                ->attach('file', $stream, $safeName)
                ->post($url, $fields);
        } catch (\Throwable $e) {
            if (is_resource($stream)) {
                fclose($stream);
            }
            throw $e;
        }
    }

    /**
     * Ambil nilai TTL cache untuk sebuah modul dari config/cms_api.php.
     * Fallback ke 600 detik bila kunci belum terdaftar agar perilaku tetap aman.
     */
    protected function cacheTtl(string $module): int
    {
        return (int) config("cms_api.cache_ttl.{$module}", 600);
    }

    /**
     * Hash identitas endpoint & credential CMS (16 karakter hex)
     * untuk isolasi multi-tenant/environment tanpa membuat cache key terlalu panjang.
     */
    protected function scopeKey(): string
    {
        return substr(hash('sha256', $this->baseUrl.'|'.$this->consId), 0, 16);
    }

    /**
     * Menyimpan respon ke cache dengan dukungan Cache Tags (Redis).
     * Jika store cache tidak mendukung tags (misal di local development),
     * method ini secara aman fallback ke Cache::remember() standar.
     */
    protected function rememberTagged(string $module, string $key, int $ttl, \Closure $callback)
    {
        if (Cache::supportsTags()) {
            $scopedKey = $this->scopeKey().':'.$key;

            return Cache::tags(['cms', "cms_{$module}"])->remember($scopedKey, $ttl, $callback);
        }

        return Cache::remember($this->fallbackVersionedKey($module, $key), $ttl, $callback);
    }

    protected function fallbackVersionedKey(string $module, string $key): string
    {
        $version = $this->moduleVersion($module);
        $fallbackKey = "cms_gen:{$this->scopeKey()}:{$module}:{$version}:{$key}";

        // Pengaman: hash bagian key spesifik agar total panjang tetap di bawah batas 255 VARCHAR database.
        if (strlen($fallbackKey) > 150) {
            $fallbackKey = "cms_gen:{$this->scopeKey()}:{$module}:{$version}:".md5($key);
        }

        return $fallbackKey;
    }

    protected function getTaggedCache(string $module, string $key): mixed
    {
        if (Cache::supportsTags()) {
            return Cache::tags(['cms', "cms_{$module}"])->get($this->scopeKey().':'.$key);
        }

        return Cache::get($this->fallbackVersionedKey($module, $key));
    }

    protected function putTaggedCache(string $module, string $key, mixed $value, int $ttl): void
    {
        if (Cache::supportsTags()) {
            Cache::tags(['cms', "cms_{$module}"])->put($this->scopeKey().':'.$key, $value, $ttl);

            return;
        }

        Cache::put($this->fallbackVersionedKey($module, $key), $value, $ttl);
    }

    protected function versionKey(string $module): string
    {
        return "cms_gen:{$this->scopeKey()}:{$module}";
    }

    protected function moduleVersion(string $module): string
    {
        return Cache::rememberForever($this->versionKey($module), fn () => (string) Str::uuid());
    }

    /**
     * Melakukan invalidasi cache untuk modul tertentu atau seluruh modul CMS.
     * Mengembalikan status dan daftar tag yang dibersihkan.
     */
    public function invalidateCache(string $module = 'all'): array
    {
        $supportedModules = [
            'news',
            'articles',
            'promotions',
            'facilities',
            'room_facilities',
            'banner_promotions',
            'doctors',
            'online_libraries',
            'dynamic_forms',
        ];

        if ($module !== 'all' && ! in_array($module, $supportedModules, true)) {
            return ['flushed' => null, 'tags' => [], 'status' => 'unknown_module'];
        }

        if (Cache::supportsTags()) {
            if ($module === 'all') {
                Cache::tags(['cms'])->flush();
                Log::info("CMS API Cache - Flushed all 'cms' tags.");

                return [
                    'flushed' => 'all',
                    'tags' => ['cms'],
                    'status' => 'success',
                ];
            }

            if (in_array($module, $supportedModules, true)) {
                $tag = "cms_{$module}";
                Cache::tags([$tag])->flush();
                Log::info("CMS API Cache - Flushed tag: {$tag}.");

                return [
                    'flushed' => $module,
                    'tags' => [$tag],
                    'status' => 'success',
                ];
            }

            return [
                'flushed' => null,
                'status' => 'unknown_module',
            ];
        }

        // Store database/file: ganti generasi seluruh varian bahasa/filter/detail.
        foreach ($module === 'all' ? $supportedModules : [$module] as $target) {
            Cache::forever($this->versionKey($target), (string) Str::uuid());
        }

        return [
            'flushed' => $module,
            'tags' => [],
            'status' => 'success',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getDoctors(array $filters = []): array
    {
        $locale = in_array(app()->getLocale(), ['id', 'en'], true) ? app()->getLocale() : 'id';
        $filters = array_intersect_key($filters, ['specialty' => true]);
        $filters['lang'] = $locale;
        $key = 'cms_doctors_'.$locale.'_'.hash('sha256', json_encode($filters, JSON_THROW_ON_ERROR));

        return $this->rememberTagged('doctors', $key, $this->cacheTtl('doctors'), function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/doctors", $filters);
                $rows = $response->json('data');
                if (! $response->successful() || ! is_array($rows)) {
                    throw new CmsApiException($response->status());
                }
                foreach ($rows as $row) {
                    if (! is_array($row) || ! is_string($row['paramedic_code'] ?? null) || ! is_string($row['name'] ?? null)) {
                        throw new CmsApiException(502);
                    }
                }

                return array_values($rows);
            } catch (CmsApiException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning('CMS doctor list unavailable', ['exception_type' => get_class($e)]);
                throw new CmsApiException;
            }
        });
    }

    /** NULL berarti 404, bukan kegagalan koneksi. Kegagalan tidak disimpan ke cache. */
    public function getDoctorByCode(string $code): ?array
    {
        $locale = in_array(app()->getLocale(), ['id', 'en'], true) ? app()->getLocale() : 'id';
        $key = 'cms_doctor_'.$locale.'_'.hash('sha256', $code);

        return $this->rememberTagged('doctors', $key, $this->cacheTtl('doctors'), function () use ($code, $locale) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/doctors/".rawurlencode($code), ['lang' => $locale]);
                if ($response->status() === 404) {
                    return null;
                }
                $row = $response->json('data');
                if (! $response->successful() || ! is_array($row) || ($row['paramedic_code'] ?? null) !== $code || ! is_string($row['name'] ?? null)) {
                    throw new CmsApiException($response->status());
                }

                return $row;
            } catch (CmsApiException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning('CMS doctor detail unavailable', ['exception_type' => get_class($e)]);
                throw new CmsApiException;
            }
        });
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
        $cacheKey = "cms_news_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('news');

        return $this->rememberTagged('news', $cacheKey, $ttl, function () use ($filters) {
            try {
                // Endpoint target: http://172.17.12.17:2302/api/v1/cms/news
                $response = $this->apiRequest()->get("{$this->baseUrl}/news", $filters);

                if ($response->successful()) {
                    // Tergantung pada format response API CMS, biasanya dibungkus key 'data'
                    return $response->json('data', []);
                }

                Log::warning('CMS API - Gagal mengambil daftar News', [
                    'status' => $response->status(),
                    'body' => $response->body(),
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
    public function getNewsBySlug(string $slug, ?string $localeOverride = null): ?array
    {
        $locale = $localeOverride ?? app()->getLocale();
        $cacheKey = "cms_news_slug_{$locale}_{$slug}";
        $ttl = $this->cacheTtl('news');

        return $this->rememberTagged('news', $cacheKey, $ttl, function () use ($slug, $locale) {
            try {
                $response = $this->apiRequest()
                    ->withHeaders(['Accept-Language' => $locale])
                    ->get("{$this->baseUrl}/news/{$slug}", ['lang' => $locale]);

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
        $cacheKey = "cms_articles_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('articles');

        return $this->rememberTagged('articles', $cacheKey, $ttl, function () use ($filters) {
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
    public function getArticleById(string $id): ?array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_article_{$locale}_{$id}";
        $ttl = $this->cacheTtl('articles');

        return $this->rememberTagged('articles', $cacheKey, $ttl, function () use ($id) {
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
    public function getArticleBySlug(string $slug, ?string $localeOverride = null): ?array
    {
        $locale = $localeOverride ?? app()->getLocale();
        $cacheKey = "cms_article_slug_{$locale}_{$slug}";
        $ttl = $this->cacheTtl('articles');

        return $this->rememberTagged('articles', $cacheKey, $ttl, function () use ($slug, $locale) {
            try {
                $response = $this->apiRequest()
                    ->withHeaders(['Accept-Language' => $locale])
                    ->get("{$this->baseUrl}/articles/{$slug}", ['lang' => $locale]);

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
        $cacheKey = "cms_promotions_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('promotions');

        return $this->rememberTagged('promotions', $cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/promotions", $filters);
                if ($response->successful()) {
                    return $response->json('data', []);
                }

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
    public function getPromotionById(string $id): ?array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_promotion_{$locale}_{$id}";
        $ttl = $this->cacheTtl('promotions');

        return $this->rememberTagged('promotions', $cacheKey, $ttl, function () use ($id) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/promotions/{$id}");
                if ($response->successful()) {
                    return $response->json('data', null);
                }

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
        $cacheKey = "cms_facilities_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('facilities');

        return $this->rememberTagged('facilities', $cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/facilities", $filters);
                if ($response->successful()) {
                    return $response->json('data', []);
                }

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
    public function getFacilityBySlug(string $slug): ?array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_facility_{$locale}_{$slug}";
        $ttl = $this->cacheTtl('facilities');

        return $this->rememberTagged('facilities', $cacheKey, $ttl, function () use ($slug) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/facilities/{$slug}");
                if ($response->successful()) {
                    return $response->json('data', null);
                }

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
        $cacheKey = "cms_room_facilities_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('room_facilities');

        return $this->rememberTagged('room_facilities', $cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/room-facilities", $filters);
                if ($response->successful()) {
                    return $response->json('data', []);
                }

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
    public function getRoomFacilityBySlug(string $slug, ?string $localeOverride = null): ?array
    {
        $locale = $localeOverride ?? app()->getLocale();
        $cacheKey = "cms_room_facility_{$locale}_{$slug}";
        $ttl = $this->cacheTtl('room_facilities');

        return $this->rememberTagged('room_facilities', $cacheKey, $ttl, function () use ($slug, $locale) {
            try {
                $response = $this->apiRequest()
                    ->withHeaders(['Accept-Language' => $locale])
                    ->get("{$this->baseUrl}/room-facilities/{$slug}", ['lang' => $locale]);
                if ($response->successful()) {
                    return $response->json('data', null);
                }

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
        $ttl = $this->cacheTtl('banner_promotions');

        return $this->rememberTagged('banner_promotions', $cacheKey, $ttl, function () {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/banner-promotions");
                if ($response->successful()) {
                    return $response->json('data', []);
                }

                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Banner Promotions', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Mengambil daftar dokumen Perpustakaan Online (Online Libraries).
     */
    public function getOnlineLibraries(array $filters = []): array
    {
        $locale = app()->getLocale();
        $cacheKey = "cms_online_libraries_{$locale}_".md5(serialize($filters));
        $ttl = $this->cacheTtl('online_libraries');

        return $this->rememberTagged('online_libraries', $cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/online-libraries", $filters);
                if ($response->successful()) {
                    return $response->json('data', []);
                }

                return [];
            } catch (\Exception $e) {
                Log::error('CMS API - Error saat fetch Online Libraries', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * Mengambil detail dokumen Perpustakaan Online berdasarkan slug.
     */
    public function getOnlineLibraryBySlug(string $slug, ?string $localeOverride = null): ?array
    {
        $locale = $localeOverride ?? app()->getLocale();
        $cacheKey = "cms_online_library_slug_{$locale}_{$slug}";
        $ttl = $this->cacheTtl('online_libraries');

        return $this->rememberTagged('online_libraries', $cacheKey, $ttl, function () use ($slug, $locale) {
            try {
                $response = $this->apiRequest()
                    ->withHeaders(['Accept-Language' => $locale])
                    ->get("{$this->baseUrl}/online-libraries/{$slug}", ['lang' => $locale]);

                if ($response->successful()) {
                    return $response->json('data', null);
                }

                return null;
            } catch (\Exception $e) {
                Log::error("CMS API - Error saat fetch Online Library Slug {$slug}", ['error' => $e->getMessage()]);

                return null;
            }
        });
    }

    /**
     * Mengirimkan data kritik, saran, atau keluhan pelanggan ke CMS API.
     * Menggunakan HTTP POST dengan HMAC signature otomatis.
     */
    public function submitFeedback(array $payload): array
    {
        return $this->submitPatientForm('feedbacks', $payload, true);
    }

    /**
     * Mengirimkan data kuesioner kepuasan pasien ke CMS API.
     * Menggunakan HTTP POST dengan HMAC signature otomatis.
     */
    public function submitQuestionnaire(string $type, array $payload): array
    {
        if (! in_array($type, ['igd', 'rawat-jalan', 'rawat-inap'], true)) {
            return ['status' => 'error', 'http_code' => 422, 'message' => __('Jenis kuesioner tidak dikenal.')];
        }

        return $this->submitPatientForm('questionnaires/'.$type, $payload);
    }

    /** Jangan retry POST otomatis: belum ada kontrak idempotency lintas aplikasi. */
    private function submitPatientForm(string $path, array $payload, bool $requiresTicket = false): array
    {
        try {
            $response = $this->postSigned("{$this->baseUrl}/{$path}", $payload);
            $ticket = $response->json('ticket_number') ?? $response->json('data.ticket_number');
            if ($response->successful() && $response->json('success') === true && (! $requiresTicket || (is_string($ticket) && trim($ticket) !== ''))) {
                return [
                    'status' => 'success',
                    'http_code' => $response->status(),
                    'ticket_number' => $ticket,
                    'message' => __('Data berhasil dikirimkan.'),
                ];
            }
            Log::warning('CMS patient submission was not confirmed', ['endpoint' => $path, 'http_code' => $response->status()]);
            $status = in_array($response->status(), [422, 429], true) ? $response->status() : 503;

            return [
                'status' => 'error',
                'http_code' => $status,
                'errors' => $status === 422 && is_array($response->json('errors')) ? $response->json('errors') : [],
                'retry_after' => $status === 429 ? max(1, min(3600, (int) $response->header('Retry-After', '60'))) : null,
                'message' => $status === 429 ? __('Terlalu banyak pengiriman. Silakan coba lagi nanti.')
                    : ($status === 422 ? __('Data belum dapat diterima. Periksa isian formulir Anda.') : __('Layanan formulir belum tersedia. Silakan coba lagi nanti.')),
            ];
        } catch (\Exception $e) {
            // Pesan exception/body HTTP bisa berisi data pasien. Catat jenis kegagalan saja.
            Log::warning('CMS patient submission unavailable', ['endpoint' => $path, 'exception_type' => get_class($e)]);

            return [
                'status' => 'exception',
                'http_code' => 503,
                'errors' => [],
                'message' => __('Layanan formulir belum tersedia. Silakan coba lagi nanti.'),
            ];
        }
    }

    /**
     * Mengambil daftar formulir dinamis publik yang aktif dan terpublikasi.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDynamicForms(?string $mode = null, ?string $lang = null): array
    {
        $validMode = in_array($mode, ['questionnaire', 'feedback'], true) ? $mode : null;
        $validLang = in_array($lang, ['id', 'en'], true) ? $lang : null;
        $query = array_filter([
            'mode' => $validMode,
            'lang' => $validLang,
        ], fn (?string $value): bool => $value !== null);

        $cacheKey = 'cms_dynamic_forms_'.($validLang ?? 'all').'_'.($validMode ?? 'all');
        $ttl = $this->cacheTtl('dynamic_forms');

        $rows = $this->rememberTagged('dynamic_forms', $cacheKey, $ttl, function () use ($query) {
            try {
                $response = $this->apiRequest()->get("{$this->baseUrl}/forms", $query);
                $data = $response->json('data');

                if (! $response->successful() || ! is_array($data)) {
                    throw new CmsApiException($response->status());
                }

                return array_values(array_filter($data, fn ($item): bool => is_array($item) && is_string($item['slug'] ?? null)));
            } catch (CmsApiException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning('CMS dynamic forms list unavailable', ['exception_type' => get_class($e)]);
                throw new CmsApiException;
            }
        });

        $now = now();

        return array_values(array_filter($rows, function (array $form) use ($now): bool {
            if (! ($form['is_public_listed'] ?? false)) {
                return false;
            }
            if (! empty($form['starts_at']) && $now->lt(Carbon::parse($form['starts_at']))) {
                return false;
            }
            if (! empty($form['ends_at']) && $now->gt(Carbon::parse($form['ends_at']))) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Mengambil skema lengkap formulir dinamis berdasarkan slug.
     * NULL berarti 404; kegagalan upstream melempar CmsApiException dan tidak di-cache.
     */
    public function getDynamicFormBySlug(string $slug): ?array
    {
        $cacheKey = 'cms_dynamic_form_'.hash('sha256', $slug);
        $cached = $this->getTaggedCache('dynamic_forms', $cacheKey);

        if (is_array($cached)) {
            return $this->applyLocalScheduleAvailability($cached);
        }

        try {
            $response = $this->apiRequest()->get("{$this->baseUrl}/forms/".rawurlencode($slug));

            if ($response->status() === 404) {
                return null;
            }

            $row = $response->json('data');
            if (! $response->successful() || ! is_array($row) || ! is_string($row['slug'] ?? null)) {
                throw new CmsApiException($response->status());
            }

            $ttl = ($row['max_responses'] ?? null) !== null
                ? (int) config('cms_api.cache_ttl.dynamic_forms_quota_ttl', 60)
                : $this->cacheTtl('dynamic_forms');

            $this->putTaggedCache('dynamic_forms', $cacheKey, $row, $ttl);

            return $this->applyLocalScheduleAvailability($row);
        } catch (CmsApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('CMS dynamic form detail unavailable', [
                'slug' => $slug,
                'exception_type' => get_class($e),
            ]);
            throw new CmsApiException;
        }
    }

    /**
     * Evaluasi ulang jadwal starts_at/ends_at secara lokal terhadap snapshot skema yang di-cache.
     *
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    protected function applyLocalScheduleAvailability(array $form): array
    {
        $status = (string) ($form['status'] ?? 'published');
        $hasPublishedVersion = ! empty($form['published_version']['version_id']);
        $remainingQuota = $form['remaining_quota'] ?? null;
        $maxResponses = $form['max_responses'] ?? null;
        $locale = ($form['locale'] ?? 'id') === 'en' ? 'en' : 'id';

        if ($status !== 'published' || ! $hasPublishedVersion) {
            $form['is_accepting_responses'] = false;
            $form['closed_reason'] ??= ($status === 'closed' ? 'closed' : 'not_published');

            return $form;
        }

        if ($maxResponses !== null && $remainingQuota !== null && (int) $remainingQuota <= 0) {
            $form['is_accepting_responses'] = false;
            $form['closed_reason'] = 'quota_full';
            $form['closed_message'] ??= $locale === 'en'
                ? 'The maximum response quota for this form has been reached.'
                : 'Kuota pengisian formulir ini sudah penuh.';

            return $form;
        }

        $now = now();
        $startsAt = ! empty($form['starts_at']) ? Carbon::parse($form['starts_at']) : null;
        $endsAt = ! empty($form['ends_at']) ? Carbon::parse($form['ends_at']) : null;
        $defaultNotStarted = $locale === 'en'
            ? 'This form is not open for responses yet.'
            : 'Formulir ini belum dibuka untuk pengisian.';
        $defaultEnded = $locale === 'en'
            ? 'The submission period for this form has ended.'
            : 'Periode pengisian formulir ini telah berakhir.';

        if ($startsAt && $now->lt($startsAt)) {
            $form['is_accepting_responses'] = false;
            $form['closed_reason'] = 'not_started';
            $form['closed_message'] ??= $defaultNotStarted;

            return $form;
        }

        if ($endsAt && $now->gt($endsAt)) {
            $wasNotStarted = ($form['closed_reason'] ?? null) === 'not_started';
            $form['is_accepting_responses'] = false;
            $form['closed_reason'] = 'ended';
            if ($wasNotStarted && ($form['closed_message'] ?? null) === $defaultNotStarted) {
                $form['closed_message'] = $defaultEnded;
            } else {
                $form['closed_message'] ??= $defaultEnded;
            }

            return $form;
        }

        if (in_array($form['closed_reason'] ?? null, ['not_started', 'ended', null], true)) {
            $form['is_accepting_responses'] = true;
            $form['closed_reason'] = null;
            $form['closed_message'] = null;
        }

        return $form;
    }

    /**
     * Unggah berkas lampiran tahap-1 ke CMS untuk mendapatkan upload_token sementara.
     */
    public function uploadDynamicFormFile(string $slug, string $fieldKey, UploadedFile $file): array
    {
        try {
            $response = $this->postMultipartSigned(
                "{$this->baseUrl}/forms/".rawurlencode($slug).'/uploads',
                $file,
                ['field_key' => $fieldKey]
            );

            $data = $response->json('data');
            if ($response->successful() && $response->json('success') === true && is_array($data) && is_string($data['upload_token'] ?? null) && $data['upload_token'] !== '') {
                return [
                    'status' => 'success',
                    'http_code' => $response->status(),
                    'data' => [
                        'upload_token' => (string) $data['upload_token'],
                        'original_name' => (string) ($data['original_name'] ?? $file->getClientOriginalName()),
                        'size_bytes' => (int) ($data['size_bytes'] ?? $file->getSize() ?? 0),
                        'mime_type' => (string) ($data['mime_type'] ?? ''),
                        'expires_at' => $data['expires_at'] ?? null,
                    ],
                    'message' => (string) ($response->json('message') ?? __('Berkas berhasil diunggah.')),
                ];
            }

            Log::warning('CMS dynamic form upload was not confirmed', [
                'slug' => $slug,
                'http_code' => $response->status(),
            ]);

            $status = in_array($response->status(), [404, 422, 429], true) ? $response->status() : 503;

            return [
                'status' => 'error',
                'http_code' => $status,
                'errors' => $status === 422 && is_array($response->json('errors')) ? $response->json('errors') : [],
                'retry_after' => $status === 429 ? max(1, min(3600, (int) $response->header('Retry-After', '60'))) : null,
                'message' => match ($status) {
                    404 => __('Formulir tidak ditemukan.'),
                    429 => __('Terlalu banyak unggahan. Silakan coba lagi nanti.'),
                    422 => (string) ($response->json('message') ?: __('Berkas lampiran tidak memenuhi ketentuan formulir.')),
                    default => __('Layanan unggah berkas belum tersedia. Silakan coba lagi nanti.'),
                },
            ];
        } catch (\Exception $e) {
            Log::warning('CMS dynamic form upload unavailable', [
                'slug' => $slug,
                'exception_type' => get_class($e),
            ]);

            return [
                'status' => 'exception',
                'http_code' => 503,
                'errors' => [],
                'message' => __('Layanan unggah berkas belum tersedia. Silakan coba lagi nanti.'),
            ];
        }
    }

    /**
     * Kirim jawaban final formulir dinamis ke CMS (tanpa mencatat data pribadi ke log).
     */
    public function submitDynamicForm(string $slug, array $payload): array
    {
        if (array_key_exists('answers', $payload) && $payload['answers'] === []) {
            $payload['answers'] = (object) [];
        }

        try {
            $response = $this->postSigned("{$this->baseUrl}/forms/".rawurlencode($slug).'/submissions', $payload);
            $data = $response->json('data');

            if ($response->successful() && $response->json('success') === true && is_array($data)) {
                $confirmation = (string) ($data['confirmation_message'] ?? $response->json('message') ?? __('Jawaban Anda berhasil dikirim.'));

                return [
                    'status' => 'success',
                    'http_code' => $response->status(),
                    'data' => [
                        'submission_id' => $data['submission_id'] ?? null,
                        'ticket_number' => $data['ticket_number'] ?? null,
                        'confirmation_message' => $confirmation,
                        'submitted_at' => $data['submitted_at'] ?? null,
                    ],
                    'message' => $confirmation,
                ];
            }

            Log::warning('CMS dynamic form submission was not confirmed', [
                'slug' => $slug,
                'http_code' => $response->status(),
            ]);

            $status = in_array($response->status(), [404, 422, 429], true) ? $response->status() : 503;

            return [
                'status' => 'error',
                'http_code' => $status,
                'errors' => $status === 422 && is_array($response->json('errors')) ? $response->json('errors') : [],
                'retry_after' => $status === 429 ? max(1, min(3600, (int) $response->header('Retry-After', '60'))) : null,
                'message' => match ($status) {
                    404 => __('Formulir tidak ditemukan.'),
                    429 => __('Terlalu banyak pengiriman. Silakan coba lagi nanti.'),
                    422 => (string) ($response->json('message') ?: __('Data belum dapat diterima. Periksa isian formulir Anda.')),
                    default => __('Layanan formulir belum tersedia. Silakan coba lagi nanti.'),
                },
            ];
        } catch (\Exception $e) {
            Log::warning('CMS dynamic form submission unavailable', [
                'slug' => $slug,
                'exception_type' => get_class($e),
            ]);

            return [
                'status' => 'exception',
                'http_code' => 503,
                'errors' => [],
                'message' => __('Layanan formulir belum tersedia. Silakan coba lagi nanti.'),
            ];
        }
    }
}
