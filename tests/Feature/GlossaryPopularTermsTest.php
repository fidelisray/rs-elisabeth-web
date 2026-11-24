<?php

namespace Tests\Feature;

use App\Services\GlossaryPopularityService;
use App\Services\HospitalApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GlossaryPopularTermsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Strictly isolate from the local PostgreSQL testing database.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'glossary.pinned_slugs' => [],
            'glossary.allowed_referer_hosts' => [],
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $migration = include database_path('migrations/2026_11_23_000000_create_glossary_term_daily_stats_table.php');
        $migration->up();

        Cache::flush();
    }

    public function test_cold_start_displays_14_validated_curated_terms_and_interactive_preview_strip(): void
    {
        $apiService = app(HospitalApiService::class);
        $curatedSlugs = (array) config('glossary.curated_fallback_slugs', []);
        $this->assertCount(22, $curatedSlugs);

        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);
            $items = $apiService->getLocalGlossary();
            $slugMap = array_fill_keys(array_map(fn ($i) => strtolower($i['slug']), $items), true);

            foreach ($curatedSlugs as $slug) {
                $this->assertArrayHasKey($slug, $slugMap, "Curated slug '{$slug}' missing in locale '{$locale}' JSON");
            }
        }

        app()->setLocale('id');

        $response = $this->get('/glosarium');
        $response->assertStatus(200);
        $response->assertSee('Istilah yang Sering Dicari');
        $response->assertSee('Istilah medis pilihan yang sering dibutuhkan pasien');
        $response->assertSee('Intisari Istilah');
        $response->assertSee('id="popularTermPreview"', false);
        $response->assertSee('#1');
        $response->assertSee('#2');
        $response->assertSee('#3');
        $response->assertDontSee('Sedang Tren');

        $popularTerms = $response->viewData('popularTerms');
        $popularMeta = $response->viewData('popularMeta');

        $this->assertCount(14, $popularTerms);
        $this->assertSame('curated', $popularMeta['mode']);
        $this->assertNotEmpty($popularTerms[0]['lead_excerpt']);
    }

    public function test_hybrid_first_party_src_parameter_and_referer_fallback_attribution(): void
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36';

        // 1. Explicit ?src= takes priority even when Referer has a zero-result search query (?q=xyz)
        $this->withHeaders([
            'User-Agent' => $ua,
            'Referer' => 'http://localhost/glosarium?q=unknowndisease',
        ])->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->get('/glosarium/diabetes?src=pop')
            ->assertStatus(200);

        $this->withHeaders(['User-Agent' => $ua])
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->get('/glosarium/diabetes?src=search')
            ->assertStatus(200);

        $this->withHeaders(['User-Agent' => $ua])
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->get('/glosarium/diabetes?src=dir')
            ->assertStatus(200);

        $this->withHeaders(['User-Agent' => $ua])
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.4'])
            ->get('/glosarium/diabetes?src=rel')
            ->assertStatus(200);

        $diabetesRow = DB::table('glossary_term_daily_stats')->where('slug', 'diabetes')->first();
        $this->assertNotNull($diabetesRow);
        $this->assertSame(1, (int) $diabetesRow->popular_clicks);
        $this->assertSame(1, (int) $diabetesRow->search_clicks);
        $this->assertSame(1, (int) $diabetesRow->directory_views);
        $this->assertSame(1, (int) $diabetesRow->internal_nav_views);
        $this->assertSame(0, (int) $diabetesRow->direct_views);

        // 2. Fallback to Referer when ?src= is omitted (including custom reverse-proxy host)
        config(['glossary.allowed_referer_hosts' => ['kamus.rs-elisabeth.test']]);

        $this->withHeaders([
            'User-Agent' => $ua,
            'Referer' => 'https://kamus.rs-elisabeth.test/glosarium?q=stroke',
        ])->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        $this->withHeaders([
            'User-Agent' => $ua,
            'Referer' => 'http://localhost/glosarium?letter=S',
        ])->withServerVariables(['REMOTE_ADDR' => '10.0.0.6'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        $this->withHeaders([
            'User-Agent' => $ua,
            'Referer' => 'http://localhost/glosarium/heart-attack',
        ])->withServerVariables(['REMOTE_ADDR' => '10.0.0.7'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        $this->withHeaders([
            'User-Agent' => $ua,
            'Referer' => 'https://www.google.com/search?q=stroke',
        ])->withServerVariables(['REMOTE_ADDR' => '10.0.0.8'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        $strokeRow = DB::table('glossary_term_daily_stats')->where('slug', 'stroke')->first();
        $this->assertNotNull($strokeRow);
        $this->assertSame(1, (int) $strokeRow->search_clicks);
        $this->assertSame(1, (int) $strokeRow->directory_views);
        $this->assertSame(1, (int) $strokeRow->internal_nav_views);
        $this->assertSame(1, (int) $strokeRow->direct_views);
    }

    public function test_anti_abuse_filters_bots_prefetch_head_self_referer_and_15_min_dedupe(): void
    {
        $humanUa = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Version/17.0 Safari/605.1.15';

        // 1. Bots and crawlers are ignored
        foreach (['Googlebot/2.1 (+http://www.google.com/bot.html)', 'curl/8.4.0', 'Mozilla/5.0 HeadlessChrome/120.0'] as $botUa) {
            $this->withHeaders(['User-Agent' => $botUa])
                ->withServerVariables(['REMOTE_ADDR' => '10.1.0.1'])
                ->get('/glosarium/asthma?src=search')
                ->assertStatus(200);
        }

        // 2. Browser prefetch/prerender requests are ignored
        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Sec-Purpose' => 'prefetch;prerender',
        ])->withServerVariables(['REMOTE_ADDR' => '10.1.0.2'])
            ->get('/glosarium/asthma?src=search')
            ->assertStatus(200);

        // 3. HEAD requests are ignored
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.1.0.3'])
            ->head('/glosarium/asthma?src=search')
            ->assertStatus(200);

        // 4. Self-referer (e.g., switching language ID/EN while already on /glosarium/asthma) is ignored
        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => 'http://localhost/glosarium/asthma',
        ])->withServerVariables(['REMOTE_ADDR' => '10.1.0.4'])
            ->get('/glosarium/asthma?src=search')
            ->assertStatus(200);

        $this->assertSame(0, DB::table('glossary_term_daily_stats')->where('slug', 'asthma')->count());

        // 5. 15-minute deduplication and canonical lowercase slug normalization
        $this->flushHeaders();
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.1.0.10'])
            ->get('/glosarium/Asthma?src=search')
            ->assertStatus(200);

        $this->flushHeaders();
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.1.0.10'])
            ->get('/glosarium/ASTHMA?src=dir')
            ->assertStatus(200);

        $asthmaRow = DB::table('glossary_term_daily_stats')->where('slug', 'asthma')->first();
        $this->assertNotNull($asthmaRow);
        $this->assertSame(1, (int) $asthmaRow->search_clicks);
        $this->assertSame(0, (int) $asthmaRow->directory_views);

        // 6. Invalid 404 slug never writes to stats table
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.1.0.11'])
            ->get('/glosarium/non-existent-term-9999?src=search')
            ->assertStatus(404);

        $this->assertSame(0, DB::table('glossary_term_daily_stats')->where('slug', 'non-existent-term-9999')->count());
    }

    public function test_cold_start_promotion_gate_exponential_decay_and_editorial_pins(): void
    {
        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();
        $popularityService = app(GlossaryPopularityService::class);
        $allItems = app(HospitalApiService::class)->getLocalGlossary();

        // 1. Only 2 organic hits (below min_organic_hits = 3) + 50 popular_clicks -> must NOT qualify
        DB::table('glossary_term_daily_stats')->insert([
            'slug' => 'cholera',
            'stat_date' => $today->toDateString(),
            'search_clicks' => 2,
            'directory_views' => 0,
            'direct_views' => 0,
            'internal_nav_views' => 0,
            'popular_clicks' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $popularityService->clearRankingCache();
        $initial = $popularityService->getPopularTerms($allItems);
        $this->assertSame('curated', $initial['meta']['mode']);
        $this->assertNotContains('cholera', array_column($initial['terms'], 'slug'));

        // 2. Adding 1 more organic search click brings cholera to organic_hits = 3, score_30d = 19.0 -> qualifies!
        DB::table('glossary_term_daily_stats')
            ->where('slug', 'cholera')
            ->update(['search_clicks' => 3]);

        // 3. Test exponential time decay (H = 10 days):
        // 'malaria' today: 4 search clicks = 12.0 * (0.5^0) = 12.0
        // 'glaucoma' 20 days ago: 6 search clicks = 18.0 * (0.5^(20/10)) = 18.0 * 0.25 = 4.5
        DB::table('glossary_term_daily_stats')->insert([
            [
                'slug' => 'malaria',
                'stat_date' => $today->toDateString(),
                'search_clicks' => 4,
                'directory_views' => 0,
                'direct_views' => 0,
                'internal_nav_views' => 0,
                'popular_clicks' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'glaucoma',
                'stat_date' => $today->copy()->subDays(20)->toDateString(),
                'search_clicks' => 6,
                'directory_views' => 0,
                'direct_views' => 0,
                'internal_nav_views' => 0,
                'popular_clicks' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $popularityService->clearRankingCache();
        $ranked = $popularityService->getPopularTerms($allItems);

        $this->assertSame('hybrid', $ranked['meta']['mode']);
        $this->assertSame(3, $ranked['meta']['qualified_count']);
        $topSlugs = array_slice(array_column($ranked['terms'], 'slug'), 0, 3);
        $this->assertSame(['cholera', 'malaria', 'glaucoma'], $topSlugs);

        // 4. Editorial pins take precedence at the very top
        config(['glossary.pinned_slugs' => ['vertigo']]);
        $popularityService->clearRankingCache();
        $withPin = $popularityService->getPopularTerms($allItems);

        $this->assertSame('vertigo', $withPin['terms'][0]['slug']);
        $this->assertTrue($withPin['terms'][0]['is_pinned']);
        $this->assertSame('cholera', $withPin['terms'][1]['slug']);
    }

    public function test_laplace_smoothed_velocity_trending_badges_capped_at_top_3(): void
    {
        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();
        $popularityService = app(GlossaryPopularityService::class);
        $allItems = app(HospitalApiService::class)->getLocalGlossary();

        // 4 surging terms in last 7 days (velocity >= 1.5, score_7d >= 6.0)
        $surgingSlugs = [
            'dengue-fever' => 6,   // score_7d = 18.0, velocity = (18+5)/5 = 4.60x
            'appendicitis' => 5,   // score_7d = 15.0, velocity = (15+5)/5 = 4.00x
            'gastritis' => 4,      // score_7d = 12.0, velocity = (12+5)/5 = 3.40x
            'typhoid-fever' => 3,  // score_7d = 9.0,  velocity = (9+5)/5  = 2.80x
        ];

        foreach ($surgingSlugs as $slug => $searchCount) {
            DB::table('glossary_term_daily_stats')->insert([
                'slug' => $slug,
                'stat_date' => $today->toDateString(),
                'search_clicks' => $searchCount,
                'directory_views' => 0,
                'direct_views' => 0,
                'internal_nav_views' => 0,
                'popular_clicks' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1 high-volume but steady term (same traffic this week and last week -> velocity = 1.0x -> not trending)
        DB::table('glossary_term_daily_stats')->insert([
            [
                'slug' => 'diabetes',
                'stat_date' => $today->toDateString(),
                'search_clicks' => 5,
                'directory_views' => 0,
                'direct_views' => 0,
                'internal_nav_views' => 0,
                'popular_clicks' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'diabetes',
                'stat_date' => $today->copy()->subDays(9)->toDateString(),
                'search_clicks' => 5,
                'directory_views' => 0,
                'direct_views' => 0,
                'internal_nav_views' => 0,
                'popular_clicks' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $popularityService->clearRankingCache();
        $result = $popularityService->getPopularTerms($allItems);

        $trendingSlugs = array_values(array_map(
            fn ($t) => $t['slug'],
            array_filter($result['terms'], fn ($t) => $t['is_trending'] === true)
        ));

        // Capped at max 3 trending badges, ordered by highest velocity; steady 'diabetes' excluded
        $this->assertCount(3, $trendingSlugs);
        $this->assertSame(['dengue-fever', 'appendicitis', 'gastritis'], $trendingSlugs);
        $this->assertNotContains('diabetes', $trendingSlugs);

        $response = $this->get('/glosarium');
        $response->assertStatus(200);
        $response->assertSee('Berdasarkan pencarian 30 hari terakhir');
        $response->assertSee('Sedang Tren');
    }

    public function test_search_relevance_highlight_and_zero_result_recovery(): void
    {
        // 1. Search for "diabetes" highlights "Diabetes" at #1 in topRelevantTerms instead of burying under letter A
        $searchResponse = $this->get('/glosarium?q=diabetes');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Hasil Paling Relevan');

        $topRelevant = $searchResponse->viewData('topRelevantTerms');
        $this->assertNotEmpty($topRelevant);
        $this->assertSame('diabetes', strtolower($topRelevant[0]['slug']));
        $this->assertStringContainsString('src=search', $searchResponse->getContent());

        // 2. Zero-result search shows Top 6 Popular Terms suggestions with src=pop
        $emptyResponse = $this->get('/glosarium?q=xyznonexistentmedicalterm999');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Istilah yang Sering Dicari Pasien:');

        $suggestedTerms = $emptyResponse->viewData('popularTerms');
        $this->assertCount(6, $suggestedTerms);
        $this->assertStringContainsString('src=pop', $emptyResponse->getContent());
    }

    public function test_artisan_cli_commands_stats_simulate_reset_and_prune(): void
    {
        $this->artisan('glossary:stats --simulate')
            ->assertExitCode(0);

        $this->assertGreaterThan(0, DB::table('glossary_term_daily_stats')->count());

        // Insert an old row (120 days ago) and verify glossary:prune-stats --days=90 deletes only the old row
        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $oldDate = Carbon::now($tz)->startOfDay()->subDays(120)->toDateString();

        DB::table('glossary_term_daily_stats')->insert([
            'slug' => 'old-term',
            'stat_date' => $oldDate,
            'search_clicks' => 3,
            'directory_views' => 0,
            'direct_views' => 0,
            'internal_nav_views' => 0,
            'popular_clicks' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('glossary:prune-stats --days=90')
            ->assertExitCode(0);

        $this->assertSame(0, DB::table('glossary_term_daily_stats')->where('slug', 'old-term')->count());
        $this->assertGreaterThan(0, DB::table('glossary_term_daily_stats')->count());

        $this->artisan('glossary:stats --reset')
            ->assertExitCode(0);

        $this->assertSame(0, DB::table('glossary_term_daily_stats')->count());
    }

    public function test_fail_open_db_resilience_cookie_session_dedupe_and_production_cli_guard(): void
    {
        $humanUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0.0.0 Safari/537.36';
        $sessionCookie = (string) config('session.cookie', 'laravel_session');

        // 1. Two visitors behind the same NAT IP + User-Agent but with distinct session cookies both count
        $this->flushHeaders();
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.9.0.1'])
            ->withCookie($sessionCookie, 'sess-user-alpha')
            ->withSession(['_token' => 'alpha'])
            ->get('/glosarium/anemia?src=search')
            ->assertStatus(200);

        // Flush session ID for second user behind same NAT IP
        $this->app['session']->driver()->setId(str_repeat('b', 40));
        $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.9.0.1'])
            ->withCookie($sessionCookie, 'sess-user-beta')
            ->withSession(['_token' => 'beta'])
            ->get('/glosarium/anemia?src=search')
            ->assertStatus(200);

        $anemiaRow = DB::table('glossary_term_daily_stats')->where('slug', 'anemia')->first();
        $this->assertNotNull($anemiaRow);
        $this->assertSame(2, (int) $anemiaRow->search_clicks);

        // 2. Production guard blocks --simulate and --reset
        $originalEnv = $this->app['env'];
        try {
            $this->app['env'] = 'production';

            $this->artisan('glossary:stats --simulate')
                ->assertExitCode(1);

            $this->artisan('glossary:stats --reset')
                ->assertExitCode(1);
        } finally {
            $this->app['env'] = $originalEnv;
        }

        $this->assertSame(2, (int) DB::table('glossary_term_daily_stats')->where('slug', 'anemia')->value('search_clicks'));

        // 3. Fail-open fallback if glossary_term_daily_stats table is unavailable
        \Illuminate\Support\Facades\Schema::dropIfExists('glossary_term_daily_stats');
        app(GlossaryPopularityService::class)->clearRankingCache();

        $exploreRes = $this->get('/glosarium');
        $exploreRes->assertStatus(200);
        $this->assertCount(14, $exploreRes->viewData('popularTerms'));
        $this->assertSame('curated', $exploreRes->viewData('popularMeta')['mode']);

        $detailRes = $this->withHeaders(['User-Agent' => $humanUa])
            ->withServerVariables(['REMOTE_ADDR' => '10.9.0.99'])
            ->get('/glosarium/diabetes?src=search');
        $detailRes->assertStatus(200);

        // 4. Verify transient DB outage did NOT poison Cache::flexible with a 1-hour 'curated' entry:
        // Re-create the table and insert a qualified term WITHOUT manually clearing the cache.
        $migration = include database_path('migrations/2026_11_23_000000_create_glossary_term_daily_stats_table.php');
        $migration->up();

        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        DB::table('glossary_term_daily_stats')->insert([
            'slug' => 'dengue-fever',
            'stat_date' => Carbon::now($tz)->toDateString(),
            'search_clicks' => 4,
            'directory_views' => 0,
            'direct_views' => 0,
            'internal_nav_views' => 0,
            'popular_clicks' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recoveredRes = $this->get('/glosarium');
        $recoveredRes->assertStatus(200);
        $this->assertSame('hybrid', $recoveredRes->viewData('popularMeta')['mode']);
        $this->assertSame('dengue-fever', $recoveredRes->viewData('popularTerms')[0]['slug']);
    }

    public function test_malformed_referers_array_query_params_slug_search_and_unqualified_pin_trending_guard(): void
    {
        $humanUa = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0.0.0 Safari/537.36';

        // 1. Array query parameters (?q[]=, ?letter[]=, ?src[]=) and array Referer queries must not throw TypeError / ErrorException
        $this->get('/glosarium?q[]=diabetes&letter[]=A')
            ->assertStatus(200);

        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => 'http://localhost/glosarium?q[]=diabetes&letter[]=D',
        ])->withServerVariables(['REMOTE_ADDR' => '10.20.0.1'])
            ->get('/glosarium/diabetes?src[]=search')
            ->assertStatus(200);

        // 2. Non-HTTP scheme Referer (mailto:) and malformed Referer URLs must not spoof internal self-referer
        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => 'mailto:/glosarium/stroke',
        ])->withServerVariables(['REMOTE_ADDR' => '10.20.0.2'])
            ->get('/glosarium/stroke?src=search')
            ->assertStatus(200);

        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => 'http:///:::invalid-url',
        ])->withServerVariables(['REMOTE_ADDR' => '10.20.0.3'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        // 3. Relative internal Referer (/glosarium?q=...) is classified consistently as search_clicks
        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => '/glosarium?q=stroke',
        ])->withServerVariables(['REMOTE_ADDR' => '10.20.0.4'])
            ->get('/glosarium/stroke')
            ->assertStatus(200);

        $strokeRow = DB::table('glossary_term_daily_stats')->where('slug', 'stroke')->first();
        $this->assertNotNull($strokeRow);
        $this->assertSame(2, (int) $strokeRow->search_clicks);
        $this->assertSame(1, (int) $strokeRow->direct_views);

        // 4. Title-based self-referer (e.g., /glosarium/Type%202%20Diabetes switching language) is filtered out
        $this->withHeaders([
            'User-Agent' => $humanUa,
            'Referer' => 'http://localhost/glosarium/Type%202%20Diabetes',
        ])->withServerVariables(['REMOTE_ADDR' => '10.20.0.5'])
            ->get('/glosarium/type-2-diabetes?src=search')
            ->assertStatus(200);

        $this->assertSame(0, DB::table('glossary_term_daily_stats')->where('slug', 'type-2-diabetes')->count());

        // 5. Searching by hyphenated slug (?q=type-2-diabetes) finds the term and ranks it #1 in topRelevantTerms
        $slugSearchRes = $this->get('/glosarium?q=type-2-diabetes');
        $slugSearchRes->assertStatus(200);
        $topRelevant = $slugSearchRes->viewData('topRelevantTerms');
        $this->assertNotEmpty($topRelevant);
        $this->assertSame('type-2-diabetes', strtolower($topRelevant[0]['slug']));

        // 6. Unqualified editorial pin (< 3 organic hits) must not receive "Sedang Tren" badge
        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->toDateString();
        config(['glossary.pinned_slugs' => ['vertigo']]);

        DB::table('glossary_term_daily_stats')->insert([
            'slug' => 'vertigo',
            'stat_date' => $today,
            'search_clicks' => 2, // score_7d = 6.0, velocity = 2.2x, but organic_hits = 2 (< 3)
            'directory_views' => 0,
            'direct_views' => 0,
            'internal_nav_views' => 0,
            'popular_clicks' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $popularityService = app(GlossaryPopularityService::class);
        $popularityService->clearRankingCache();
        $allItems = app(HospitalApiService::class)->getLocalGlossary();
        $popularData = $popularityService->getPopularTerms($allItems);

        $this->assertSame('vertigo', $popularData['terms'][0]['slug']);
        $this->assertTrue($popularData['terms'][0]['is_pinned']);
        $this->assertFalse($popularData['terms'][0]['is_organic']);
        $this->assertFalse($popularData['terms'][0]['is_trending']);
    }
}
