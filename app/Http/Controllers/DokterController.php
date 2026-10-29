<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\DoctorApiService;
use App\Services\CmsApiService;
use App\DTOs\Cms\DoctorDto;
use App\Exceptions\CmsApiException;
use Illuminate\Http\JsonResponse;

class DokterController extends Controller
{
    //
    public function __construct(
        protected DoctorApiService $apiService,
        protected CmsApiService $cmsApiService
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

        return view('dokter.index', compact('units', 'spesialisasi'));

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

        // dd($spesialisasi);

        return view("dokter.index", compact('spesialisasi'));
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
