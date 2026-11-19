<?php

namespace Tests\Unit;

use App\Services\CmsApiService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DynamicFormClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cms_api.base_url' => 'https://cms.invalid/api/v1/cms',
            'cms_api.cons_id' => 'WEB-CLIENT',
            'cms_api.secret_key' => 'unit-test-secret',
            'cms_api.upload_timeout' => 30,
            'cms_api.cache_ttl.dynamic_forms' => 600,
            'cms_api.cache_ttl.dynamic_forms_quota_ttl' => 60,
            'cache.default' => 'array',
        ]);

        Cache::flush();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_upload_dynamic_form_file_signs_sha256_and_preserves_original_filename(): void
    {
        $bytes = "%PDF-1.4\nSample diagnostic report bytes";
        $expectedSha256 = hash('sha256', $bytes);
        $file = UploadedFile::fake()->createWithContent('laporan-pasien.pdf', $bytes);

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/survei-igd/uploads' => Http::response([
                'success' => true,
                'status_code' => 201,
                'message' => 'Berkas berhasil diunggah.',
                'data' => [
                    'upload_token' => 'tok-uuid-1234',
                    'original_name' => 'laporan-pasien.pdf',
                    'size_bytes' => strlen($bytes),
                    'mime_type' => 'application/pdf',
                    'expires_at' => '2026-11-20T08:00:00Z',
                ],
            ], 201),
        ]);

        $service = app(CmsApiService::class);
        $result = $service->uploadDynamicFormFile('survei-igd', 'lampiran_bukti', $file);

        $this->assertSame('success', $result['status']);
        $this->assertSame(201, $result['http_code']);
        $this->assertSame('tok-uuid-1234', $result['data']['upload_token']);
        $this->assertSame('laporan-pasien.pdf', $result['data']['original_name']);

        Http::assertSent(function ($request) use ($expectedSha256) {
            $timestamp = $request->header('X-Timestamp')[0] ?? '';
            $consId = $request->header('X-Cons-ID')[0] ?? '';
            $signature = $request->header('X-Signature')[0] ?? '';
            $contentHash = $request->header('X-Content-SHA256')[0] ?? '';
            $expectedSig = base64_encode(hash_hmac('sha256', $timestamp.'WEB-CLIENT'.$expectedSha256, 'unit-test-secret', true));

            $multipartData = collect($request->data());
            $hasFieldKey = $multipartData->contains(fn ($part) => ($part['name'] ?? '') === 'field_key' && ($part['contents'] ?? '') === 'lampiran_bukti');
            $hasFile = $request->hasFile('file', null, 'laporan-pasien.pdf');

            return $request->isMultipart()
                && $consId === 'WEB-CLIENT'
                && hash_equals($expectedSha256, $contentHash)
                && hash_equals($expectedSig, $signature)
                && $hasFieldKey
                && $hasFile;
        });
    }

    public function test_submit_dynamic_form_parses_ticket_and_forwards_errors_without_logging_patient_data(): void
    {
        Log::spy();

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/kritik-rs/submissions' => Http::sequence()
                ->push([
                    'success' => true,
                    'status_code' => 201,
                    'message' => 'Terima kasih, masukan dicatat.',
                    'data' => [
                        'submission_id' => 88,
                        'ticket_number' => 'DFB-20261119-AB12CD',
                        'confirmation_message' => 'Terima kasih, masukan dicatat.',
                        'submitted_at' => '2026-11-19T08:00:00Z',
                    ],
                ], 201)
                ->push([
                    'success' => false,
                    'status_code' => 422,
                    'message' => 'Validasi gagal.',
                    'errors' => ['answers.keluhan' => ['Pertanyaan [Keluhan] wajib diisi.']],
                ], 422)
                ->push([
                    'success' => false,
                    'status_code' => 429,
                    'message' => 'Too many requests.',
                ], 429, ['Retry-After' => '45']),
        ]);

        $service = app(CmsApiService::class);

        $ok = $service->submitDynamicForm('kritik-rs', [
            'version_id' => 5,
            'answers' => ['nama' => 'Pasien Rahasia', 'keluhan' => 'Waktu tunggu lama'],
        ]);
        $this->assertSame('success', $ok['status']);
        $this->assertSame(201, $ok['http_code']);
        $this->assertSame('DFB-20261119-AB12CD', $ok['data']['ticket_number']);
        $this->assertSame(88, $ok['data']['submission_id']);

        $err422 = $service->submitDynamicForm('kritik-rs', [
            'version_id' => 5,
            'answers' => ['nama' => 'Pasien Rahasia'],
        ]);
        $this->assertSame('error', $err422['status']);
        $this->assertSame(422, $err422['http_code']);
        $this->assertArrayHasKey('answers.keluhan', $err422['errors']);

        $err429 = $service->submitDynamicForm('kritik-rs', [
            'version_id' => 5,
            'answers' => ['nama' => 'Pasien Rahasia'],
        ]);
        $this->assertSame('error', $err429['status']);
        $this->assertSame(429, $err429['http_code']);
        $this->assertSame(45, $err429['retry_after']);

        Log::shouldHaveReceived('warning')
            ->with('CMS dynamic form submission was not confirmed', ['slug' => 'kritik-rs', 'http_code' => 422])
            ->once();
        Log::shouldHaveReceived('warning')
            ->with('CMS dynamic form submission was not confirmed', ['slug' => 'kritik-rs', 'http_code' => 429])
            ->once();
    }

    public function test_get_dynamic_form_by_slug_uses_60s_ttl_for_quota_forms_and_600s_for_standard_and_reevaluates_schedule(): void
    {
        $now = Carbon::parse('2026-11-19 10:00:00');
        Carbon::setTestNow($now);

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/form-berkuota' => Http::response([
                'data' => $this->sampleSchema('form-berkuota', maxResponses: 50, startsAt: null, endsAt: '2026-11-19 10:05:00'),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/form-standar' => Http::response([
                'data' => $this->sampleSchema('form-standar', maxResponses: null, startsAt: null, endsAt: '2026-11-19 10:02:00'),
            ], 200),
        ]);

        $service = app(CmsApiService::class);

        // Fetch awal kedua form (2 HTTP calls)
        $quotaForm = $service->getDynamicFormBySlug('form-berkuota');
        $stdForm = $service->getDynamicFormBySlug('form-standar');
        $this->assertTrue($quotaForm['is_accepting_responses']);
        $this->assertTrue($stdForm['is_accepting_responses']);
        Http::assertSentCount(2);

        // +30s: keduanya masih hit cache (tetap 2 HTTP calls)
        Carbon::setTestNow($now->copy()->addSeconds(30));
        $service->getDynamicFormBySlug('form-berkuota');
        $service->getDynamicFormBySlug('form-standar');
        Http::assertSentCount(2);

        // +65s: form-berkuota (TTL 60s) expire & fetch ulang; form-standar (TTL 600s) tetap hit cache
        Carbon::setTestNow($now->copy()->addSeconds(65));
        $service->getDynamicFormBySlug('form-berkuota');
        $service->getDynamicFormBySlug('form-standar');
        Http::assertSentCount(3);

        // +180s (10:03:00): form-standar masih dari cache namun ends_at (10:02:00) sudah lewat -> lokal jadi ended
        Carbon::setTestNow($now->copy()->addSeconds(180));
        $expiredStd = $service->getDynamicFormBySlug('form-standar');
        $this->assertFalse($expiredStd['is_accepting_responses']);
        $this->assertSame('ended', $expiredStd['closed_reason']);
        Http::assertSentCount(3);
    }

    public function test_invalidate_cache_for_dynamic_forms_works_without_tags(): void
    {
        config(['cache.default' => 'file']);
        Cache::store('file')->flush();

        try {
            Http::fake([
                'https://cms.invalid/api/v1/cms/forms/form-uji' => Http::sequence()
                    ->push(['data' => $this->sampleSchema('form-uji', title: 'Versi Awal')], 200)
                    ->push(['data' => $this->sampleSchema('form-uji', title: 'Versi Terbaru')], 200),
            ]);

            $service = app(CmsApiService::class);
            $this->assertSame('Versi Awal', $service->getDynamicFormBySlug('form-uji')['title']);
            $this->assertSame('Versi Awal', $service->getDynamicFormBySlug('form-uji')['title']);

            $res = $service->invalidateCache('dynamic_forms');
            $this->assertSame('success', $res['status']);
            $this->assertSame('Versi Terbaru', $service->getDynamicFormBySlug('form-uji')['title']);
            Http::assertSentCount(2);
        } finally {
            Cache::store('file')->flush();
        }
    }

    public function test_get_dynamic_forms_fails_closed_when_is_public_listed_is_omitted_and_updates_stale_not_started_message(): void
    {
        $now = Carbon::parse('2026-11-19 10:00:00');
        Carbon::setTestNow($now);

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms' => Http::response([
                'data' => [
                    ['id' => 1, 'title' => 'Tampil', 'slug' => 'tampil', 'is_public_listed' => true],
                    ['id' => 2, 'title' => 'Tanpa Flag', 'slug' => 'tanpa-flag'],
                ],
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/jendela-singkat' => Http::response([
                'data' => array_merge(
                    $this->sampleSchema(
                        'jendela-singkat',
                        startsAt: '2026-11-19 10:02:00',
                        endsAt: '2026-11-19 10:04:00',
                    ),
                    [
                        'is_accepting_responses' => false,
                        'closed_reason' => 'not_started',
                        'closed_message' => 'Formulir ini belum dibuka untuk pengisian.',
                    ]
                ),
            ], 200),
        ]);

        $service = app(CmsApiService::class);
        $list = $service->getDynamicForms();
        $this->assertCount(1, $list);
        $this->assertSame('tampil', $list[0]['slug']);

        // Snapshot awal berstatus not_started, lalu melewati ends_at di cache lokal.
        $initial = $service->getDynamicFormBySlug('jendela-singkat');
        $this->assertFalse($initial['is_accepting_responses']);
        $this->assertSame('not_started', $initial['closed_reason']);

        Carbon::setTestNow($now->copy()->addMinutes(5));
        $ended = $service->getDynamicFormBySlug('jendela-singkat');
        $this->assertFalse($ended['is_accepting_responses']);
        $this->assertSame('ended', $ended['closed_reason']);
        $this->assertSame('Periode pengisian formulir ini telah berakhir.', $ended['closed_message']);
    }

    private function sampleSchema(
        string $slug,
        string $title = 'Survei Uji',
        ?int $maxResponses = null,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): array {
        return [
            'id' => 1,
            'title' => $title,
            'slug' => $slug,
            'description' => 'Deskripsi uji',
            'mode' => 'questionnaire',
            'locale' => 'id',
            'status' => 'published',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'max_responses' => $maxResponses,
            'allow_multiple_submissions' => true,
            'is_public_listed' => true,
            'is_accepting_responses' => true,
            'remaining_quota' => $maxResponses,
            'closed_reason' => null,
            'closed_message' => null,
            'confirmation_message' => 'Terima kasih.',
            'published_version' => [
                'version_id' => 10,
                'version_number' => 1,
                'published_at' => '2026-11-19T08:00:00Z',
                'sections' => [[
                    'section_key' => 'sec_1',
                    'title' => 'Bagian 1',
                    'sort_order' => 1,
                    'next_section_rule' => ['action' => 'submit', 'target_section_key' => null],
                    'fields' => [[
                        'field_key' => 'komentar',
                        'type' => 'short_text',
                        'label' => 'Komentar',
                        'is_required' => true,
                        'sort_order' => 1,
                        'options' => ['subtype' => 'text'],
                    ]],
                ]],
            ],
        ];
    }
}
