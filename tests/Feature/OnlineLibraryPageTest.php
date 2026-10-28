<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnlineLibraryPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cms_api.base_url' => 'http://cms.test/api/v1/cms',
            'cms_api.cons_id' => 'web-client',
            'cms_api.secret_key' => 'secret-key',
        ]);
    }

    public function test_index_displays_empty_state_when_api_returns_empty_list(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries*' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $response = $this->get(route('online-library.index'));

        $response->assertStatus(200);
        $response->assertSee('Perpustakaan Online');
        $response->assertSee('Dokumen Perpustakaan Belum Tersedia');
    }

    public function test_index_displays_documents_and_supports_english_translation(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'title' => 'Patient Care Guide',
                        'slug' => 'patient-care-guide',
                        'alternate_slugs' => [
                            'id' => 'panduan-perawatan-pasien',
                            'en' => 'patient-care-guide',
                        ],
                        'category' => 'Health Guide',
                        'description' => '<p>Comprehensive guide for patients and families.</p>',
                        'file_path' => 'online-libraries/guide.pdf',
                        'file_name' => 'patient-care-guide.pdf',
                        'file_size' => 1572864,
                        'file_url' => 'http://cms.test/storage/online-libraries/guide.pdf',
                        'is_published' => true,
                        'created_at' => '2026-10-28T08:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->withSession(['locale' => 'en'])->get(route('online-library.index'));

        $response->assertStatus(200);
        $response->assertSee('Online Library');
        $response->assertSee('Patient Care Guide');
        $response->assertSee('Health Guide');
        $response->assertSee('1.50 MB');
        $response->assertSee('Read & Details');
    }

    public function test_show_displays_document_detail_and_pdf_viewer(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries/panduan-kesehatan*' => Http::response([
                'data' => [
                    'id' => 2,
                    'title' => 'Panduan Kesehatan Jantung',
                    'slug' => 'panduan-kesehatan',
                    'alternate_slugs' => [
                        'id' => 'panduan-kesehatan',
                        'en' => 'heart-health-guide',
                    ],
                    'category' => 'Edukasi Medis',
                    'description' => '<p>Isi panduan lengkap kesehatan jantung Anda.</p>',
                    'file_path' => 'online-libraries/jantung.pdf',
                    'file_name' => 'panduan-jantung.pdf',
                    'file_size' => 512000,
                    'file_url' => 'http://cms.test/storage/online-libraries/jantung.pdf',
                    'is_published' => true,
                    'created_at' => '2026-10-28T08:00:00Z',
                ],
            ], 200),
        ]);

        $response = $this->get(route('online-library.show', ['slug' => 'panduan-kesehatan']));

        $response->assertStatus(200);
        $response->assertSee('Panduan Kesehatan Jantung');
        $response->assertSee('Isi panduan lengkap kesehatan jantung Anda.', false);
        $response->assertSee('panduan-jantung.pdf');
        $response->assertSee('500 KB');
        $response->assertSee(route('online-library.file', ['slug' => 'panduan-kesehatan']));
        $response->assertSee(route('online-library.file', ['slug' => 'panduan-kesehatan', 'download' => 1]));
    }

    public function test_file_endpoint_streams_pdf_inline_with_sameorigin_header(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries/panduan-kesehatan*' => Http::response([
                'data' => [
                    'id' => 2,
                    'title' => 'Panduan Kesehatan Jantung',
                    'slug' => 'panduan-kesehatan',
                    'alternate_slugs' => [
                        'id' => 'panduan-kesehatan',
                        'en' => 'heart-health-guide',
                    ],
                    'category' => 'Edukasi Medis',
                    'description' => '<p>Isi panduan lengkap.</p>',
                    'file_path' => 'online-libraries/jantung.pdf',
                    'file_name' => '1. KESEHATAN KERJA.pdf',
                    'file_size' => 512000,
                    'file_url' => 'https://latu.rs-elisabeth.com/media/rs-web-cms-media-bucket-staging/online-libraries/jantung.pdf',
                    'is_published' => true,
                    'created_at' => '2026-10-28T08:00:00Z',
                ],
            ], 200),
            'https://latu.rs-elisabeth.com/media/rs-web-cms-media-bucket-staging/online-libraries/jantung.pdf' => Http::response(
                '%PDF-1.4 fake pdf content',
                200,
                ['Content-Type' => 'application/pdf']
            ),
        ]);

        $response = $this->get(route('online-library.file', ['slug' => 'panduan-kesehatan']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->assertStringContainsString('inline;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('1. KESEHATAN KERJA.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_file_endpoint_forces_attachment_download_when_download_param_is_present(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries/panduan-kesehatan*' => Http::response([
                'data' => [
                    'id' => 2,
                    'title' => 'Panduan Kesehatan Jantung',
                    'slug' => 'panduan-kesehatan',
                    'alternate_slugs' => [
                        'id' => 'panduan-kesehatan',
                        'en' => 'heart-health-guide',
                    ],
                    'category' => 'Edukasi Medis',
                    'description' => '<p>Isi panduan lengkap.</p>',
                    'file_path' => 'online-libraries/jantung.pdf',
                    'file_name' => '1. KESEHATAN KERJA.pdf',
                    'file_size' => 512000,
                    'file_url' => 'https://latu.rs-elisabeth.com/media/rs-web-cms-media-bucket-staging/online-libraries/jantung.pdf',
                    'is_published' => true,
                    'created_at' => '2026-10-28T08:00:00Z',
                ],
            ], 200),
            'https://latu.rs-elisabeth.com/media/rs-web-cms-media-bucket-staging/online-libraries/jantung.pdf' => Http::response(
                '%PDF-1.4 fake pdf content',
                200,
                ['Content-Type' => 'application/pdf']
            ),
        ]);

        $response = $this->get(route('online-library.file', ['slug' => 'panduan-kesehatan', 'download' => 1]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('1. KESEHATAN KERJA.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_show_redirects_to_translated_slug_when_locale_switches(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries/panduan-kesehatan?lang=en' => Http::response([
                'message' => 'Not found',
            ], 404),
            'http://cms.test/api/v1/cms/online-libraries/panduan-kesehatan?lang=id' => Http::response([
                'data' => [
                    'id' => 2,
                    'title' => 'Panduan Kesehatan Jantung',
                    'slug' => 'panduan-kesehatan',
                    'alternate_slugs' => [
                        'id' => 'panduan-kesehatan',
                        'en' => 'heart-health-guide',
                    ],
                    'category' => 'Edukasi Medis',
                    'description' => '<p>Isi panduan lengkap.</p>',
                    'file_path' => 'online-libraries/jantung.pdf',
                    'file_name' => 'panduan-jantung.pdf',
                    'file_size' => 512000,
                    'file_url' => 'http://cms.test/storage/online-libraries/jantung.pdf',
                    'is_published' => true,
                    'created_at' => '2026-10-28T08:00:00Z',
                ],
            ], 200),
        ]);

        $response = $this->withSession(['locale' => 'en'])
            ->get(route('online-library.show', ['slug' => 'panduan-kesehatan']));

        $response->assertRedirect(route('online-library.show', ['slug' => 'heart-health-guide']));
    }

    public function test_show_returns_404_when_document_not_found(): void
    {
        Http::fake([
            'http://cms.test/api/v1/cms/online-libraries/*' => Http::response([
                'message' => 'Not Found',
            ], 404),
        ]);

        $response = $this->get(route('online-library.show', ['slug' => 'dokumen-tidak-ada']));

        $response->assertStatus(404);
    }

    public function test_cms_api_service_invalidates_online_libraries_cache(): void
    {
        $service = app(\App\Services\CmsApiService::class);
        $result = $service->invalidateCache('online_libraries');

        $this->assertSame('success', $result['status']);
        $this->assertSame('online_libraries', $result['flushed']);
    }
}
