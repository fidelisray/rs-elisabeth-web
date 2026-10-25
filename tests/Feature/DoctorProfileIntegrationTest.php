<?php

namespace Tests\Feature;

use App\Exceptions\CmsApiException;
use App\Services\CmsApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Disiapkan offline, BELUM DIJALANKAN. Semua HTTP menggunakan fake. */
class DoctorProfileIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'cms_api.base_url' => 'https://cms.invalid/api/v1/cms',
            'cms_api.cons_id' => 'TEST-READER',
            'cms_api.secret_key' => 'test-secret',
            'cache.default' => 'array',
        ]);
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_profile_proxy_uses_paramedic_code_and_effective_language(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/doctors/DOC-1*' => Http::response(['data' => $this->profile()], 200)]);
        $this->getJson('/dokter/profil/DOC-1?lang=en')->assertOk()
            ->assertJsonPath('data.paramedic_code', 'DOC-1')
            ->assertJsonPath('data.education', null);
        Http::assertSent(function ($request) {
            $timestamp = $request->header('X-Timestamp')[0];
            $signature = base64_encode(hash_hmac('sha256', $timestamp.'TEST-READER', 'test-secret', true));
            return str_contains($request->url(), 'lang=en') && $request->hasHeader('X-Signature', $signature);
        });
    }

    public function test_missing_profile_and_upstream_failure_are_different_states(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/doctors/*' => Http::sequence()->push([], 404)->push([], 500)]);
        $this->getJson('/dokter/profil/DOC-1')->assertNotFound();
        $this->getJson('/dokter/profil/DOC-2')->assertStatus(503);
    }

    public function test_http_failures_are_not_cached_as_a_missing_profile(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/doctors/*' => Http::sequence()->push([], 500)->push(['data' => $this->profile()], 200)]);
        $service = app(CmsApiService::class);
        try {
            $service->getDoctorByCode('DOC-1');
            $this->fail('Kegagalan CMS harus dibedakan dari 404.');
        } catch (CmsApiException) {
            $this->assertSame('DOC-1', $service->getDoctorByCode('DOC-1')['paramedic_code']);
        }
        Http::assertSentCount(2);
    }

    public function test_doctor_cache_without_tags_is_invalidated_without_losing_news_cache(): void
    {
        config(['cache.default' => 'file']);
        app()->setLocale('id');
        Http::fake([
            'https://cms.invalid/api/v1/cms/doctors/*' => Http::sequence()->push(['data' => $this->profile()], 200)
                ->push(['data' => [...$this->profile(), 'bio' => 'Bio baru']], 200),
            'https://cms.invalid/api/v1/cms/news*' => Http::response(['data' => [['title' => 'Berita']]], 200),
        ]);
        $service = app(CmsApiService::class);
        $service->invalidateCache('all');
        $this->assertSame('Bio awal', $service->getDoctorByCode('DOC-1')['bio']);
        $service->getNews();
        $this->assertSame('success', $service->invalidateCache('doctors')['status']);
        $this->assertSame('Bio baru', $service->getDoctorByCode('DOC-1')['bio']);
        $this->assertSame('Berita', $service->getNews()[0]['title']);
        Http::assertSentCount(3);
    }

    public function test_locale_cache_and_specialty_filters_do_not_mix(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/doctors*' => Http::response(['data' => [$this->profile()]], 200)]);
        $service = app(CmsApiService::class);
        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);
            $service->getDoctors(['specialty' => 'SP-A']);
            $service->getDoctors(['specialty' => 'SP-B']);
            $service->getDoctors(['specialty' => 'SP-A']);
        }
        Http::assertSentCount(4);
    }

    private function profile(): array
    {
        return ['paramedic_code' => 'DOC-1', 'name' => 'Dokter Uji', 'bio' => 'Bio awal', 'education' => null, 'is_active' => true, 'is_profile_complete' => false];
    }
}
