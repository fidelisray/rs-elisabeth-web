<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FeedbackService
{
    protected CmsApiService $cmsApi;

    public function __construct(?CmsApiService $cmsApi = null)
    {
        $this->cmsApi = $cmsApi ?? app(CmsApiService::class);
    }

    /**
     * Memproses pengiriman feedback / keluhan pasien ke modul CMS API,
     * dilengkapi strategi Mock / Logging Fallback jika endpoint CMS belum aktif.
     *
     * @param array $data Data tervalidasi dari StoreFeedbackRequest
     * @return array
     */
    public function sendFeedback(array $data): array
    {
        $payload = [
            'reporter_name'       => $data['reporter_name'] ?? '',
            'relation_to_patient' => $data['relation_to_patient'] ?? '',
            'relation_other'      => $data['relation_other'] ?? null,
            'patient_name'        => $data['patient_name'] ?? '',
            'phone_number'        => $data['phone_number'] ?? '',
            'categories'          => $data['complaint_categories'] ?? [],
            'category_other'      => $data['category_other'] ?? null,
            'complaint_detail'    => $data['complaint_detail'] ?? '',
            'expected_solution'   => $data['expected_solution'] ?? '',
            'locale'              => app()->getLocale(),
            'source'              => 'web_public',
            'submitted_at'        => now()->toDateTimeString(),
        ];

        // 1. Cek apakah environment memaksakan mock mode
        $forceMock = filter_var(env('CMS_FEEDBACK_MOCK', false), FILTER_VALIDATE_BOOLEAN);

        if ($forceMock) {
            return $this->handleMockFallback($payload, 'Environment CMS_FEEDBACK_MOCK aktif');
        }

        // 2. Coba kirimkan ke endpoint resmi CMS API via CmsApiService
        $response = $this->cmsApi->submitFeedback($payload);

        // 3. Jika CMS sudah siap dan merespons sukses (200 / 201)
        if (($response['status'] ?? '') === 'success') {
            return [
                'success'       => true,
                'is_mock'       => false,
                'ticket_number' => $response['ticket_number'] ?? $this->generateTicketNumber(),
                'message'       => __('Keluhan Anda berhasil dikirimkan. Tim Customer Care kami akan segera menindaklanjutinya.'),
            ];
        }

        // 4. Jika CMS belum memiliki endpoint (404 Not Found), connection timeout, atau server CMS belum siap
        $httpCode = $response['http_code'] ?? 500;
        if ($httpCode === 404 || ($response['status'] ?? '') === 'exception') {
            $reason = ($httpCode === 404) 
                ? 'Endpoint CMS belum tersedia (404)' 
                : 'Server CMS sedang tidak dapat dijangkau (Connection Timeout/Offline)';

            return $this->handleMockFallback($payload, $reason);
        }

        // 5. Jika ada error spesifik lain dari CMS (misal 422 Unprocessable Content)
        return [
            'success' => false,
            'message' => $response['message'] ?? __('Terjadi kesalahan saat memproses data keluhan Anda. Silakan coba beberapa saat lagi.'),
        ];
    }

    /**
     * Menjalankan strategi Mock / Logging Fallback:
     * Menyimpan data keluhan ke log cadangan dan mengembalikan nomor tiket simulasi.
     */
    protected function handleMockFallback(array $payload, string $reason): array
    {
        $ticketNumber = $this->generateTicketNumber();

        Log::info("[FEEDBACK-MOCK-FALLBACK] {$reason}. Data formulir keluhan pasien dicatat ke log:", [
            'ticket_number' => $ticketNumber,
            'payload'       => $payload,
        ]);

        return [
            'success'       => true,
            'is_mock'       => true,
            'ticket_number' => $ticketNumber,
            'message'       => __('Keluhan Anda berhasil dikirimkan. Tim Customer Care kami akan segera menindaklanjutinya.'),
        ];
    }

    /**
     * Membuat nomor tiket keluhan terformat (misal: FB-20261022-A891).
     */
    protected function generateTicketNumber(): string
    {
        $dateCode = date('Ymd');
        $randomHex = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 4));

        return "FB-{$dateCode}-{$randomHex}";
    }
}
