<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocalizedSlugRedirectTest extends TestCase
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

    public function test_room_detail_redirects_to_english_alternate_slug_when_locale_switches_to_en(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/room-facilities/kamar-vip?lang=en' => Http::response([], 404),
            'https://cms.invalid/api/v1/cms/room-facilities/kamar-vip?lang=id' => Http::response([
                'data' => [
                    'id' => 1,
                    'name' => 'Kamar VIP',
                    'slug' => 'kamar-vip',
                    'alternate_slugs' => [
                        'id' => 'kamar-vip',
                        'en' => 'vip-room',
                    ],
                ],
            ], 200),
        ]);

        $this->withSession(['locale' => 'en'])
            ->get('/ruang-perawatan/kamar-vip')
            ->assertRedirect(route('ruang-perawatan.show', ['slug' => 'vip-room']));
    }

    public function test_news_detail_redirects_to_english_alternate_slug_when_locale_switches_to_en(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/news/berita-kesehatan?lang=en' => Http::response([], 404),
            'https://cms.invalid/api/v1/cms/news/berita-kesehatan?lang=id' => Http::response([
                'data' => [
                    'id' => 1,
                    'title' => 'Berita Kesehatan',
                    'slug' => 'berita-kesehatan',
                    'alternate_slugs' => [
                        'id' => 'berita-kesehatan',
                        'en' => 'health-news',
                    ],
                ],
            ], 200),
        ]);

        $this->withSession(['locale' => 'en'])
            ->get('/news/berita-kesehatan')
            ->assertRedirect(route('news.show', ['slug' => 'health-news']));
    }

    public function test_article_detail_redirects_to_english_alternate_slug_when_locale_switches_to_en(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/articles/artikel-jantung?lang=en' => Http::response([], 404),
            'https://cms.invalid/api/v1/cms/articles/artikel-jantung?lang=id' => Http::response([
                'data' => [
                    'id' => 1,
                    'title' => 'Artikel Jantung',
                    'slug' => 'artikel-jantung',
                    'alternate_slugs' => [
                        'id' => 'artikel-jantung',
                        'en' => 'heart-article',
                    ],
                ],
            ], 200),
        ]);

        $this->withSession(['locale' => 'en'])
            ->get('/articles/artikel-jantung')
            ->assertRedirect(route('articles.show', ['slug' => 'heart-article']));
    }
}
