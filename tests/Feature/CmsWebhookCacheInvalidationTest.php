<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CmsWebhookCacheInvalidationTest extends TestCase
{
    protected string $consId = 'WEB-FRONTEND';

    protected string $secretKey = 'test-secret-key-12345';

    protected string $endpoint = '/api/internal/webhook/cache-invalidate';

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan konfigurasi mock untuk pengujian
        Config::set('cms_api.webhook.enabled', true);
        Config::set('cms_api.webhook.cons_id', $this->consId);
        Config::set('cms_api.webhook.secret_key', $this->secretKey);
        Config::set('cms_api.webhook.tolerance_seconds', 300);
        Config::set('cache.default', 'array');
    }

    /**
     * Helper untuk membuat header HMAC yang valid.
     * Signature menandatangani `timestamp + consId + raw request body`
     * agar konsisten dengan logika verifikasi middleware (anti-replay).
     */
    protected function generateHmacHeaders(array $body = [], ?int $customTimestamp = null, ?string $customConsId = null, ?string $customSecret = null): array
    {
        $timestamp = (string) ($customTimestamp ?? time());
        $consId = $customConsId ?? $this->consId;
        $secret = $customSecret ?? $this->secretKey;

        $rawSignature = hash_hmac('sha256', $timestamp.$consId.json_encode($body), $secret, true);
        $signature = base64_encode($rawSignature);

        return [
            'X-Cons-ID' => $consId,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_rejects_request_without_security_headers(): void
    {
        $response = $this->postJson($this->endpoint, ['module' => 'news']);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_rejects_request_with_invalid_consumer_id(): void
    {
        $body = ['module' => 'news'];
        $headers = $this->generateHmacHeaders($body, customConsId: 'MALICIOUS-CLIENT');

        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid Consumer ID (X-Cons-ID).',
            ]);
    }

    public function test_rejects_request_with_expired_timestamp(): void
    {
        // Timestamp 10 menit yang lalu (melebihi toleransi 300 detik)
        $body = ['module' => 'news'];
        $expiredTime = time() - 600;
        $headers = $this->generateHmacHeaders($body, customTimestamp: $expiredTime);

        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Request timestamp expired or out of tolerance window.',
            ]);
    }

    public function test_rejects_request_with_tampered_signature(): void
    {
        $body = ['module' => 'news'];
        $headers = $this->generateHmacHeaders($body);
        $headers['X-Signature'] = base64_encode('invalid-fake-signature');

        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid HMAC signature (X-Signature).',
            ]);
    }

    public function test_rejects_request_with_mismatched_body_signature(): void
    {
        // Signature dibuat untuk body A, tapi request dikirim dengan body B.
        // Membuktikan signature benar-benar mengikat isi body (anti-replay).
        $signedBody = ['module' => 'news'];
        $sentBody = ['module' => 'all'];

        $headers = $this->generateHmacHeaders($signedBody);

        $response = $this->postJson($this->endpoint, $sentBody, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid HMAC signature (X-Signature).',
            ]);
    }

    public function test_rejects_when_webhook_is_disabled(): void
    {
        Config::set('cms_api.webhook.enabled', false);

        $body = ['module' => 'news'];
        $headers = $this->generateHmacHeaders($body);
        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'CMS Webhook integration is currently disabled.',
            ]);
    }

    public function test_successfully_invalidates_specific_module_cache(): void
    {
        // Simpan cache dummy pada tag cms_news
        Cache::tags(['cms', 'cms_news'])->put('test_news_key', 'old_news_data', 600);
        Cache::tags(['cms', 'cms_articles'])->put('test_article_key', 'article_data', 600);

        $this->assertEquals('old_news_data', Cache::tags(['cms', 'cms_news'])->get('test_news_key'));

        $body = [
            'module' => 'news',
            'event' => 'content.updated',
        ];
        $headers = $this->generateHmacHeaders($body);
        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'module' => 'news',
                    'event' => 'content.updated',
                ],
            ]);

        // Buktikan tag cms_news sudah terhapus, sedangkan tag artikel lain tetap utuh
        $this->assertNull(Cache::tags(['cms', 'cms_news'])->get('test_news_key'));
        $this->assertEquals('article_data', Cache::tags(['cms', 'cms_articles'])->get('test_article_key'));
    }

    public function test_successfully_invalidates_all_modules_cache(): void
    {
        Cache::tags(['cms', 'cms_news'])->put('key_news', 'news_data', 600);
        Cache::tags(['cms', 'cms_promotions'])->put('key_promo', 'promo_data', 600);

        $body = [
            'module' => 'all',
            'event' => 'cache.flush_all',
        ];
        $headers = $this->generateHmacHeaders($body);
        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'module' => 'all',
                ],
            ]);

        // Tag induk 'cms' di-flush, kedua cache harus bersih
        $this->assertNull(Cache::tags(['cms', 'cms_news'])->get('key_news'));
        $this->assertNull(Cache::tags(['cms', 'cms_promotions'])->get('key_promo'));
    }

    public function test_successfully_invalidates_doctors_module_cache(): void
    {
        Cache::tags(['cms', 'cms_doctors'])->put('test_doctor_key', 'old_doctor_data', 600);
        Cache::tags(['cms', 'cms_news'])->put('test_news_key', 'news_data', 600);

        $body = [
            'module' => 'doctors',
            'event' => 'content.updated',
        ];
        $headers = $this->generateHmacHeaders($body);
        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'module' => 'doctors',
                    'event' => 'content.updated',
                ],
            ]);

        $this->assertNull(Cache::tags(['cms', 'cms_doctors'])->get('test_doctor_key'));
        $this->assertEquals('news_data', Cache::tags(['cms', 'cms_news'])->get('test_news_key'));
    }

    public function test_rejects_invalid_module_name(): void
    {
        $body = ['module' => 'unsupported_module_xyz'];
        $headers = $this->generateHmacHeaders($body);
        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(422);
    }

    public function test_versioned_cache_invalidates_without_cache_tags(): void
    {
        // FileStore tidak mendukung tags; webhook mengganti generasi cache modul.
        Config::set('cache.default', 'file');

        $body = ['module' => 'news'];
        $headers = $this->generateHmacHeaders($body);

        $response = $this->postJson($this->endpoint, $body, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
