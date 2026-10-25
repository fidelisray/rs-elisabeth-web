<?php

namespace App\Services;

use App\Support\QuestionnaireDefinition;
use App\Support\PatientFormErrors;
/**
 * Mengirim kuesioner kepuasan pasien ke CMS API.
 *
 * Tidak ada fallback mock: kegagalan CMS dilaporkan ke pengguna, bukan dianggap berhasil.
 */
class QuestionnaireService
{
    protected CmsApiService $cmsApi;

    public function __construct(?CmsApiService $cmsApi = null)
    {
        $this->cmsApi = $cmsApi ?? app(CmsApiService::class);
    }

    /**
     * @param  string  $type  igd | rawat-jalan | rawat-inap (format URL, sama dengan CMS)
     * @param  array  $data  Data tervalidasi dari StoreQuestionnaireRequest
     * @return array{success: bool, message: string}
     */
    public function sendQuestionnaire(string $type, array $data): array
    {
        $demographics = [
            'age_group' => $data['age_group'] ?? '',
            'gender' => $data['gender'] ?? '',
            'payment_method' => $data['payment_method'] ?? '',
        ];

        // Allowlist per layanan: hidden fields atau metadata tidak menjadi jawaban.
        $ratings = array_intersect_key($data, QuestionnaireDefinition::QUESTIONS[$type] ?? []);

        $payload = [
            'questionnaire_type' => $type,
            'demographics' => $demographics,
            'ratings' => $ratings,
            'suggestions' => $data['suggestions'] ?? null,
            'locale' => app()->getLocale(),
            'source' => 'qr_barcode',
            'submitted_at' => now()->toDateTimeString(),
        ];

        $response = $this->cmsApi->submitQuestionnaire($type, $payload);

        if (($response['status'] ?? '') === 'success') {
            return [
                'success' => true,
                'message' => __('Terima kasih atas partisipasi dan penilaian Anda. Setiap masukan sangat berharga dalam membantu kami meningkatkan mutu pelayanan di RS St. Elisabeth Semarang.'),
            ];
        }

        return [
            'success' => false,
            'http_code' => $response['http_code'] ?? 503,
            'errors' => PatientFormErrors::map((array) ($response['errors'] ?? []), $type),
            'retry_after' => $response['retry_after'] ?? null,
            'message' => $response['message'] ?? __('Terjadi kesalahan saat memproses data kuesioner Anda. Silakan coba beberapa saat lagi.'),
        ];
    }
}
