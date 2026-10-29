<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DoctorApiService
{
    /**
     * Create a new class instance.
     */
    protected string $baseUrl;
    protected string $medinEndpoint;
    // protected string $apiKey;
    protected string $consId;
    protected string $secretKey;
    protected int $timeout;

    protected const TOKEN_CACHE_KEY = null;
    // protected const TOKEN_CACHE_KEY = 'hospital_api_token';

    protected function decodeData(mixed $data): mixed
    {
        return is_string($data) ? json_decode($data) : $data;
    }

    public function __construct()
    {
        $this->baseUrl = config('rsapi.base_url') ?? '';
        $this->medinEndpoint = config('rsapi.medin_endpoint') ?? '';
        // $this->apiKey = config('rsapi.api_key');
        $this->consId = config('rsapi.medin_consid') ?? '';
        $this->secretKey = config('rsapi.medin_secretkey') ?? '';
        $this->timeout = (int) config('rsapi.timeout', 5);
    }

    protected function apiRequest(): \Illuminate\Http\Client\PendingRequest
    {   
        if (! config('rsapi.enabled', true)) throw new \RuntimeException('RS API dinonaktifkan pada environment ini.');
        // $token = $this->getValidToken();

        // dd($token);

        $consid = $this->consId;
        $secretKey = $this->secretKey;

        $tStamp = strval(time()); // Unix timestamp — strtotime('1970-01-01') selalu 0
        $signature = hash_hmac('sha256', $tStamp . $consid, $secretKey, true);
        $encodedSignature = base64_encode($signature);

        return Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $this->apiKey,
            'X-Cons-ID' => $consid,
            'X-Timestamp' => $tStamp,
            'X-Signature' => $encodedSignature,
            'Accept'  => 'application/json',
        ])->timeout($this->timeout)
          ->connectTimeout(3)
          ->retry(2, 100, function ($exception, $request) {
              return $exception instanceof \Throwable
                  || ($request->response() && $request->response()->serverError());
          }, throw: false);
    }

    /**
     * Ambil daftar dokter, 
     * setup cache 
     */
    public function getDaftarUnits(array $filters = []): array
    {
        $cacheKey = 'units_' . md5(serialize($filters));
        $ttl      = config('rsapi.cache_ttl.units');

        return Cache::remember($cacheKey, $ttl, function () use ($filters) {
            try {
                $response = $this->apiRequest()
                    ->get("{$this->baseUrl}{$this->medinEndpoint}/api/reference/master/lst_hsu");

                // Dump struktur response, lalu stop eksekusi
                
                if ($response->successful()) {
                    // echo gettype(json_decode($response->json('Data')));
                    // dd(json_decode($response->json('Data')));
                    
                    $data = $this->decodeData($response->json('Data'));
                    return is_array($data) ? $data : [];
                }

                Log::warning('API dokter gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    // public function getDaftarDokter(array $filters = []): array
    // {
    //     $cacheKey = 'dokter_' . md5(serialize($filters));
    //     $ttl      = config('rsapi.cache_ttl.dokter');

    //     return Cache::remember($cacheKey, $ttl, function () use ($filters) {
    //         try {
    //             $response = $this->apiRequest()
    //                 ->get("{$this->baseUrl}{$this->medinEndpoint}/api/physician/list/doctor-schedule/PL-02");

    //             // Dump struktur response, lalu stop eksekusi
    //             // dd($response->json());

    //             if ($response->successful()) {
    //                 return $response->json('Data', []);
    //             }

    //             Log::warning('API dokter gagal', [
    //                 'status' => $response->status(),
    //                 'body'   => $response->body(),
    //             ]);
    //             return [];

    //         } catch (\Exception $e) {
    //             Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
    //             return [];
    //         }
    //     });
    // }
    
    public function getDaftarDokterByUnitId(string $id): array
    {
        $cacheKey = 'dokterUnitId_'.$id;
        $ttl      = config('rsapi.cache_ttl.dokter');

        return Cache::remember($cacheKey, $ttl, function () use ($id) {
            try {
                $response = $this->apiRequest()
                    ->get("{$this->baseUrl}{$this->medinEndpoint}/api/physician/list/doctor-schedule/{$id}");

                // Dump struktur response, lalu stop eksekusi
                // dd($response->json());

                if ($response->successful()) {
                    $data = $this->decodeData($response->json('Data'));
                    return is_array($data) ? $data : [];
                }

                Log::warning('API dokter gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function getDaftarSpesialisasi(bool $forceRefresh = false): array
    {
        $cacheKey = 'specialty_list_';
        $fallbackKey = 'specialty_list_fallback';
        $ttl = (int) config('rsapi.cache_ttl.specialty', 3600);

        if (! $forceRefresh) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ! empty($cached)) {
                return $cached;
            }
        }

        try {
            $response = $this->apiRequest()
                ->get("{$this->baseUrl}{$this->medinEndpoint}/api/reference/master/lst_spc");

            // Dump struktur response, lalu stop eksekusi
            // dd(json_decode($response->json('Data')));

            if ($response->successful()) {
                $data = $this->decodeData($response->json('Data')) ?? [];
                if (! is_array($data)) {
                    $data = [];
                }
                if (! empty($data)) {
                    Cache::put($cacheKey, $data, $ttl);
                    Cache::forever($fallbackKey, $data);

                    return $data;
                }
            }

            Log::warning('API dokter gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
        }

        $fallback = Cache::get($fallbackKey, []);
        if (is_array($fallback) && ! empty($fallback)) {
            Cache::put($cacheKey, $fallback, min(max($ttl, 60), 300));

            return $fallback;
        }

        return [];
    }

    public function getCachedSpesialisasi(): array
    {
        $cached = Cache::get('specialty_list_');
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        return $this->getDaftarSpesialisasi();
    }

    public function getCachedUnits(array $filters = []): array
    {
        $cacheKey = 'units_' . md5(serialize($filters));
        return Cache::get($cacheKey, []);
    }

    public function getDokterBySpesialisasi(string $specialtyCode, bool $forceRefresh = false): array
    {
        $cacheKey = 'doctor_specialty_' . $specialtyCode;
        $ttl      = config('rsapi.cache_ttl.dokter_by_speciality');

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use($specialtyCode) {
            try {
                $encodedCode = rawurlencode($specialtyCode);
                $response = $this->apiRequest()
                    ->get("{$this->baseUrl}{$this->medinEndpoint}/api/paramedicschedule/base/list/specialty/{$encodedCode}");

                if ($response->successful()) {
                    $status = $response->json('Status');
                    $rawData = $response->json('Data');

                    // Jika spesialisasi belum memiliki dokter terdaftar di HIS
                    if ($status === 'NOT FOUND' || $rawData === null || $rawData === '') {
                        return [
                            "Success" => true,
                            "LeaveSchedule" => [],
                            "ScheduleByDate" => [],
                            "ScheduleRoutine" => [],
                        ];
                    }

                    $data = $this->decodeData($rawData);
                    if (! is_object($data) || ! is_array($data->ScheduleRoutine ?? null)) {
                        return ['Success' => false, 'Message' => 'Struktur jadwal tidak valid.'];
                    }

                    $response_data = [
                        "Success" => true,
                        "LeaveSchedule" => $data?->LeaveSchedule ?? [],
                        "ScheduleByDate" => $data?->ScheduleByDate ?? [],
                        "ScheduleRoutine" => $data?->ScheduleRoutine ?? [],
                    ];

                    return $response_data;
                }

                Log::warning('API dokter gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    "Success" => false,
                    "Message" => "Data gagal diambil dari API Medin -request DokterBySpesialisasi"
                ];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return [
                    "Success" => false, 
                    "Message" => "Gagal conncect ke API RS -request DokterBySpesialisasi"
                ];
            }
        });
    }

    // app/Services/HospitalApiService.php

    /**
     * Ambil semua dokter dari seluruh specialty code,
     * hasil merge disimpan di cache terpisah.
     */

    public function fetchAndCacheAllDokter(bool $forceRefresh = false): int
    {
        if (! config('rsapi.enabled', true)) return 0;

        $blacklist = config('rsapi.blacklist_specialties', []);
        $delay = config('rsapi.fetch_delay_microseconds', 150000);

        // Ambil specialty codes secara dinamis dari API
        $spesialisasiList = [];

        foreach ($this->getDaftarSpesialisasi($forceRefresh) as $items) {
            $code = ((array) $items)['Code'] ?? null;
            if (is_string($code) && $code !== '' && !in_array($code, $blacklist, true)) {
                $spesialisasiList[] = $code;
            }
        }

        if (empty($spesialisasiList)) {
            Log::error('Gagal fetch daftar spesialisasi, fetchAndCacheAllDokter dibatalkan.');
            return 0;
        }

        $allDoctors = [];
        $failed = [];
        $i = 1;

        foreach ($spesialisasiList as $code) {
            try {
                $data = $this->getDokterBySpesialisasi($code, $forceRefresh);

                if (!($data['Success'] ?? false) || ! is_array($data['ScheduleRoutine'] ?? null)) {
                    $failed[] = $code;
                    $i++;
                    continue;
                }

                $i++;

                if (!empty($data['ScheduleRoutine']) && is_array($data['ScheduleRoutine'])) {
                    foreach ($data['ScheduleRoutine'] as $item) {
                        $itemArray = is_object($item) ? (array) $item : $item;
                        $itemArray['SpecialityCode'] = $code;
                        $allDoctors[] = $itemArray;
                    }
                }
            } catch (\Exception $e) {
                $failed[] = $code;
                Log::warning("Gagal fetch specialty {$code}", ['exception_type' => get_class($e)]);
                $i++;
                continue;
            }

            if ($delay > 0) {
                usleep($delay);
            }
        }

        if ($allDoctors === []) {
            Log::error('Warm-up dokter gagal total: tidak ada data dokter yang berhasil ditarik.', [
                'failed_specialties' => $failed,
            ]);
            return 0;
        }

        if ($failed !== []) {
            Log::warning('Warm-up dokter selesai dengan beberapa spesialisasi gagal.', [
                'failed_specialties' => $failed,
                'total_doctors_cached' => count($allDoctors),
            ]);
        }

        Cache::put('all_doctors_list', $allDoctors, now()->addHours(12));
        Cache::put('all_doctors_grouped', collect($allDoctors)
            ->groupBy('SpecialityCode')
            ->toArray(), now()->addHours(12));

        return count($allDoctors);
    }

    /**
     * Menarik seluruh data dokter segar dari API MedInfras dengan perlindungan fail-safe.
     *
     * @throws \RuntimeException jika penarikan data gagal total (agar SWR mempertahankan cache lama)
     */
    public function getFreshDoctorsList(bool $forceRefresh = false): array
    {
        $count = $this->fetchAndCacheAllDokter($forceRefresh);
        $data = Cache::get('all_doctors_list', []);

        if (empty($data)) {
            throw new \RuntimeException('Gagal menarik data dokter dari MedInfras; mempertahankan data cache sebelumnya.');
        }

        return $data;
    }



    // public function getDokterBySpesialisasi(string $specialtyCode): array
    // {
    //     $cacheKey = 'specialty_' . $specialtyCode;
    //     $ttl      = config('rsapi.cache_ttl.specialty');

    //     return Cache::remember($cacheKey, $ttl, function () use($specialtyCode) {
    //         try {
    //             $response = $this->apiRequest()
    //                 ->get("{$this->baseUrl}{$this->medinEndpoint}/api/paramedic/schedule/specialty/{$specialtyCode}");

    //             if ($response->successful()) {

    //                 $data = json_decode($response->json('Data'));

    //                 $response_data = [
    //                     "LeaveSchedule" => $data->LeaveSchedule,
    //                     "ScheduleByDate" => $data->ScheduleByDate,
    //                     "ScheduleRoutine" => $data->ScheduleRoutine,
    //                 ];

    //                 return $response_data;
    //             }

    //             Log::warning('API dokter gagal', [
    //                 'status' => $response->status(),
    //                 'body'   => $response->body(),
    //             ]);
    //             return [$response->status(), $response->body()];

    //         } catch (\Exception $e) {
    //             Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
    //             return ["Gagal conncect ke API RS -request DaftarDokterByUnitId"];
    //         }
    //     });
    // }
}
