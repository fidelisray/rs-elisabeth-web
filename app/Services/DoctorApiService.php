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
        // $token = $this->getValidToken();

        // dd($token);

        date_default_timezone_set('Asia/Jakarta');

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
        ])->timeout($this->timeout);
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
                    
                    return json_decode($response->json('Data', []));
                }

                Log::warning('API dokter gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [$response->status(), $response->body()];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return ["Gagal conncect ke API RS -request DaftarUnits"];
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
                    return $response->json('Data', []);
                }

                Log::warning('API dokter gagal', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [$response->status(), $response->body()];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return ["Gagal conncect ke API RS -request DaftarDokterByUnitId"];
            }
        });
    }

    public function getDaftarSpesialisasi(): array
    {
        $cacheKey = 'specialty_list_';
        $ttl      = config('rsapi.cache_ttl.specialty');

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = $this->apiRequest()
                ->get("{$this->baseUrl}{$this->medinEndpoint}/api/reference/master/lst_spc");

            // Dump struktur response, lalu stop eksekusi
            // dd(json_decode($response->json('Data')));

            if ($response->successful()) {
                $data = json_decode($response->json('Data', '[]')) ?? [];
                if (!is_array($data)) {
                    $data = [];
                }
                Cache::put($cacheKey, $data, $ttl);
                return $data;
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
    }

    public function getCachedSpesialisasi(): array
    {
        return Cache::get('specialty_list_', []);
    }

    public function getCachedUnits(array $filters = []): array
    {
        $cacheKey = 'units_' . md5(serialize($filters));
        return Cache::get($cacheKey, []);
    }

    public function getDokterBySpesialisasi(string $specialtyCode): array
    {
        $cacheKey = 'doctor_specialty_' . $specialtyCode;
        $ttl      = config('rsapi.cache_ttl.dokter_by_speciality');

        return Cache::remember($cacheKey, $ttl, function () use($specialtyCode) {
            try {
                $response = $this->apiRequest()
                    ->get("{$this->baseUrl}{$this->medinEndpoint}/api/paramedicschedule/base/list/specialty/{$specialtyCode}");

                // Dump struktur response, lalu stop eksekusi
                // dd($response->json('Data'));
                // dd(json_decode($response->json('Data')));

                // dd($response->status());

                if ($response->successful()) {
                    // dd(json_decode($response->json('Data', [])));

                    $data = json_decode($response->json('Data'));

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
                // return [$response->status(), $response->body()];

            } catch (\Exception $e) {
                Log::error('Gagal connect ke API RS', ['error' => $e->getMessage()]);
                return [
                    "Success" => false, 
                    "Message" => "Gagal conncect ke API RS -request DokterBySpesialisasi"];
            }
        });
    }

    // app/Services/HospitalApiService.php

    /**
     * Ambil semua dokter dari seluruh specialty code,
     * hasil merge disimpan di cache terpisah.
     */

    public function fetchAndCacheAllDokter(): int
    {
    // Ambil specialty codes secara dinamis dari API
    $spesialisasiList = [];


    foreach ($this->getDaftarSpesialisasi() as $items) {
        $spesialisasiList[] = $items->Code;
    }

    if (empty($spesialisasiList)) {
        Log::error('Gagal fetch daftar spesialisasi, fetchAndCacheAllDokter dibatalkan.');
        return 0;
    }

    // dd($spesialisasiList);

    $allDoctors = [];
    $i = 1;
    $specialityLength = count($spesialisasiList);

    foreach ($spesialisasiList as $code) {

        try {
            $data = $this->getDokterBySpesialisasi($code);
            
            
            if (!$data['Success']) {
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

            // dd($allDoctors);
        } catch (\Exception $e) {
            Log::warning("Gagal fetch specialty {$code}", ['error' => $e->getMessage()]);

            echo "\t" . "Gagal fetch speciality {$code}";
            echo "\n\n";
            $i++;
            continue;
        }

        sleep(1);
    }

    Cache::put('all_doctors_list', $allDoctors, now()->addHours(12));
    Cache::put('all_doctors_grouped', collect($allDoctors)                       // grouped → jika perlu
    ->groupBy('SpecialityCode')
    ->toArray(), now()->addHours(12));

    return count($allDoctors);
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
