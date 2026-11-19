<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\SubmitDynamicFormWebRequest;
use App\Services\FeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    protected FeedbackService $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Menampilkan halaman formulir Kritik & Saran (Feedback Pelanggan).
     */
    public function index(Request $request): View|Response
    {
        $legacySlug = config('cms_api.legacy_form_slugs.feedback');
        if (is_string($legacySlug) && trim($legacySlug) !== '') {
            return app(DynamicFormController::class)->renderSlug(
                request: $request,
                slug: trim($legacySlug),
                source: 'web_public',
                submitUrl: route('feedback.store'),
                uploadUrl: route('dynamic-forms.upload', ['slug' => trim($legacySlug)]),
                breadcrumbs: [
                    ['label' => __('Beranda'), 'url' => url('/')],
                    ['label' => __('Kritik & Saran')],
                ]
            );
        }

        $relations = [
            'Diri Sendiri',
            'Orang Tua',
            'Suami/Istri',
            'Saudara Kandung',
            'Anak Kandung',
            'Lainnya',
        ];

        $categories = [
            'Pelayanan Medis (Tindakan medis, penegakan diagnosis, penundaan pelayanan, dll)',
            'Pelayanan Non Medis (Prosedur pendaftaran, pembayaran, klaim asuransi, dll)',
            'Fasilitas & Sarana Prasarana',
            'Tarif dan biaya pemeriksaan',
            'Etika dan perilaku petugas',
            'Lainnya',
        ];

        return view('feedback.index', compact('relations', 'categories'));
    }

    /**
     * Memproses pengiriman formulir kritik dan saran pelanggan.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $legacySlug = config('cms_api.legacy_form_slugs.feedback');
        if (is_string($legacySlug) && trim($legacySlug) !== '') {
            $dynamicRequest = app(SubmitDynamicFormWebRequest::class);

            return app(DynamicFormController::class)->submitSlug(
                $dynamicRequest,
                trim($legacySlug),
                forcedSource: 'web_public'
            );
        }

        $feedbackRequest = app(StoreFeedbackRequest::class);
        $validatedData = $feedbackRequest->validated();

        $result = $this->feedbackService->sendFeedback($validatedData);

        // Dukungan respons JSON jika formulir disubmit via AJAX / Fetch
        if ($request->expectsJson() || $request->ajax()) {
            if ($result['success'] ?? false) {
                return response()->json([
                    'success'       => true,
                    'ticket_number' => $result['ticket_number'] ?? null,
                    'message'       => $result['message'] ?? __('Keluhan Anda berhasil dikirimkan.'),
                ]);
            }

            $status = match ($result['http_code'] ?? 503) { 422 => 422, 429 => 429, default => 503 };
            $headers = isset($result['retry_after']) ? ['Retry-After' => (string) $result['retry_after']] : [];
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? __('Gagal memproses formulir keluhan Anda.'),
                'errors' => $result['errors'] ?? [],
            ], $status, $headers);
        }

        // Respons standar HTTP Redirect dengan Session Flash
        if ($result['success'] ?? false) {
            return redirect()
                ->route('feedback.index')
                ->with('feedback_success', true)
                ->with('ticket_number', $result['ticket_number'] ?? null)
                ->with('feedback_message', $result['message'] ?? null);
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors($result['errors'] ?? [])
            ->with('feedback_error', $result['message'] ?? __('Gagal memproses formulir keluhan Anda. Silakan coba kembali.'));
    }
}
