<?php

namespace Tests\Unit;

use App\Services\RegOnlinePoliReferenceService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RegOnlinePoliReferenceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'services.regonline.poli_db_connection' => 'sqlsrv',
            'services.regonline.poli_cache_ttl' => 300,
            'database.connections.sqlsrv.host' => '',
            'database.connections.sqlsrv.database' => '',
        ]);

        Cache::flush();
    }

    public function test_get_poli_references_returns_cached_list_and_active_codes_derive_only_active(): void
    {
        $cachedReferences = [
            ['code' => 'PL-02', 'name' => 'KLINIK BEDAH', 'is_active' => true],
            ['code' => 'PL-27', 'name' => 'KLINIK UMUM', 'is_active' => false],
            ['code' => 'PL-31', 'name' => 'KLINIK ELISABETH BEDAH', 'is_active' => true],
        ];

        Cache::put(RegOnlinePoliReferenceService::LIST_CACHE_KEY, $cachedReferences, 300);

        $service = new RegOnlinePoliReferenceService();

        $this->assertSame($cachedReferences, $service->getPoliReferences());
        $this->assertSame(['PL-02', 'PL-31'], $service->getActivePoliCodes());
    }

    public function test_get_poli_references_falls_back_to_permanent_cache_when_sql_server_unconfigured(): void
    {
        $fallbackReferences = [
            ['code' => 'PL-22', 'name' => 'KLINIK TELEKONSULTASI', 'is_active' => true],
            ['code' => 'PM-03', 'name' => 'INSTALASI RADIOLOGI', 'is_active' => false],
        ];

        Cache::forever(RegOnlinePoliReferenceService::LIST_FALLBACK_CACHE_KEY, $fallbackReferences);

        $service = new RegOnlinePoliReferenceService();

        $this->assertSame($fallbackReferences, $service->getPoliReferences());
        $this->assertSame(['PL-22'], $service->getActivePoliCodes());
    }

    public function test_get_active_poli_codes_falls_back_to_legacy_code_cache_when_list_cache_missing(): void
    {
        Cache::forever(RegOnlinePoliReferenceService::FALLBACK_CACHE_KEY, ['PL-01', 'PL-31']);

        $service = new RegOnlinePoliReferenceService();

        $this->assertNull($service->getPoliReferences());
        $this->assertSame(['PL-01', 'PL-31'], $service->getActivePoliCodes());
    }

    public function test_get_poli_references_returns_null_when_unconfigured_and_no_fallback(): void
    {
        $service = new RegOnlinePoliReferenceService();

        $this->assertNull($service->getPoliReferences());
        $this->assertNull($service->getActivePoliCodes());
    }

    public function test_get_poli_references_recovers_from_fallback_when_db_query_throws(): void
    {
        config([
            'services.regonline.poli_db_connection' => 'nonexistent_readonly_conn',
            'database.connections.nonexistent_readonly_conn' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'host' => '127.0.0.1',
            ],
        ]);

        $fallbackReferences = [
            ['code' => 'PL-31', 'name' => 'KLINIK ELISABETH BEDAH', 'is_active' => true],
            ['code' => 'PL-27', 'name' => 'KLINIK UMUM', 'is_active' => false],
        ];
        Cache::forever(RegOnlinePoliReferenceService::LIST_FALLBACK_CACHE_KEY, $fallbackReferences);

        $service = new RegOnlinePoliReferenceService();

        // Missing dbo.ref_poli_online on sqlite throws; service must recover safely from fallback
        $this->assertSame($fallbackReferences, $service->getPoliReferences(true));
        $this->assertSame(['PL-31'], $service->getActivePoliCodes());
    }

    public function test_get_active_poli_codes_does_not_retry_failing_db_twice_in_same_request(): void
    {
        config([
            'services.regonline.poli_db_connection' => 'failing_readonly_conn',
            'database.connections.failing_readonly_conn' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'host' => '127.0.0.1',
            ],
        ]);

        Log::spy();

        $service = new RegOnlinePoliReferenceService();

        $this->assertNull($service->getPoliReferences());
        $this->assertNull($service->getActivePoliCodes());

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_force_refresh_active_poli_codes_derives_from_fallback_instead_of_stale_active_cache(): void
    {
        config([
            'services.regonline.poli_db_connection' => 'failing_readonly_conn',
            'database.connections.failing_readonly_conn' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'host' => '127.0.0.1',
            ],
        ]);

        Cache::put(RegOnlinePoliReferenceService::CACHE_KEY, ['OLD-99'], 300);
        Cache::forever(RegOnlinePoliReferenceService::LIST_FALLBACK_CACHE_KEY, [
            ['code' => 'PL-31', 'name' => 'KLINIK ELISABETH BEDAH', 'is_active' => true],
            ['code' => 'PL-27', 'name' => 'KLINIK UMUM', 'is_active' => false],
        ]);

        $service = new RegOnlinePoliReferenceService();

        $this->assertSame(['PL-31'], $service->getActivePoliCodes(true));
    }
}
