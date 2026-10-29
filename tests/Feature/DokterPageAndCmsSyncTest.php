<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DokterPageAndCmsSyncTest extends TestCase
{
    protected string $webhookConsId = 'WEB-FRONTEND';

    protected string $webhookSecretKey = 'test-webhook-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'rsapi.enabled' => true,
            'rsapi.base_url' => 'https://medin.invalid',
            'rsapi.medin_endpoint' => '/medinfrasapi/rsses',
            'rsapi.medin_consid' => 'MEDIN-CONS',
            'rsapi.medin_secretkey' => 'medin-secret',
            'rsapi.cache_ttl.specialty' => 3600,
            'cms_api.base_url' => 'https://cms.invalid/api/v1/cms',
            'cms_api.cons_id' => 'TEST-READER',
            'cms_api.secret_key' => 'test-secret',
            'cms_api.webhook.enabled' => true,
            'cms_api.webhook.cons_id' => $this->webhookConsId,
            'cms_api.webhook.secret_key' => $this->webhookSecretKey,
            'cms_api.webhook.tolerance_seconds' => 300,
            'cache.default' => 'array',
        ]);

        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_dokter_page_fetches_specialties_from_medin_when_cache_is_empty(): void
    {
        Http::fake([
            'https://medin.invalid/medinfrasapi/rsses/api/reference/master/lst_spc' => Http::response([
                'Data' => json_encode([
                    ['Type' => 'lst_spc^SPECIALTY', 'Code' => 'KSM-19', 'Name' => 'KESEHATAN ANAK'],
                    ['Type' => 'lst_spc^SPECIALTY', 'Code' => 'KSM-28', 'Name' => 'PENYAKIT DALAM'],
                ]),
            ], 200),
        ]);

        $this->assertFalse(Cache::has('specialty_list_'));

        $response = $this->get('/dokter');

        $response->assertOk()
            ->assertSee('data-code="KSM-19"', false)
            ->assertSee('Kesehatan Anak')
            ->assertSee('data-code="KSM-28"', false)
            ->assertSee('Penyakit Dalam');

        $this->assertTrue(Cache::has('specialty_list_'));
        $this->assertTrue(Cache::has('specialty_list_fallback'));
    }

    public function test_dokter_page_recovers_specialties_from_fallback_cache_when_medin_fails(): void
    {
        Cache::forever('specialty_list_fallback', [
            (object) ['Type' => 'lst_spc^SPECIALTY', 'Code' => 'KSM-29', 'Name' => 'PENYAKIT JANTUNG DAN PEMBULUH DARAH'],
        ]);

        Http::fake([
            'https://medin.invalid/medinfrasapi/rsses/api/reference/master/lst_spc' => Http::response([], 500),
        ]);

        $response = $this->get('/dokter');

        $response->assertOk()
            ->assertSee('data-code="KSM-29"', false)
            ->assertSee('Penyakit Jantung Dan Pembuluh Darah');
    }

    public function test_all_dokter_filters_out_doctors_marked_inactive_in_cms(): void
    {
        Cache::put('all_doctors_list', [
            [
                'ParamedicCode' => '0047160191',
                'ParamedicName' => 'dr. Emijati Soepojo, Sp.A',
                'SpecialityCode' => 'KSM-19',
                'SpecialtyName' => 'KESEHATAN ANAK',
                'ServiceUnitCode' => 'PL-01',
                'ServiceUnitName' => 'KLINIK ANAK',
                'DayNumber' => 1,
                'OperationalTimeName' => '08:00 - 12:00',
            ],
            [
                'ParamedicCode' => '0011223344',
                'ParamedicName' => 'dr. Budi Santoso, Sp.A',
                'SpecialityCode' => 'KSM-19',
                'SpecialtyName' => 'KESEHATAN ANAK',
                'ServiceUnitCode' => 'PL-01',
                'ServiceUnitName' => 'KLINIK ANAK',
                'DayNumber' => 1,
                'OperationalTimeName' => '12:00 - 16:00',
            ],
        ], 3600);

        Http::fake([
            'https://cms.invalid/api/v1/cms/doctors*' => Http::response([
                'data' => [
                    [
                        'paramedic_code' => '0011223344',
                        'name' => 'dr. Budi Santoso, Sp.A',
                        'specialty_code' => 'KSM-19',
                        'specialty_name' => 'KESEHATAN ANAK',
                        'is_active' => true,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/dokter/all-dokter', ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.ParamedicCode', '0011223344')
            ->assertJsonMissing(['ParamedicCode' => '0047160191']);
    }

    public function test_cms_webhook_invalidates_doctor_cache_and_updates_active_doctor_list_immediately(): void
    {
        Cache::put('all_doctors_list', [
            [
                'ParamedicCode' => 'DOC-1',
                'ParamedicName' => 'dr. Satu',
                'SpecialityCode' => 'KSM-19',
                'DayNumber' => 1,
            ],
            [
                'ParamedicCode' => 'DOC-2',
                'ParamedicName' => 'dr. Dua',
                'SpecialityCode' => 'KSM-19',
                'DayNumber' => 1,
            ],
        ], 3600);

        Http::fake([
            'https://cms.invalid/api/v1/cms/doctors*' => Http::sequence()
                ->push([
                    'data' => [
                        ['paramedic_code' => 'DOC-1', 'name' => 'dr. Satu', 'is_active' => true],
                        ['paramedic_code' => 'DOC-2', 'name' => 'dr. Dua', 'is_active' => true],
                    ],
                ], 200)
                ->push([
                    'data' => [
                        ['paramedic_code' => 'DOC-1', 'name' => 'dr. Satu', 'is_active' => true],
                    ],
                ], 200),
        ]);

        // Request 1: kedua dokter aktif
        $this->getJson('/dokter/all-dokter', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonCount(2);

        // Admin CMS mematikan toggle is_active pada DOC-2 -> CMS mengirim webhook doctors
        $body = ['module' => 'doctors', 'event' => 'content.updated'];
        $timestamp = (string) time();
        $signature = base64_encode(hash_hmac(
            'sha256',
            $timestamp.$this->webhookConsId.json_encode($body),
            $this->webhookSecretKey,
            true
        ));

        $this->postJson('/api/internal/webhook/cache-invalidate', $body, [
            'X-Cons-ID' => $this->webhookConsId,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
        ])->assertOk();

        // Request 2: DOC-2 langsung hilang tanpa menunggu TTL habis
        $this->getJson('/dokter/all-dokter', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.ParamedicCode', 'DOC-1');
    }

    public function test_all_dokter_fails_open_to_medin_schedules_when_cms_is_unreachable(): void
    {
        Cache::put('all_doctors_list', [
            [
                'ParamedicCode' => 'DOC-1',
                'ParamedicName' => 'dr. Satu',
                'SpecialityCode' => 'KSM-19',
                'DayNumber' => 1,
            ],
        ], 3600);

        Http::fake([
            'https://cms.invalid/api/v1/cms/doctors*' => Http::response([], 503),
        ]);

        $this->getJson('/dokter/all-dokter', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.ParamedicCode', 'DOC-1');
    }
}

