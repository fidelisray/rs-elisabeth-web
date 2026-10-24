<?php

namespace App\Services;

/**
 * Mengirim kritik dan saran pasien ke CMS API.
 *
 * Tidak ada fallback mock. Jika CMS tidak menyimpan data, pasien diberi tahu bahwa
 * pengiriman gagal, sehingga tidak ada keluhan yang dianggap terkirim padahal hilang.
 */
class FeedbackService
{
    protected CmsApiService $cmsApi;

    public function __construct(?CmsApiService $cmsApi = null)
    {
        $this->cmsApi = $cmsApi ?? app(CmsApiService::class);
    }

    /**
     * @param  array  $data  Data tervalidasi dari StoreFeedbackRequest
     * @return array{success: bool, ticket_number?: string, message: string}
     */
    public function sendFeedback(array $data): array
    {
        $payload = [
            'reporter_name' => $data['reporter_name'] ?? '',
            'relation_to_patient' => $data['relation_to_patient'] ?? '',
            'relation_other' => $data['relation_other'] ?? null,
            'patient_name' => $data['patient_name'] ?? '',
            'phone_number' => $data['phone_number'] ?? '',
            'categories' => $data['complaint_categories'] ?? [],
            'category_other' => $data['category_other'] ?? null,
            'complaint_detail' => $data['complaint_detail'] ?? '',
            'expected_solution' => $data['expected_solution'] ?? '',
            'locale' => app()->getLocale(),
            'source' => 'web_public',
            'submitted_at' => now()->toDateTimeString(),
        ];

        $response = $this->cmsApi->submitFeedback($payload);

        if (($response['status'] ?? '') === 'success') {
            return [
                'success' => true,
                'ticket_number' => (string) ($response['ticket_number'] ?? ''),
                'message' => __('Keluhan Anda berhasil dikirimkan. Tim Customer Care kami akan segera menindaklanjutinya.'),
            ];
        }

        return [
            'success' => false,
            'message' => __('Keluhan Anda belum dapat dikirimkan saat ini. Silakan coba beberapa saat lagi.'),
        ];
    }
}
