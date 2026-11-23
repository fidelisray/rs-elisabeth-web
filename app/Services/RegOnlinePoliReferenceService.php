<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegOnlinePoliReferenceService
{
    public const CACHE_KEY = 'regonline_active_poli_codes';
    public const FALLBACK_CACHE_KEY = 'regonline_active_poli_codes_fallback';
    public const LIST_CACHE_KEY = 'regonline_poli_references';
    public const LIST_FALLBACK_CACHE_KEY = 'regonline_poli_references_fallback';

    /**
     * Mencegah percobaan koneksi SQL Server berulang dalam satu request
     * ketika koneksi sedang gagal dan cache fallback kosong.
     */
    protected bool $failedFetchInRequest = false;

    /**
     * Membaca daftar lengkap referensi poliklinik secara READ-ONLY
     * dari tabel dbo.ref_poli_online pada koneksi SQL Server sekunder.
     *
     * @return array<int, array{code: string, name: string, is_active: bool}>|null
     */
    public function getPoliReferences(bool $forceRefresh = false): ?array
    {
        $ttl = max((int) config('services.regonline.poli_cache_ttl', 300), 30);

        if ($forceRefresh) {
            $this->failedFetchInRequest = false;
        } else {
            $cached = Cache::get(self::LIST_CACHE_KEY);
            if (is_array($cached)) {
                return $cached;
            }

            if ($this->failedFetchInRequest) {
                return $this->getFallbackPoliReferences();
            }
        }

        $connection = (string) config('services.regonline.poli_db_connection', 'sqlsrv');
        $driver = (string) config("database.connections.{$connection}.driver", 'sqlsrv');
        $host = trim((string) config("database.connections.{$connection}.host", ''));
        $database = trim((string) config("database.connections.{$connection}.database", ''));

        if ($host === '' || $database === '' || ($driver === 'sqlsrv' && ! extension_loaded('pdo_sqlsrv'))) {
            return $this->getFallbackPoliReferences();
        }

        try {
            // Query SELECT murni (read-only) agar tidak pernah memodifikasi database RSSES
            $rawRows = DB::connection($connection)
                ->table('dbo.ref_poli_online')
                ->select(['kode_poli', 'nama_poli', 'isActive'])
                ->orderBy('nama_poli')
                ->orderBy('kode_poli')
                ->get()
                ->map(fn ($row) => [
                    'code' => trim((string) ($row->kode_poli ?? '')),
                    'name' => trim((string) ($row->nama_poli ?? '')),
                    'is_active' => strtolower(trim((string) ($row->isActive ?? ''))) === 'y',
                ])
                ->filter(fn (array $item) => $item['code'] !== '')
                ->unique('code')
                ->values();

            $activeCodes = $this->extractActiveCodes($rawRows->all());
            $items = $rawRows
                ->filter(fn (array $item) => $item['name'] !== '')
                ->values()
                ->all();

            Cache::put(self::LIST_CACHE_KEY, $items, $ttl);
            Cache::forever(self::LIST_FALLBACK_CACHE_KEY, $items);
            Cache::put(self::CACHE_KEY, $activeCodes, $ttl);
            Cache::forever(self::FALLBACK_CACHE_KEY, $activeCodes);

            return $items;
        } catch (\Throwable $e) {
            $this->failedFetchInRequest = true;

            Log::warning('Gagal membaca referensi dbo.ref_poli_online dari SQL Server; menggunakan cache fallback.', [
                'exception_type' => get_class($e),
            ]);

            $fallback = $this->getFallbackPoliReferences();
            if ($fallback !== null) {
                Cache::put(self::LIST_CACHE_KEY, $fallback, min($ttl, 60));
            }

            return $fallback;
        }
    }

    /**
     * Membaca daftar kode_poli yang aktif (isActive = 'y') secara READ-ONLY
     * dari tabel dbo.ref_poli_online pada koneksi SQL Server sekunder.
     *
     * @return array<int, string>|null Daftar kode poli aktif, atau null jika sumber belum tersedia.
     */
    public function getActivePoliCodes(bool $forceRefresh = false): ?array
    {
        $ttl = max((int) config('services.regonline.poli_cache_ttl', 300), 30);

        if (! $forceRefresh) {
            $cached = Cache::get(self::CACHE_KEY);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $references = $this->getPoliReferences($forceRefresh);
        if (is_array($references)) {
            if (! $forceRefresh && ! $this->failedFetchInRequest) {
                $cachedAfterFetch = Cache::get(self::CACHE_KEY);
                if (is_array($cachedAfterFetch)) {
                    return $cachedAfterFetch;
                }
            }

            $activeCodes = $this->extractActiveCodes($references);
            Cache::put(self::CACHE_KEY, $activeCodes, $this->failedFetchInRequest ? min($ttl, 60) : $ttl);

            return $activeCodes;
        }

        $fallback = $this->getFallbackCodes();
        if ($fallback !== null) {
            Cache::put(self::CACHE_KEY, $fallback, min($ttl, 60));
        }

        return $fallback;
    }

    /**
     * @param array<int, array{code: string, name: string, is_active: bool}> $references
     * @return array<int, string>
     */
    protected function extractActiveCodes(array $references): array
    {
        $codes = [];
        foreach ($references as $item) {
            $code = trim((string) ($item['code'] ?? ''));
            if ($code !== '' && ! empty($item['is_active'])) {
                $codes[$code] = $code;
            }
        }

        return array_values($codes);
    }

    /**
     * @return array<int, array{code: string, name: string, is_active: bool}>|null
     */
    protected function getFallbackPoliReferences(): ?array
    {
        $fallback = Cache::get(self::LIST_FALLBACK_CACHE_KEY);

        return is_array($fallback) ? $fallback : null;
    }

    /**
     * @return array<int, string>|null
     */
    protected function getFallbackCodes(): ?array
    {
        $fallback = Cache::get(self::FALLBACK_CACHE_KEY);

        return is_array($fallback) ? $fallback : null;
    }
}
