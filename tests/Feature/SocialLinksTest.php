<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
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
            'database.connections.sqlsrv.host' => '',
            'database.connections.sqlsrv.database' => '',
            'cache.default' => 'array',
        ]);

        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_social_links_render_threads_and_all_channels_across_footer_top_bar_and_mobile_offcanvas(): void
    {
        config([
            'services.social' => [
                'instagram' => '#',
                'facebook' => '#',
                'youtube' => '#',
                'threads' => '#',
            ],
        ]);

        Http::fake([
            'https://medin.invalid/medinfrasapi/rsses/api/reference/master/lst_spc' => Http::response([
                'Data' => json_encode([
                    ['Type' => 'lst_spc^SPECIALTY', 'Code' => 'KSM-19', 'Name' => 'KESEHATAN ANAK'],
                ]),
            ], 200),
        ]);

        $response = $this->get('/dokter');
        $response->assertOk();

        $html = $response->getContent();

        // Footer, Desktop Top Bar, dan Mobile Offcanvas masing-masing merender 1 tombol Threads (total 3)
        $this->assertSame(3, substr_count($html, 'class="btn-social instagram"'));
        $this->assertSame(3, substr_count($html, 'class="btn-social facebook"'));
        $this->assertSame(3, substr_count($html, 'class="btn-social youtube"'));
        $this->assertSame(3, substr_count($html, 'class="btn-social threads"'));
        $this->assertSame(3, substr_count($html, 'fa-brands fa-threads'));
        $this->assertSame(3, substr_count($html, 'aria-label="Threads"'));
        $this->assertStringContainsString('class="offcanvas-social"', $html);

        // Fallback "#" tidak boleh membuka tab baru
        $this->assertDoesNotMatchRegularExpression('/href="#"[^>]*target="_blank"/', $html);
    }

    public function test_configured_social_urls_render_with_safe_external_link_attributes(): void
    {
        config([
            'services.social' => [
                'instagram' => 'https://www.instagram.com/rselisabethsemarang/',
                'facebook' => 'https://www.facebook.com/rselisabethsemarang/',
                'youtube' => 'https://www.youtube.com/@rselisabethsemarang',
                'threads' => 'https://www.threads.net/@rselisabethsemarang',
            ],
        ]);

        $footerHtml = view('components.footer')->render();
        $navbarHtml = view('components.navbar')->render();

        $this->assertStringContainsString('href="https://www.threads.net/@rselisabethsemarang"', $footerHtml);
        $this->assertStringContainsString('target="_blank" rel="noopener noreferrer"', $footerHtml);
        $this->assertSame(2, substr_count($navbarHtml, 'href="https://www.threads.net/@rselisabethsemarang"'));
        $this->assertSame(2, substr_count($navbarHtml, 'href="https://www.instagram.com/rselisabethsemarang/"'));
    }

    public function test_services_config_sanitizes_invalid_or_unsafe_social_urls_to_hash_fallback(): void
    {
        $envBackup = [
            'HOSPITAL_SOCIAL_INSTAGRAM_URL' => getenv('HOSPITAL_SOCIAL_INSTAGRAM_URL'),
            'HOSPITAL_SOCIAL_FACEBOOK_URL' => getenv('HOSPITAL_SOCIAL_FACEBOOK_URL'),
            'HOSPITAL_SOCIAL_YOUTUBE_URL' => getenv('HOSPITAL_SOCIAL_YOUTUBE_URL'),
            'HOSPITAL_SOCIAL_THREADS_URL' => getenv('HOSPITAL_SOCIAL_THREADS_URL'),
        ];

        try {
            putenv('HOSPITAL_SOCIAL_INSTAGRAM_URL=https://www.instagram.com/rselisabeth/');
            putenv('HOSPITAL_SOCIAL_FACEBOOK_URL=javascript:alert(1)');
            putenv('HOSPITAL_SOCIAL_YOUTUBE_URL=not-a-valid-url');
            putenv('HOSPITAL_SOCIAL_THREADS_URL=  https://www.threads.net/@rselisabeth  ');

            $services = require base_path('config/services.php');

            $this->assertSame('https://www.instagram.com/rselisabeth/', $services['social']['instagram']);
            $this->assertSame('#', $services['social']['facebook']);
            $this->assertSame('#', $services['social']['youtube']);
            $this->assertSame('https://www.threads.net/@rselisabeth', $services['social']['threads']);
        } finally {
            foreach ($envBackup as $key => $value) {
                if ($value === false) {
                    putenv($key);
                } else {
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}
