<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CmsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CmsWebhookController extends Controller
{
    /**
     * Endpoint internal webhook untuk menerima sinyal invalidasi cache dari CMS.
     * Mengosongkan tag cache Redis secara instan saat data diubah di CMS.
     */
    public function invalidate(Request $request, CmsApiService $cmsApiService): JsonResponse
    {
        $validated = $request->validate([
            'module' => 'nullable|string|in:all,news,articles,promotions,facilities,room_facilities,banner_promotions,doctors',
            'event' => 'nullable|string|max:50',
            // 'slug' disediakan sebagai metadata tambahan untuk keperluan logging/audit
            // di masa mendatang. Saat ini invalidasi bersifat granular per-modul
            // (cache tags), sehingga slug tidak dipakai untuk menargetkan satu entri.
            // Pertahankan field ini agar payload CMS tetap kompatibel ke depan.
            'slug' => 'nullable|string|max:255',
            'timestamp' => 'nullable|numeric',
        ]);

        $module = $validated['module'] ?? 'all';
        $event = $validated['event'] ?? 'content.updated';

        Log::info("CMS Webhook Received - Event: {$event}, Module: {$module}", [
            'ip' => $request->ip(),
            'client' => $request->header('X-Cons-ID'),
            // Metadata tambahan: slug entitas yang berubah (jika dikirim oleh CMS).
            'slug' => $validated['slug'] ?? null,
        ]);

        $result = $cmsApiService->invalidateCache($module);

        if ($result['status'] === 'unknown_module') {
            return response()->json([
                'success' => false,
                'message' => "Modul '{$module}' tidak dikenali untuk invalidasi cache.",
                'data' => null,
            ], 422);
        }

        // Driver cache tidak mendukung tags (misal file/database) → cache TIDAK terhapus.
        // Jangan balas 200 agar pengirim tidak keliru mengira cache sudah dibersihkan.
        if ($result['status'] === 'driver_unsupported_tags') {
            return response()->json([
                'success' => false,
                'message' => 'Invalidasi cache tidak dapat dilakukan: driver cache aktif tidak mendukung cache tags.',
                'data' => null,
            ], 501);
        }

        return response()->json([
            'success' => true,
            'message' => "Cache CMS berhasil dibatalkan untuk modul: {$module}.",
            'data' => [
                'module' => $module,
                'event' => $event,
                'tags_flushed' => $result['tags'],
                'driver_status' => $result['status'],
                'timestamp' => time(),
            ],
        ], 200);
    }
}
