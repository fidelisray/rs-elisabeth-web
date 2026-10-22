<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class QuestionnaireService
{
    protected CmsApiService $cmsApi;

    public function __construct(?CmsApiService $cmsApi = null)
    {
        $this->cmsApi = $cmsApi ?? app(CmsApiService::class);
    }

    /**
     * Memproses pengiriman kuesioner kepuasan pasien ke CMS API,
     * dilengkapi strategi Mock / Logging Fallback jika endpoint CMS belum aktif.
     *
     * @param string $type Jenis kuesioner ('igd', 'rawat_jalan', 'rawat_inap')
     * @param array $data Data tervalidasi dari StoreQuestionnaireRequest
     * @return array
     */
    public function sendQuestionnaire(string $type, array $data): array
    {
        // Pisahkan demografi dan saran dari data rating
        $demographics = [
            'age_group'      => $data['age_group'] ?? '',
            'gender'         => $data['gender'] ?? '',
            'payment_method' => $data['payment_method'] ?? '',
        ];
        $suggestions = $data['suggestions'] ?? null;

        // Ambil semua respon rating selain field khusus
        $ratings = array_diff_key($data, array_flip([
            'age_group',
            'gender',
            'payment_method',
            'suggestions',
            'hp_website',
            '_token',
        ]));

        $payload = [
            'questionnaire_type' => $type,
            'demographics'       => $demographics,
            'ratings'            => $ratings,
            'suggestions'        => $suggestions,
            'locale'             => app()->getLocale(),
            'source'             => 'qr_barcode',
            'submitted_at'       => now()->toDateTimeString(),
        ];

        // 1. Cek apakah environment memaksakan mock mode
        $forceMock = filter_var(env('CMS_QUESTIONNAIRE_MOCK', false), FILTER_VALIDATE_BOOLEAN);

        if ($forceMock) {
            return $this->handleMockFallback($type, $payload, 'Environment CMS_QUESTIONNAIRE_MOCK aktif');
        }

        // 2. Coba kirimkan ke endpoint resmi CMS API via CmsApiService
        $response = $this->cmsApi->submitQuestionnaire($type, $payload);

        // 3. Jika CMS sudah siap dan merespons sukses (200 / 201)
        if (($response['status'] ?? '') === 'success') {
            return [
                'success' => true,
                'is_mock' => false,
                'message' => __('Terima kasih atas partisipasi dan penilaian Anda. Setiap masukan sangat berharga dalam membantu kami meningkatkan mutu pelayanan di RS St. Elisabeth Semarang.'),
            ];
        }

        // 4. Jika CMS belum memiliki endpoint (404 Not Found), connection timeout, atau server CMS belum siap
        $httpCode = $response['http_code'] ?? 500;
        if ($httpCode === 404 || ($response['status'] ?? '') === 'exception') {
            $reason = ($httpCode === 404)
                ? 'Endpoint CMS belum tersedia (404)'
                : 'Server CMS sedang tidak dapat dijangkau (Connection Timeout/Offline)';

            return $this->handleMockFallback($type, $payload, $reason);
        }

        // 5. Jika ada error spesifik lain dari CMS (misal 422 Unprocessable Content)
        return [
            'success' => false,
            'message' => $response['message'] ?? __('Terjadi kesalahan saat memproses data kuesioner Anda. Silakan coba beberapa saat lagi.'),
        ];
    }

    /**
     * Menangani fallback ketika modul CMS belum siap atau belum memiliki endpoint.
     * Mencatat payload ke storage/logs/laravel.log dan mengembalikan respons sukses ramah pengguna.
     */
    protected function handleMockFallback(string $type, array $payload, string $reason): array
    {
        Log::info("[MOCK FALLBACK] Kuesioner Kepuasan Pasien ({$type}) berhasil diterima dan dicatat ke log lokal.", [
            'type'         => $type,
            'reason'       => $reason,
            'payload'      => $payload,
            'client_ip'    => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'recorded_at'  => now()->toIso8601String(),
        ]);

        return [
            'success' => true,
            'is_mock' => true,
            'message' => __('Terima kasih atas partisipasi dan penilaian Anda. Setiap masukan sangat berharga dalam membantu kami meningkatkan mutu pelayanan di RS St. Elisabeth Semarang.'),
        ];
    }
}
