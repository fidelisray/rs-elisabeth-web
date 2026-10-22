<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackRequest;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

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
    public function index(): View
    {
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
    public function store(StoreFeedbackRequest $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validated();

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

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? __('Gagal memproses formulir keluhan Anda.'),
            ], 422);
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
            ->with('feedback_error', $result['message'] ?? __('Gagal memproses formulir keluhan Anda. Silakan coba kembali.'));
    }
}
