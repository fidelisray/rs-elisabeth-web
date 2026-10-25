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
        $spesialisasi = $this->apiService->getCachedSpesialisasi();

        // dd($spesialisasi);

        return view("dokter.index", compact('spesialisasi'));
    }

    public function dokterBySpesialisasi(string $specialtyCode) {

        $doctors = $this->apiService->getDokterBySpesialisasi($specialtyCode);

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

        $data = Cache::get('all_doctors_list', []);

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data dokter belum tersedia.',
                'data'    => [],
            ], 503);
        }
        
        return response()->json($data, 200);
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
