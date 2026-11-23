<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegOnlinePoliReferenceService
{
    public const CACHE_KEY = 'regonline_active_poli_codes';
    public const FALLBACK_CACHE_KEY = 'regonline_active_poli_codes_fallback';

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

        $connection = (string) config('services.regonline.poli_db_connection', 'sqlsrv');
        $host = trim((string) config("database.connections.{$connection}.host", ''));
        $database = trim((string) config("database.connections.{$connection}.database", ''));

        if ($host === '' || $database === '' || ! extension_loaded('pdo_sqlsrv')) {
            return $this->getFallbackCodes();
        }

        try {
            // Query SELECT murni (read-only) agar tidak pernah memodifikasi database RSSES
            $codes = DB::connection($connection)
                ->table('dbo.ref_poli_online')
                ->whereRaw("LOWER(LTRIM(RTRIM(isActive))) = 'y'")
                ->pluck('kode_poli')
                ->map(fn ($code) => trim((string) $code))
                ->filter(fn ($code) => $code !== '')
                ->unique()
                ->values()
                ->all();

            Cache::put(self::CACHE_KEY, $codes, $ttl);
            Cache::forever(self::FALLBACK_CACHE_KEY, $codes);

            return $codes;
        } catch (\Throwable $e) {
            Log::warning('Gagal membaca referensi dbo.ref_poli_online dari SQL Server; menggunakan cache fallback.', [
                'exception_type' => get_class($e),
            ]);

            $fallback = $this->getFallbackCodes();
            if ($fallback !== null) {
                Cache::put(self::CACHE_KEY, $fallback, min($ttl, 60));
            }

            return $fallback;
        }
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
