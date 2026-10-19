<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Services\CmsApiService;
use App\Services\DoctorApiService;
use Mockery;
use Tests\TestCase;

class HomeNewsDisplayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_all_home_section_limits_are_exactly_three(): void
    {
        $this->assertSame(3, HomeController::HOME_NEWS_LIMIT);
        $this->assertSame(3, HomeController::HOME_ARTICLES_LIMIT);
        $this->assertSame(3, HomeController::HOME_PROMOTIONS_LIMIT);
    }

    public function test_home_page_displays_maximum_three_news_cards(): void
    {
        // Siapkan 7 dummy berita seperti response dari CMS
        $mockNews = [];
        for ($i = 1; $i <= 7; $i++) {
            $mockNews[] = [
                'id'           => $i,
                'title'        => "Berita Pengujian {$i}",
                'slug'         => "berita-pengujian-{$i}",
                'category'     => 'Kesehatan',
                'author'       => 'Humas RS',
                'excerpt'      => "Ringkasan berita ke-{$i} untuk pengujian.",
                'content'      => "<p>Konten lengkap berita ke-{$i}</p>",
                'image_path'   => "/images/news{$i}.jpg",
                'image_url'    => "https://example.com/news{$i}.jpg",
                'is_published' => true,
                'created_at'   => '2026-10-19 10:00:00',
            ];
        }

        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('getArticles')->andReturn([]);
        $mockCms->shouldReceive('getNews')->andReturn($mockNews);
        $mockCms->shouldReceive('getPromotions')->andReturn([]);
        $mockCms->shouldReceive('getBannerPromotions')->andReturn([]);
        $mockCms->shouldReceive('getFacilities')->andReturn([]);

        $mockDoctor = Mockery::mock(DoctorApiService::class);
        $mockDoctor->shouldReceive('getDaftarSpesialisasi')->andReturn([]);

        $this->app->instance(CmsApiService::class, $mockCms);
        $this->app->instance(DoctorApiService::class, $mockDoctor);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('latestNews', function ($latestNews) {
            return count($latestNews) === 3;
        });

        // Pastikan berita 1, 2, 3 muncul
        $response->assertSee('Berita Pengujian 1');
        $response->assertSee('Berita Pengujian 2');
        $response->assertSee('Berita Pengujian 3');

        // Pastikan berita 4 tidak ikut dirender di home
        $response->assertDontSee('Berita Pengujian 4');

        // Pastikan class grid 3-kolom ada
        $response->assertSee('col-md-6 col-lg-4');
    }

    public function test_home_page_displays_maximum_three_articles(): void
    {
        $mockArticles = [];
        for ($i = 1; $i <= 6; $i++) {
            $mockArticles[] = [
                'id'         => $i,
                'title'      => "Artikel Kesehatan {$i}",
                'slug'       => "artikel-kesehatan-{$i}",
                'excerpt'    => "Ringkasan artikel {$i}.",
                'content'    => "<p>Konten artikel {$i}.</p>",
                'image_path' => "/images/art{$i}.jpg",
                'image_url'  => "https://example.com/art{$i}.jpg",
                'tags'       => 'Penyakit Dalam',
                'author'     => 'dr. Elisabeth',
                'views'      => 10,
                'created_at' => '2026-10-19 10:00:00',
            ];
        }

        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('getArticles')->andReturn($mockArticles);
        $mockCms->shouldReceive('getNews')->andReturn([]);
        $mockCms->shouldReceive('getPromotions')->andReturn([]);
        $mockCms->shouldReceive('getBannerPromotions')->andReturn([]);
        $mockCms->shouldReceive('getFacilities')->andReturn([]);

        $mockDoctor = Mockery::mock(DoctorApiService::class);
        $mockDoctor->shouldReceive('getDaftarSpesialisasi')->andReturn([]);

        $this->app->instance(CmsApiService::class, $mockCms);
        $this->app->instance(DoctorApiService::class, $mockDoctor);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('latestArticles', function ($articles) {
            return count($articles) === 3;
        });

        $response->assertSee('Artikel Kesehatan 1');
        $response->assertSee('Artikel Kesehatan 2');
        $response->assertSee('Artikel Kesehatan 3');
        $response->assertDontSee('Artikel Kesehatan 4');
    }

    public function test_home_page_displays_maximum_three_promotions(): void
    {
        $mockPromos = [];
        for ($i = 1; $i <= 6; $i++) {
            $mockPromos[] = [
                'id'          => $i,
                'title'       => "Paket Promo {$i}",
                'slug'        => "paket-promo-{$i}",
                'excerpt'     => "Ringkasan promo {$i}.",
                'description' => "Deskripsi promo {$i}.",
                'image_path'  => "/images/promo{$i}.jpg",
                'image_url'   => "https://example.com/promo{$i}.jpg",
                'is_active'   => true,
            ];
        }

        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('getArticles')->andReturn([]);
        $mockCms->shouldReceive('getNews')->andReturn([]);
        $mockCms->shouldReceive('getPromotions')->andReturn($mockPromos);
        $mockCms->shouldReceive('getBannerPromotions')->andReturn([]);
        $mockCms->shouldReceive('getFacilities')->andReturn([]);

        $mockDoctor = Mockery::mock(DoctorApiService::class);
        $mockDoctor->shouldReceive('getDaftarSpesialisasi')->andReturn([]);

        $this->app->instance(CmsApiService::class, $mockCms);
        $this->app->instance(DoctorApiService::class, $mockDoctor);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('promotions', function ($promos) {
            return count($promos) === 3;
        });

        $response->assertSee('Paket Promo 1');
        $response->assertSee('Paket Promo 2');
        $response->assertSee('Paket Promo 3');
        $response->assertDontSee('Paket Promo 4');

        // Pastikan modal trigger terpasang pada semua kartu yang ditampilkan
        $response->assertSee('data-bs-target="#promoModal"', false);
    }

    public function test_home_page_handles_empty_news_gracefully(): void
    {
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('getArticles')->andReturn([]);
        $mockCms->shouldReceive('getNews')->andReturn([]);
        $mockCms->shouldReceive('getPromotions')->andReturn([]);
        $mockCms->shouldReceive('getBannerPromotions')->andReturn([]);
        $mockCms->shouldReceive('getFacilities')->andReturn([]);

        $mockDoctor = Mockery::mock(DoctorApiService::class);
        $mockDoctor->shouldReceive('getDaftarSpesialisasi')->andReturn([]);

        $this->app->instance(CmsApiService::class, $mockCms);
        $this->app->instance(DoctorApiService::class, $mockDoctor);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Berita Belum Tersedia');
        $response->assertSee('Artikel Belum Tersedia');
        $response->assertSee('Belum Ada Paket & Promo');
    }

    public function test_home_page_handles_missing_slug_safely(): void
    {
        $mockNews = [
            [
                'id'           => 1,
                'title'        => 'Berita Tanpa Slug',
                'slug'         => null,
                'category'     => null,
                'excerpt'      => 'Ringkasan aman.',
                'image_url'    => null,
                'is_published' => true,
                'created_at'   => null,
            ]
        ];

        $mockArticles = [
            [
                'id'         => 1,
                'title'      => 'Artikel Tanpa Slug',
                'slug'       => null,
                'tags'       => null,
                'author'     => null,
                'excerpt'    => 'Ringkasan aman.',
                'content'    => null,
                'image_url'  => null,
                'views'      => 0,
                'created_at' => null,
            ]
        ];

        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('getArticles')->andReturn($mockArticles);
        $mockCms->shouldReceive('getNews')->andReturn($mockNews);
        $mockCms->shouldReceive('getPromotions')->andReturn([]);
        $mockCms->shouldReceive('getBannerPromotions')->andReturn([]);
        $mockCms->shouldReceive('getFacilities')->andReturn([]);

        $mockDoctor = Mockery::mock(DoctorApiService::class);
        $mockDoctor->shouldReceive('getDaftarSpesialisasi')->andReturn([]);

        $this->app->instance(CmsApiService::class, $mockCms);
        $this->app->instance(DoctorApiService::class, $mockDoctor);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Berita Tanpa Slug');
        $response->assertSee('Artikel Tanpa Slug');
        $response->assertSee(route('news.index'));
        $response->assertSee(route('articles.index'));
    }
}
