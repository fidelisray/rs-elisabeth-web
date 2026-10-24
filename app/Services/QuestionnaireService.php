<?php

namespace App\Services;

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

        // Semua field selain demografi, saran, dan kontrol formulir adalah jawaban rating.
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
            'message' => __('Terjadi kesalahan saat memproses data kuesioner Anda. Silakan coba beberapa saat lagi.'),
        ];
    }
}
