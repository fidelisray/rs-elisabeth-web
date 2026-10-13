<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCmsWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * Memvalidasi otentisitas sinyal webhook dari Headless CMS
     * menggunakan header X-Cons-ID, X-Timestamp, dan X-Signature (HMAC SHA-256).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah fitur webhook diaktifkan
        if (!config('cms_api.webhook.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'CMS Webhook integration is currently disabled.',
            ], 403);
        }

        $consId = (string) $request->header('X-Cons-ID', '');
        $timestamp = (string) $request->header('X-Timestamp', '');
        $signature = (string) $request->header('X-Signature', '');

        // 2. Validasi keberadaan seluruh header keamanan yang diwajibkan
        if (empty($consId) || empty($timestamp) || empty($signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required security headers (X-Cons-ID, X-Timestamp, X-Signature).',
            ], 401);
        }

        $expectedConsId = (string) config('cms_api.webhook.cons_id', '');
        $secretKey = (string) config('cms_api.webhook.secret_key', '');

        // 3. Validasi konfigurasi secret key pada server
        if (empty($secretKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook secret key is not configured on the receiver.',
            ], 500);
        }

        // 4. Validasi Consumer ID
        if (!empty($expectedConsId) && !hash_equals($expectedConsId, $consId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Consumer ID (X-Cons-ID).',
            ], 403);
        }

        // 5. Validasi toleransi timestamp (Replay Attack Prevention)
        $tolerance = (int) config('cms_api.webhook.tolerance_seconds', 300);
        $currentTime = time();
        $requestTime = (int) $timestamp;

        if (!is_numeric($timestamp) || abs($currentTime - $requestTime) > $tolerance) {
            return response()->json([
                'success' => false,
                'message' => 'Request timestamp expired or out of tolerance window.',
            ], 401);
        }

        // 6. Validasi HMAC SHA-256 Signature
        // Signature WAJIB mengikat isi body (timestamp + consId + raw request content)
        // agar mencegah replay-attack: signature lama tidak dapat dipakai ulang
        // dengan body yang berbeda dalam jendela toleransi timestamp.
        $contentRaw = hash_hmac('sha256', $timestamp . $consId . $request->getContent(), $secretKey, true);
        $expectedSignature = base64_encode($contentRaw);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid HMAC signature (X-Signature).',
            ], 403);
        }

        return $next($request);
    }
}
