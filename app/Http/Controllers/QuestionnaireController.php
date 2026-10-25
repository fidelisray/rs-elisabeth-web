<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionnaireRequest;
use App\Services\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    protected QuestionnaireService $questionnaireService;

    public function __construct(QuestionnaireService $questionnaireService)
    {
        $this->questionnaireService = $questionnaireService;
    }

    /**
     * Skala standar penilaian kuesioner.
     */
    protected function getScales(): array
    {
        return [
            'puas' => [
                'Sangat Tidak Puas',
                'Tidak Puas',
                'Puas',
                'Sangat Puas',
            ],
            'setuju' => [
                'Sangat Tidak Setuju',
                'Tidak Setuju',
                'Setuju',
                'Sangat Setuju',
            ],
            'mudah' => [
                'Sangat Tidak Mudah',
                'Tidak Mudah',
                'Mudah',
                'Sangat Mudah',
            ],
            'memadai' => [
                'Sangat Tidak Memadai',
                'Tidak Memadai',
                'Memadai',
                'Sangat Memadai',
            ],
        ];
    }

    /**
     * Opsi profil demografi responden.
     */
    protected function getDemographicOptions(): array
    {
        return [
            'age_groups' => ['< 15 tahun', '15 - 64 tahun', '> 64 tahun'],
            'genders'    => ['Laki-laki', 'Perempuan'],
            'payments'   => ['Tunai', 'BPJS Kesehatan', 'Asuransi', 'Jaminan Perusahaan'],
        ];
    }

    /**
     * Portal Hub Kuesioner Kepuasan Pasien.
     */
    public function index(): View
    {
        $units = [
            [
                'slug'        => 'igd',
                'route'       => route('questionnaire.igd'),
                'title'       => 'Instalasi Gawat Darurat (IGD)',
                'description' => 'Penilaian layanan kegawatdaruratan, kecepatan triase, responsivitas dokter/perawat, dan fasilitas ruang tunggu IGD.',
                'icon'        => 'fa-solid fa-truck-medical',
                'questions_count' => 19,
                'time_estimate'   => '3-4 menit',
                'color'       => '#e11d48', // Medical Rose
            ],
            [
                'slug'        => 'rawat-jalan',
                'route'       => route('questionnaire.rawat-jalan'),
                'title'       => 'Poliklinik Rawat Jalan',
                'description' => 'Penilaian kenyamanan ruang tunggu, ketepatan jadwal dokter, keramahan staf pendaftaran/apotek/kasir, dan fasilitas poliklinik.',
                'icon'        => 'fa-solid fa-stethoscope',
                'questions_count' => 15,
                'time_estimate'   => '2-3 menit',
                'color'       => '#008fd7', // Medical Blue
            ],
            [
                'slug'        => 'rawat-inap',
                'route'       => route('questionnaire.rawat-inap'),
                'title'       => 'Ruang Rawat Inap',
                'description' => 'Penilaian kenyamanan kamar & tempat tidur, kebersihan, frekuensi visite dokter, asuhan keperawatan, dan mutu makanan diet.',
                'icon'        => 'fa-solid fa-bed-pulse',
                'questions_count' => 23,
                'time_estimate'   => '4-5 menit',
                'color'       => '#059669', // Healing Emerald
            ],
        ];

        return view('questionnaire.index', compact('units'));
    }

    /**
     * Halaman Kuesioner Kepuasan Pasien IGD.
     */
    public function igd(): View
    {
        $type = 'igd';
        $title = 'Kuesioner Kepuasan Pasien IGD';
        $badge = 'Instalasi Gawat Darurat';
        $badgeIcon = 'fa-solid fa-truck-medical';
        $actionRoute = route('questionnaire.igd.store');
        $isSuggestionRequired = false;

        $questions = [
            [
                'name'  => 'speed_triage_arrival',
                'text'  => 'Seberapa cepat Anda ditangani oleh petugas IGD setelah tiba?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'triage_efficiency',
                'text'  => 'Bagaimana penilaian Anda terhadap kecepatan dan efisiensi dalam proses triase (penilaian awal kondisi) yang Anda jalani?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'post_triage_care_speed',
                'text'  => 'Seberapa puas Anda akan kecepatan Anda menerima perawatan atau intervensi medis setelah proses triase?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'waiting_time_acceptable',
                'text'  => 'Anda merasa waktu tunggu untuk menerima perawatan lanjutan setelah penilaian awal dapat diterima',
                'scale' => 'setuju',
            ],
            [
                'name'  => 'medical_explanation',
                'text'  => 'Seberapa puas Anda terkait penjelasan dokter atau perawat tentang kondisi kesehatan Anda dan langkah-langkah perawatan yang akan diambil?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'informed_procedure_risks',
                'text'  => 'Anda diberitahu tentang prosedur medis yang akan dilakukan, termasuk risiko dan manfaatnya',
                'scale' => 'setuju',
            ],
            [
                'name'  => 'staff_responsiveness',
                'text'  => 'Dokter atau perawat responsif dalam menjawab pertanyaan atau kekhawatiran Anda selama berada di IGD',
                'scale' => 'setuju',
            ],
            [
                'name'  => 'calm_communication',
                'text'  => 'Seberapa baik staf medis menyampaikan informasi penting dengan tenang dan jelas, meskipun dalam situasi darurat atau krisis?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'emotional_support',
                'text'  => 'Seberapa baik perawat atau dokter memberikan dukungan emosional kepada Anda selama perawatan di IGD?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'family_support',
                'text'  => 'Keluarga Anda merasa diperhatikan dan diberikan dukungan emosional oleh staf IGD',
                'scale' => 'setuju',
            ],
            [
                'name'  => 'waiting_area_comfort',
                'text'  => 'Bagaimana penilaian Anda terhadap kenyamanan dan fasilitas di area tunggu IGD untuk keluarga?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'family_information_clarity',
                'text'  => 'Seberapa baik keluarga Anda menerima informasi yang konsisten dan jelas tentang kondisi dan perawatan Anda selama di IGD?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'overall_igd_experience',
                'text'  => 'Secara keseluruhan, bagaimana Anda menilai pengalaman Anda di Instalasi Gawat Darurat?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'information_accessibility',
                'text'  => 'Seberapa mudah Anda mendapatkan informasi yang Anda butuhkan selama berada di IGD?',
                'scale' => 'mudah',
            ],
            [
                'name'  => 'recommend_igd',
                'text'  => 'Anda akan merekomendasikan Unit Gawat Darurat RS St. Elisabeth Semarang kepada teman atau keluarga',
                'scale' => 'setuju',
            ],
        ];

        $suggestionPrompt = 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan di Unit Gawat Darurat kami?';
        $demographics = $this->getDemographicOptions();
        $scales = $this->getScales();

        return view('questionnaire.show', compact(
            'type', 'title', 'badge', 'badgeIcon', 'actionRoute', 'questions',
            'suggestionPrompt', 'isSuggestionRequired', 'demographics', 'scales'
        ));
    }

    /**
     * Memproses submit kuesioner IGD.
     */
    public function storeIgd(StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        return $this->handleStore('igd', $request);
    }

    /**
     * Halaman Kuesioner Kepuasan Pasien Rawat Jalan.
     */
    public function rawatJalan(): View
    {
        $type = 'rawat-jalan';
        $title = 'Kuesioner Kepuasan Pasien Rawat Jalan';
        $badge = 'Poliklinik Rawat Jalan';
        $badgeIcon = 'fa-solid fa-stethoscope';
        $actionRoute = route('questionnaire.rawat-jalan.store');
        $isSuggestionRequired = true; // Sesuai template Google Form bertanda bintang

        $questions = [
            [
                'name'  => 'waiting_room_comfort',
                'text'  => 'Bagaimana penilaian Anda terhadap kenyamanan ruang tunggu rawat jalan?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'examination_room_comfort',
                'text'  => 'Seberapa puas Anda dengan kenyamanan dan kualitas fasilitas di ruang pemeriksaan dokter?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'parking_staff',
                'text'  => 'Bagaimana Anda menilai ketersediaan dan keramahan petugas parkir?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'doctor_schedule_punctuality',
                'text'  => 'Seberapa puas Anda dengan ketepatan jadwal praktek dokter?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'doctor_explanation',
                'text'  => 'Seberapa puas Anda dengan penjelasan Dokter tentang kondisi kesehatan Anda dan rencana perawatan yang akan dijalani?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'nurse_responsiveness',
                'text'  => 'Bagaimana Anda menilai ketersediaan dan responsivitas perawat dalam memenuhi kebutuhan Anda?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'staff_information_clarity',
                'text'  => 'Seberapa puas Anda dengan informasi yang diberikan oleh petugas (pendaftaran, apoteker, analis, radiografer, kasir) mengenai prosedur yang akan dilakukan, pembiayaan, dan obat-obatan yang harus Anda konsumsi?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'staff_friendliness',
                'text'  => 'Seberapa puas Anda dengan keramahan petugas (pendaftaran, apoteker, analis, radiografer, kasir) dalam memberikan informasi kepada anda?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'staff_problem_solving',
                'text'  => 'Seberapa puas anda dengan kemampuan petugas kami dalam memberikan saran dan Solusi atas keluhan anda?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'overall_outpatient_experience',
                'text'  => 'Bagaimana Anda menilai keseluruhan pengalaman Anda selama melakukan pelayanan rawat jalan di Rumah Sakit St. Elisabeth Semarang?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'recommend_hospital',
                'text'  => 'Anda akan merekomendasikan rumah sakit ini kepada teman atau keluarga',
                'scale' => 'setuju',
            ],
        ];

        $suggestionPrompt = 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan rawat jalan di rumah sakit kami?';
        $demographics = $this->getDemographicOptions();
        $scales = $this->getScales();

        return view('questionnaire.show', compact(
            'type', 'title', 'badge', 'badgeIcon', 'actionRoute', 'questions',
            'suggestionPrompt', 'isSuggestionRequired', 'demographics', 'scales'
        ));
    }

    /**
     * Memproses submit kuesioner Rawat Jalan.
     */
    public function storeRawatJalan(StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        return $this->handleStore('rawat-jalan', $request);
    }

    /**
     * Halaman Kuesioner Kepuasan Pasien Rawat Inap.
     */
    public function rawatInap(): View
    {
        $type = 'rawat-inap';
        $title = 'Kuesioner Kepuasan Pasien Rawat Inap';
        $badge = 'Ruang Rawat Inap';
        $badgeIcon = 'fa-solid fa-bed-pulse';
        $actionRoute = route('questionnaire.rawat-inap.store');
        $isSuggestionRequired = false;

        $questions = [
            [
                'name'  => 'room_bed_comfort',
                'text'  => 'Bagaimana penilaian Anda terhadap kenyamanan kamar dan tempat tidur selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'room_cleanliness',
                'text'  => 'Bagaimana penilaian Anda terhadap kebersihan kamar dan kamar mandi yang Anda gunakan selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'room_facilities',
                'text'  => 'Seberapa puas Anda dengan ketersediaan dan kualitas fasilitas di kamar (misalnya, AC, TV, perlengkapan mandi)?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'environment_peace',
                'text'  => 'Bagaimana Anda menilai ketenangan dan keamanan lingkungan selama Anda dirawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'doctor_visit_frequency',
                'text'  => 'Seberapa puas Anda dengan frekuensi kunjungan dokter selama masa rawat inap Anda?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'doctor_explanation',
                'text'  => 'Seberapa puas Anda akan penjelasan Dokter tentang kondisi kesehatan Anda dan rencana perawatan yang akan dijalani?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'nurse_responsiveness',
                'text'  => 'Bagaimana Anda menilai ketersediaan dan responsivitas perawat dalam memenuhi kebutuhan Anda selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'nurse_care_quality',
                'text'  => 'Seberapa puas Anda dengan kualitas perawatan yang diberikan oleh perawat selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'medical_staff_info',
                'text'  => 'Seberapa puas Anda akan kejelasan informasi yang diberikan oleh staf medis mengenai kondisi kesehatan Anda, prosedur yang akan dilakukan, dan obat-obatan yang harus Anda konsumsi?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'decision_involvement',
                'text'  => 'Seberapa baik Anda dilibatkan dalam pengambilan keputusan mengenai perawatan dan pengobatan yang Anda terima?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'procedure_adequacy',
                'text'  => 'Seberapa memadai penjelasan tentang prosedur medis yang akan dilakukan, termasuk risiko dan manfaatnya?',
                'scale' => 'memadai',
            ],
            [
                'name'  => 'family_communication',
                'text'  => 'Seberapa baik komunikasi yang dilakukan oleh staf medis dengan keluarga Anda mengenai kondisi dan perawatan Anda selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'food_quality',
                'text'  => 'Bagaimana penilaian Anda terhadap kualitas dan rasa makanan yang disediakan selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'diet_menu_suitability',
                'text'  => 'Anda mendapatkan pilihan menu yang sesuai dengan kebutuhan diet Anda selama masa rawat inap',
                'scale' => 'setuju',
            ],
            [
                'name'  => 'diet_explanation',
                'text'  => 'Seberapa baik Anda diberi penjelasan tentang pentingnya diet dan bagaimana makanan yang disediakan mendukung proses penyembuhan Anda?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'overall_food_service',
                'text'  => 'Seberapa puas Anda secara keseluruhan dengan layanan makanan yang disediakan selama masa rawat inap?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'overall_inpatient_experience',
                'text'  => 'Bagaimana Anda menilai keseluruhan pengalaman Anda selama masa rawat inap di RS Elisabeth Semarang?',
                'scale' => 'puas',
            ],
            [
                'name'  => 'information_ease',
                'text'  => 'Seberapa mudah Anda mendapatkan informasi yang Anda butuhkan selama masa rawat inap?',
                'scale' => 'mudah',
            ],
            [
                'name'  => 'recommend_inpatient',
                'text'  => 'Anda akan merekomendasikan RS St. Elisabeth Semarang kepada teman atau keluarga',
                'scale' => 'setuju',
            ],
        ];

        $suggestionPrompt = 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan rawat inap di rumah sakit kami?';
        $demographics = $this->getDemographicOptions();
        $scales = $this->getScales();

        return view('questionnaire.show', compact(
            'type', 'title', 'badge', 'badgeIcon', 'actionRoute', 'questions',
            'suggestionPrompt', 'isSuggestionRequired', 'demographics', 'scales'
        ));
    }

    /**
     * Memproses submit kuesioner Rawat Inap.
     */
    public function storeRawatInap(StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        return $this->handleStore('rawat-inap', $request);
    }

    /**
     * Eksekutor pemrosesan submit ke QuestionnaireService.
     */
    protected function handleStore(string $type, StoreQuestionnaireRequest $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validated();
        $result = $this->questionnaireService->sendQuestionnaire($type, $validatedData);

        if ($request->expectsJson() || $request->ajax()) {
            if ($result['success'] ?? false) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                ]);
            }

            $status = match ($result['http_code'] ?? 503) { 422 => 422, 429 => 429, default => 503 };
            $headers = isset($result['retry_after']) ? ['Retry-After' => (string) $result['retry_after']] : [];
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? __('Gagal memproses kuesioner Anda.'),
                'errors' => $result['errors'] ?? [],
            ], $status, $headers);
        }

        if ($result['success'] ?? false) {
            return back()->with([
                'questionnaire_success' => true,
                'questionnaire_message' => $result['message'],
            ]);
        }

        return back()
            ->withInput()
            ->withErrors($result['errors'] ?? [])
            ->with('questionnaire_error', $result['message'] ?? __('Terjadi kesalahan saat memproses data Anda. Silakan coba lagi.'));
    }
}
