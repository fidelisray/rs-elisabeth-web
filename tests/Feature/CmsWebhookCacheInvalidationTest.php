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
     */
    protected function generateHmacHeaders(?int $customTimestamp = null, ?string $customConsId = null, ?string $customSecret = null): array
    {
        $timestamp = (string) ($customTimestamp ?? time());
        $consId = $customConsId ?? $this->consId;
        $secret = $customSecret ?? $this->secretKey;

        $rawSignature = hash_hmac('sha256', $timestamp . $consId, $secret, true);
        $signature = base64_encode($rawSignature);

        return [
            'X-Cons-ID'    => $consId,
            'X-Timestamp'  => $timestamp,
            'X-Signature'  => $signature,
            'Accept'       => 'application/json',
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
        $headers = $this->generateHmacHeaders(customConsId: 'MALICIOUS-CLIENT');

        $response = $this->postJson($this->endpoint, ['module' => 'news'], $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid Consumer ID (X-Cons-ID).',
            ]);
    }

    public function test_rejects_request_with_expired_timestamp(): void
    {
        // Timestamp 10 menit yang lalu (melebihi toleransi 300 detik)
        $expiredTime = time() - 600;
        $headers = $this->generateHmacHeaders(customTimestamp: $expiredTime);

        $response = $this->postJson($this->endpoint, ['module' => 'news'], $headers);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Request timestamp expired or out of tolerance window.',
            ]);
    }

    public function test_rejects_request_with_tampered_signature(): void
    {
        $headers = $this->generateHmacHeaders();
        $headers['X-Signature'] = base64_encode('invalid-fake-signature');

        $response = $this->postJson($this->endpoint, ['module' => 'news'], $headers);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid HMAC signature (X-Signature).',
            ]);
    }

    public function test_rejects_when_webhook_is_disabled(): void
    {
        Config::set('cms_api.webhook.enabled', false);

        $headers = $this->generateHmacHeaders();
        $response = $this->postJson($this->endpoint, ['module' => 'news'], $headers);

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

        $headers = $this->generateHmacHeaders();
        $response = $this->postJson($this->endpoint, [
            'module' => 'news',
            'event'  => 'content.updated',
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'module' => 'news',
                    'event'  => 'content.updated',
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

        $headers = $this->generateHmacHeaders();
        $response = $this->postJson($this->endpoint, [
            'module' => 'all',
            'event'  => 'cache.flush_all',
        ], $headers);

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

    public function test_rejects_invalid_module_name(): void
    {
        $headers = $this->generateHmacHeaders();
        $response = $this->postJson($this->endpoint, [
            'module' => 'unsupported_module_xyz',
        ], $headers);

        $response->assertStatus(422);
    }
}
