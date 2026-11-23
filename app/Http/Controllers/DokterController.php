<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\DoctorApiService;
use App\Services\CmsApiService;
use App\Services\RegOnlinePoliReferenceService;
use App\DTOs\Cms\DoctorDto;
use App\Exceptions\CmsApiException;
use Illuminate\Http\JsonResponse;

class DokterController extends Controller
{
    //
    public function __construct(
        protected DoctorApiService $apiService,
        protected CmsApiService $cmsApiService,
        protected RegOnlinePoliReferenceService $poliReferenceService
    ) {}

    public function profile(Request $request, string $paramedicCode): JsonResponse
    {
        $validated = $request->validate(['lang' => ['nullable', 'in:id,en']]);
        if (isset($validated['lang'])) {
            app()->setLocale($validated['lang']);
        }
        try {
            $profile = $this->cmsApiService->getDoctorByCode($paramedicCode);
            if ($profile === null || ! ($profile['is_active'] ?? false)) {
                return response()->json(['success' => false, 'message' => __('Profil dokter belum tersedia.')], 404)
                    ->header('Cache-Control', 'no-store');
            }

            return response()->json(['success' => true, 'data' => DoctorDto::fromArray($profile)->toArray()])
                ->header('Cache-Control', 'no-store');
        } catch (CmsApiException $e) {
            return response()->json(['success' => false, 'message' => __('Profil dokter belum dapat dimuat.')], 503)
                ->header('Cache-Control', 'no-store');
        }
    }

    public function index(Request $request)
    {
        // $filters = $request->only(['spesialisasi', 'nama', 'page']);
        // [$units, $dokter] = [
        //     $this->apiService->getDaftarUnits(),
        //     $this->apiService->getDaftarDokter(),
        // ];

        $units = $this->apiService->getDaftarUnits();
        $spesialisasi = $this->apiService->getDaftarSpesialisasi();
        $activePoliCodes = $this->poliReferenceService->getActivePoliCodes();

        return view('dokter.index', compact('units', 'spesialisasi', 'activePoliCodes'));

        // return view('dokter.index', compact('dokter', 'filters'));

        // return view('dokter.index', [
        //     'units' => $units,
        //     'doctors' => $doctors
        // ]);
    }

    public function dokterByUnitId(string $id) {

        $doctors = $this->apiService->GetDaftarDokterByUnitId($id);

        return response()->json($doctors);
    }

    public function dokterInit() {
        $doctors = $this->apiService->GetDaftarDokterByUnitId('PL-01');

        return response()->json($doctors);
    }

    public function spesialisasi() {
        $spesialisasi = $this->apiService->getDaftarSpesialisasi();
        $activePoliCodes = $this->poliReferenceService->getActivePoliCodes();

        // dd($spesialisasi);

        return view("dokter.index", compact('spesialisasi', 'activePoliCodes'));
    }

    public function dokterBySpesialisasi(string $specialtyCode) {

        $doctors = $this->apiService->getDokterBySpesialisasi($specialtyCode);
        if (isset($doctors['ScheduleRoutine']) && is_array($doctors['ScheduleRoutine'])) {
            $doctors['ScheduleRoutine'] = $this->filterActiveDoctors($doctors['ScheduleRoutine']);
        }

        // dd(response()->json($doctors['Data']));
        // dd($doctors);

        return response()->json($doctors);
    }

    public function allDokter(Request $request): \Illuminate\Http\JsonResponse
    {
        // Tolak jika bukan AJAX request
        if (!$request->ajax()) {
            abort(403, 'Forbidden');
        }

        // $data = Cache::get('all_doctors_list', []);

        // if (empty($data)) {
        //     // Gunakan cache lock untuk mencegah race condition (multiple simultaneous warm-up)
        //     $lock = Cache::lock('lock:warmup_doctors', 60);
        //     if ($lock->get()) {
        //         try {
        //             @set_time_limit(120);
        //             $this->apiService->fetchAndCacheAllDokter();
        //         } finally {
        //             $lock->release();
        //         }
        //         $data = Cache::get('all_doctors_list', []);
        //     } else {
        //         // Tunggu proses warm-up yang sedang berjalan oleh thread lain (maks 15 detik)
        //         $lock->block(15);

        //     }
        // }

        $freshTtl = (int) config('rsapi.cache_ttl.doctors_fresh', 3600);
        $staleTtl = (int) config('rsapi.cache_ttl.doctors_stale', 86400);

        try {
            $data = Cache::flexible('all_doctors_list', [$freshTtl, $staleTtl], function () {
                return $this->apiService->getFreshDoctorsList();
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal revalidasi data dokter di background', [
                'error' => $e->getMessage(),
            ]);
            $data = Cache::get('all_doctors_list', []);
        }

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data dokter belum tersedia.',
                'data'    => [],
            ], 503);
        }

        $data = $this->filterActiveDoctors($data);

        return response()->json($data, 200);
    }

    /**
     * Menyaring daftar jadwal dokter Medin berdasarkan status is_active dari CMS.
     * Fail-open: jika CMS tidak dapat dihubungi, kembalikan data Medin tanpa gagal.
     */
    protected function filterActiveDoctors(array $schedules): array
    {
        if (empty($schedules)) {
            return [];
        }

        try {
            $cmsDoctors = $this->cmsApiService->getDoctors();
            if (empty($cmsDoctors)) {
                return $schedules;
            }

            $activeCodes = [];
            foreach ($cmsDoctors as $doc) {
                if (($doc['is_active'] ?? true) && ! empty($doc['paramedic_code'])) {
                    $activeCodes[trim((string) $doc['paramedic_code'])] = true;
                }
            }

            return array_values(array_filter($schedules, function ($item) use ($activeCodes) {
                $row = is_object($item) ? (array) $item : $item;
                $code = trim((string) ($row['ParamedicCode'] ?? ''));

                return $code !== '' && isset($activeCodes[$code]);
            }));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal menyinkronkan status is_active dokter dari CMS; menampilkan data Medin.', [
                'exception_type' => get_class($e),
            ]);

            return $schedules;
        }
    }

    public function test_doctor_api() {
        $doctors = Cache::get('all_doctors_list', []);
        return response()->json($doctors);
    }

    /**
     * Mengenkripsi parameter pendaftaran menjadi link Regonline (?d=...)
     *
     * @param array    $data             Data jadwal & pasien
     * @param string   $secretKey        Kunci rahasia (HARUS SAMA dengan ENV['URL_ENCRYPTION_KEY'] di Regonline)
     * @param string   $baseUrl          URL dasar Regonline
     * @param int|null $expiresInSeconds Opsional: masa berlaku link dalam detik (misal 7 * 86400 untuk 7 hari)
     * @return string                    URL lengkap siap kirim
     */
    public static function buatLinkRegonline(
        array $data,
        string $secretKey,
        string $baseUrl = 'https://regonline.rs-elisabeth.com',
        ?int $expiresInSeconds = null
    ): string {
        if (trim($secretKey) === '') {
            throw new \RuntimeException('Kunci enkripsi RegOnline belum dikonfigurasi.');
        }

        if ($expiresInSeconds !== null && $expiresInSeconds > 0) {
            $data['exp'] = time() + $expiresInSeconds;
        }

        $cipher = 'aes-256-gcm';
        $key    = hash('sha256', $secretKey, true); // 32-byte binary key
        $iv     = random_bytes(12);                 // 12-byte nonce standar GCM
        $tag    = '';

        $plaintext = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($plaintext === false) {
            throw new \RuntimeException('Gagal meng-encode data ke JSON.');
        }

        $ciphertext = openssl_encrypt(
            $plaintext,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16 // Auth tag 16 byte (anti-tamper)
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Proses enkripsi OpenSSL gagal.');
        }

        // Gabungkan IV (12 byte) + Tag (16 byte) + Ciphertext, lalu encode URL-safe Base64
        $combined = $iv . $tag . $ciphertext;
        $token    = rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');

        return rtrim($baseUrl, '/') . '/?d=' . $token;
    }

    public function dispatchBooking(Request $request)
    {
        $usePrefill = $request->boolean('use_prefill')
            || $request->filled('rm')
            || $request->filled('dob')
            || $request->filled('wa');

        $rules = [
            'tgl'        => ['required', 'string', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'kodepoli'   => ['required', 'string', 'max:50'],
            'kodedokter' => ['required', 'string', 'max:50'],
            'sesi'       => ['required', 'integer', 'min:1'],
        ];

        if ($usePrefill) {
            $rules['rm']  = ['required', 'string', 'max:30', 'regex:/^[0-9\-]+$/'];
            $rules['dob'] = ['required', 'string', 'max:20'];
            $rules['wa']  = ['required', 'string', 'max:25', 'regex:/^[0-9+\-\s]+$/'];
            $rules['cb']  = ['nullable', 'string', 'in:PERSONAL,ASURANSI'];
        }

        $validated = $request->validate($rules);

        if ($usePrefill) {
            // Struktur Payload 1: Lengkap dengan pre-fill data pasien
            $payload = [
                'tgl'        => $validated['tgl'],
                'kodepoli'   => $validated['kodepoli'],
                'kodedokter' => $validated['kodedokter'],
                'sesi'       => (int) $validated['sesi'],
                'rm'         => trim($validated['rm']),
                'dob'        => trim($validated['dob']),
                'wa'         => trim($validated['wa']),
            ];

            if (! empty($validated['cb'])) {
                $payload['cb'] = $validated['cb'];
            }
        } else {
            // Struktur Payload 2: Default hanya jadwal poli & dokter
            $payload = [
                'tgl'        => $validated['tgl'],
                'kodepoli'   => $validated['kodepoli'],
                'kodedokter' => $validated['kodedokter'],
                'sesi'       => (int) $validated['sesi'],
            ];
        }

        $baseUrl   = (string) config('services.regonline.base_url', 'https://regonline.rs-elisabeth.com');
        $secretKey = trim((string) config('services.regonline.encryption_key', ''));
        if ($secretKey === '') {
            \Illuminate\Support\Facades\Log::error('Kunci enkripsi RegOnline belum dikonfigurasi pada environment.');
            abort(503, __('Layanan Sementara Terganggu'));
        }
        $rawTtl    = config('services.regonline.token_ttl');
        $ttl       = ($rawTtl !== null && $rawTtl !== '' && (int) $rawTtl > 0) ? (int) $rawTtl : null;

        $targetUrl = self::buatLinkRegonline(
            data: $payload,
            secretKey: $secretKey,
            baseUrl: $baseUrl,
            expiresInSeconds: $ttl
        );

        return redirect()->away($targetUrl);
    }

    // public function detail(int $id)
    // {
    //     // Ambil detail + jadwal secara bersamaan (concurrent requests)
    //     [$detail, $jadwal] = [
    //         $this->apiService->getDokterById($id),
    //         $this->apiService->getJadwalDokter($id),
    //     ];

    //     abort_if(empty($detail), 404);

    //     return view('dokter.detail', compact('detail', 'jadwal'));
    // }
}
