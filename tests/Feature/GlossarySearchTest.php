<?php

namespace Tests\Feature;

use App\Services\GlossarySearchService;
use App\Services\HospitalApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GlossarySearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_index_search_for_diabetes_returns_only_the_8_title_matches_in_deterministic_order(): void
    {
        $response = $this->withSession(['locale' => 'id'])->get('/glosarium?q=diabetes');
        $response->assertStatus(200);

        $glossary = $response->viewData('glossary');
        $flatItems = [];
        foreach ($glossary as $groupItems) {
            foreach ($groupItems as $item) {
                $flatItems[] = $item;
            }
        }

        $this->assertCount(8, $flatItems);

        foreach ($flatItems as $item) {
            $this->assertStringContainsStringIgnoringCase('diabetes', (string) $item['istilah']);
        }

        $topRelevant = $response->viewData('topRelevantTerms');
        $this->assertCount(6, $topRelevant);
        $this->assertSame([
            'Diabetes',
            'Diabetes Insipidus',
            'Type 1 Diabetes',
            'Type 2 Diabetes',
            'Gestational Diabetes',
            'Type 1 Diabetes In Children',
        ], array_column($topRelevant, 'istilah'));

        $searchService = app(GlossarySearchService::class);
        $allItems = app(HospitalApiService::class)->getLocalGlossary();
        $ranked = $searchService->searchTitleMatches($allItems, 'diabetes');

        $this->assertSame([
            'Diabetes',
            'Diabetes Insipidus',
            'Type 1 Diabetes',
            'Type 2 Diabetes',
            'Gestational Diabetes',
            'Type 1 Diabetes In Children',
            'Type 2 Diabetes In Children',
            'Prediabetes',
        ], array_column($ranked, 'istilah'));
    }

    public function test_description_only_indonesian_keyword_returns_zero_results_with_title_hint_and_popular_terms(): void
    {
        $response = $this->withSession(['locale' => 'id'])->get('/glosarium?q=jantung');
        $response->assertStatus(200);

        $this->assertSame([], $response->viewData('glossary'));
        $this->assertSame([], $response->viewData('topRelevantTerms'));
        $this->assertCount(6, $response->viewData('popularTerms'));
        $response->assertSee('Pencarian mencocokkan nama istilah medis.');
        $response->assertSee('Diabetes, Asthma, Stroke');
        $response->assertSee('Istilah yang Sering Dicari Pasien:');
    }

    public function test_token_based_and_search_matches_words_in_any_order_and_hyphenated_slugs(): void
    {
        $outOfOrder = $this->get('/glosarium?q=diabetes+type+2');
        $outOfOrder->assertStatus(200);

        $topRelevant = $outOfOrder->viewData('topRelevantTerms');
        $this->assertSame([
            'Type 2 Diabetes',
            'Type 2 Diabetes In Children',
        ], array_column($topRelevant, 'istilah'));

        $hyphenSlug = $this->get('/glosarium?q=type-2-diabetes');
        $hyphenSlug->assertStatus(200);

        $slugRelevant = $hyphenSlug->viewData('topRelevantTerms');
        $this->assertSame([
            'Type 2 Diabetes',
            'Type 2 Diabetes In Children',
        ], array_column($slugRelevant, 'istilah'));
    }

    public function test_group_by_prefix_handles_single_letter_leading_words_without_trailing_spaces(): void
    {
        $searchService = app(GlossarySearchService::class);
        $allItems = app(HospitalApiService::class)->getLocalGlossary();

        $lymphomaMatches = $searchService->searchTitleMatches($allItems, 'cell lymphoma');
        $grouped = $searchService->groupByPrefix($lymphomaMatches);

        $this->assertArrayHasKey('B', $grouped);
        $this->assertArrayHasKey('T', $grouped);
        $this->assertArrayNotHasKey('B ', $grouped);
        $this->assertArrayNotHasKey('T ', $grouped);

        $xMatches = $searchService->searchTitleMatches($allItems, 'agammaglobulinemia');
        $xGrouped = $searchService->groupByPrefix($xMatches);
        $this->assertArrayHasKey('X', $xGrouped);
        $this->assertArrayNotHasKey('X ', $xGrouped);
    }

    public function test_autocomplete_endpoint_returns_popular_terms_on_empty_query_with_private_no_store_header(): void
    {
        $response = $this->withSession(['locale' => 'id'])->getJson('/glosarium/cari');
        $response->assertStatus(200);

        $cacheControl = strtolower((string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);

        $response->assertJsonPath('mode', 'popular');
        $response->assertJsonPath('query', '');
        $items = $response->json('items');
        $this->assertIsArray($items);
        $this->assertCount(6, $items);

        foreach ($items as $item) {
            $this->assertArrayHasKey('slug', $item);
            $this->assertArrayHasKey('istilah', $item);
            $this->assertArrayHasKey('letter', $item);
            $this->assertArrayHasKey('excerpt', $item);
            $this->assertArrayHasKey('url', $item);
            $this->assertStringContainsString('?src=pop', (string) $item['url']);
        }
    }

    public function test_autocomplete_endpoint_supports_single_character_and_ranked_search_and_malformed_queries(): void
    {
        // 1. Single character triggers search suggestions (no minlength=2 gate)
        $singleChar = $this->withSession(['locale' => 'id'])->getJson('/glosarium/cari?q=d');
        $singleChar->assertStatus(200);
        $singleChar->assertJsonPath('mode', 'search');
        $singleChar->assertJsonPath('query', 'd');
        $this->assertGreaterThan(0, (int) $singleChar->json('total'));
        $this->assertLessThanOrEqual(8, count($singleChar->json('items')));
        $this->assertStringContainsString('?src=search', (string) $singleChar->json('items.0.url'));

        // 2. Query "diabetes" returns total=8 with Diabetes #1 and Diabetes Insipidus #2
        $diabetes = $this->withSession(['locale' => 'id'])->getJson('/glosarium/cari?q=diabetes');
        $diabetes->assertStatus(200);
        $diabetes->assertJsonPath('mode', 'search');
        $diabetes->assertJsonPath('total', 8);
        $diabetes->assertJsonPath('all_results_url', '/glosarium?q=diabetes');
        $diabetes->assertJsonPath('items.0.istilah', 'Diabetes');
        $diabetes->assertJsonPath('items.0.url', '/glosarium/diabetes?src=search');
        $diabetes->assertJsonPath('items.1.istilah', 'Diabetes Insipidus');
        $diabetes->assertJsonPath('items.7.istilah', 'Prediabetes');

        // 3. Array query parameter ?q[]=diabetes does not cause TypeError or HTTP 500
        $malformedArray = $this->getJson('/glosarium/cari?q[]=diabetes');
        $malformedArray->assertStatus(200);
        $malformedArray->assertJsonPath('mode', 'popular');

        // 4. Hyphen-only and regex-metacharacter queries return clean 0-result JSON
        $hyphens = $this->getJson('/glosarium/cari?q=---');
        $hyphens->assertStatus(200);
        $hyphens->assertJsonPath('mode', 'search');
        $hyphens->assertJsonPath('total', 0);
        $hyphens->assertJsonPath('items', []);

        $regexChars = $this->getJson('/glosarium/cari?q=' . urlencode('[a-z]+(diabetes)*'));
        $regexChars->assertStatus(200);
        $regexChars->assertJsonPath('mode', 'search');
        $regexChars->assertJsonPath('total', 0);
    }

    public function test_index_and_show_pages_render_modern_aria_combobox_widget_and_drop_legacy_dead_code(): void
    {
        $indexRes = $this->withSession(['locale' => 'id'])->get('/glosarium');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('data-glossary-search-widget', false);
        $indexRes->assertSee('role="combobox"', false);
        $indexRes->assertSee('aria-autocomplete="list"', false);
        $indexRes->assertSee('aria-haspopup="listbox"', false);
        $indexRes->assertSee('role="listbox"', false);
        $indexRes->assertSee('class="hero-glossary"', false);
        $indexRes->assertDontSee('minlength="2"', false);
        $indexRes->assertDontSee('btn-outline-success', false);
        $indexRes->assertDontSee('id="resetSearchBtn"', false);
        $indexRes->assertDontSee('id="termDetailModal"', false);
        $indexRes->assertDontSee('id="glosarium-search-result"', false);

        // Verify the hero section itself contains neither legacy green nor red outline search buttons.
        $indexHtml = (string) $indexRes->getContent();
        $this->assertMatchesRegularExpression('/<section id="hero-section"[^>]*>.*?<\/section>/s', $indexHtml);
        preg_match('/<section id="hero-section"[^>]*>.*?<\/section>/s', $indexHtml, $heroMatches);
        $heroHtml = $heroMatches[0] ?? '';
        $this->assertStringNotContainsString('btn-outline-success', $heroHtml);
        $this->assertStringNotContainsString('btn-outline-danger', $heroHtml);

        $showRes = $this->withSession(['locale' => 'en'])->get('/glosarium/diabetes');
        $showRes->assertStatus(200);
        $showRes->assertSee('data-glossary-search-widget', false);
        $showRes->assertSee('id="glossaryDetailSearchInput"', false);
        $showRes->assertSee('role="combobox"', false);
        $showRes->assertSee('Search Another Term');
        $showRes->assertSee('Search medical term titles (e.g. Diabetes, Asthma, Stroke)...');
        $showRes->assertSee('View 1 result for &quot;:query&quot;', false);
        $showRes->assertSee('hero-glossary', false);
        $showRes->assertDontSee('minlength="2"', false);
    }

    public function test_leading_and_trailing_hyphens_preserve_exact_and_prefix_ranking(): void
    {
        $searchService = app(GlossarySearchService::class);
        $allItems = app(HospitalApiService::class)->getLocalGlossary();

        $ranked = $searchService->searchTitleMatches($allItems, '---diabetes---');

        $this->assertCount(8, $ranked);
        $this->assertSame('Diabetes', $ranked[0]['istilah']);
        $this->assertSame(GlossarySearchService::SCORE_EXACT, $ranked[0]['relevance_score']);
        $this->assertSame('Diabetes Insipidus', $ranked[1]['istilah']);
        $this->assertSame(GlossarySearchService::SCORE_PREFIX, $ranked[1]['relevance_score']);
    }

    public function test_show_page_trims_single_letter_sub_prefix_cleanly(): void
    {
        $response = $this->withSession(['locale' => 'id'])->get('/glosarium/x-linked-agammaglobulinemia');
        $response->assertStatus(200);
        $response->assertSee('Sub-Awalan "X"', false);
        $response->assertDontSee('Sub-Awalan "X "', false);
    }

    public function test_autocomplete_route_enforces_throttle_rate_limit(): void
    {
        $route = app('router')->getRoutes()->getByName('glossary.search');
        $this->assertNotNull($route);
        $this->assertContains('throttle:120,1', $route->gatherMiddleware());

        for ($i = 0; $i < 120; $i++) {
            $res = $this->getJson('/glosarium/cari?q=diabetes');
            $res->assertStatus(200);
        }

        $limited = $this->getJson('/glosarium/cari?q=diabetes');
        $limited->assertStatus(429);
    }
}
