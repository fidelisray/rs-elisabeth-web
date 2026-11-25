<?php

namespace Tests\Feature;

use App\Http\Controllers\DynamicFormController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicFormWebTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cms_api.base_url' => 'https://cms.invalid/api/v1/cms',
            'cms_api.cons_id' => 'WEB-FRONTEND',
            'cms_api.secret_key' => 'test-secret',
            'cache.default' => 'array',
            'cms_api.legacy_form_slugs' => [
                'feedback' => null,
                'igd' => null,
                'rawat-jalan' => null,
                'rawat-inap' => null,
            ],
        ]);

        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_catalog_index_shows_only_public_listed_forms_supports_mode_filter_and_handles_empty_and_error_states(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/forms' => Http::sequence()
                ->push([
                    'data' => [
                        [
                            'id' => 1,
                            'title' => 'Survei Rawat Jalan Publik',
                            'slug' => 'survei-rajal-publik',
                            'description' => 'Survei terbuka untuk pasien poliklinik.',
                            'mode' => 'questionnaire',
                            'locale' => 'id',
                            'status' => 'published',
                            'is_accepting_responses' => true,
                            'is_public_listed' => true,
                        ],
                        [
                            'id' => 2,
                            'title' => 'Form QR Internal Tersembunyi',
                            'slug' => 'form-qr-tersembunyi',
                            'description' => 'Tidak boleh muncul di katalog.',
                            'mode' => 'questionnaire',
                            'locale' => 'id',
                            'status' => 'published',
                            'is_accepting_responses' => true,
                            'is_public_listed' => false,
                        ],
                    ],
                ], 200)
                ->push([], 500),
            'https://cms.invalid/api/v1/cms/forms?mode=feedback' => Http::response([
                'data' => [],
            ], 200),
        ]);

        $res = $this->get(route('dynamic-forms.index'));
        $res->assertOk();
        $res->assertSee('Survei Rawat Jalan Publik');
        $res->assertDontSee('Form QR Internal Tersembunyi');

        // Empty state saat filter feedback kosong
        $filteredEmpty = $this->get(route('dynamic-forms.index', ['mode' => 'feedback']));
        $filteredEmpty->assertOk();
        $filteredEmpty->assertSee('Belum Ada Formulir Publik Terbuka');

        // Upstream 500 memunculkan Error state 503
        Cache::flush();
        $errRes = $this->get(route('dynamic-forms.index'));
        $errRes->assertStatus(503);
        $errRes->assertSee('Gagal Memuat Daftar Formulir');
    }

    public function test_show_page_handles_noindex_for_unlisted_language_note_closed_schedule_and_404(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/qr-unlisted' => Http::response([
                'data' => $this->formSchemaPayload('qr-unlisted', [
                    'title' => 'Patient Satisfaction Survey',
                    'locale' => 'en',
                    'is_public_listed' => false,
                    'allow_multiple_submissions' => false,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/public-listed' => Http::response([
                'data' => $this->formSchemaPayload('public-listed', [
                    'title' => 'Kritik Layanan',
                    'mode' => 'feedback',
                    'locale' => 'id',
                    'is_public_listed' => true,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/form-belum-buka' => Http::response([
                'data' => $this->formSchemaPayload('form-belum-buka', [
                    'starts_at' => now()->addDays(2)->toIso8601String(),
                    'is_accepting_responses' => false,
                    'closed_reason' => 'not_started',
                    'closed_message' => 'Formulir dibuka mulai lusa.',
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/tidak-ada' => Http::response([
                'message' => 'Not Found',
            ], 404),
        ]);

        // 1. Unlisted form: noindex, nofollow + catatan bahasa berbeda + cookie rse_form_respondent diterbitkan
        $unlistedRes = $this->withSession(['locale' => 'id'])->get(route('dynamic-forms.show', ['slug' => 'qr-unlisted']));
        $unlistedRes->assertOk();
        $unlistedRes->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $unlistedRes->assertSee('Catatan bahasa:');
        $unlistedRes->assertCookie(DynamicFormController::RESPONDENT_COOKIE);

        // 2. Bisa dibuka pada locale EN tanpa 404
        $enRes = $this->withSession(['locale' => 'en'])->get(route('dynamic-forms.show', ['slug' => 'public-listed']));
        $enRes->assertOk();
        $enRes->assertDontSee('<meta name="robots" content="noindex, nofollow">', false);
        $enRes->assertSee('Language note:');

        // 3. Jadwal belum buka menampilkan status tutup (kembalikan locale ke id)
        $closedRes = $this->withSession(['locale' => 'id'])->get(route('dynamic-forms.show', ['slug' => 'form-belum-buka']));
        $closedRes->assertOk();
        $closedRes->assertSee('Pengisian Formulir Ditutup');
        $closedRes->assertSee('Formulir dibuka mulai lusa.');

        // 4. Draft/tidak ada -> 404
        $this->get(route('dynamic-forms.show', ['slug' => 'tidak-ada']))->assertNotFound();

        // 5. Upstream 500 -> 503 error state (tidak menyamar sebagai 404/200)
        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/gangguan-server' => Http::response([], 500),
        ]);
        $this->withSession(['locale' => 'id'])
            ->get(route('dynamic-forms.show', ['slug' => 'gangguan-server']))
            ->assertStatus(503)
            ->assertSee('Gagal Memuat Formulir');
    }

    public function test_upload_and_submit_proxies_forward_signed_payload_respondent_cookie_and_422_errors(): void
    {
        $respondentUuid = (string) Str::uuid();

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/form-tutup' => Http::response([
                'data' => $this->formSchemaPayload('form-tutup', [
                    'is_accepting_responses' => false,
                    'closed_reason' => 'closed',
                    'closed_message' => 'Formulir ditutup sementara.',
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/survei-berkas' => Http::response([
                'data' => $this->formSchemaPayload('survei-berkas', [
                    'mode' => 'feedback',
                    'allow_multiple_submissions' => false,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/survei-berkas/uploads' => Http::response([
                'success' => true,
                'status_code' => 201,
                'message' => 'Berkas berhasil diunggah.',
                'data' => [
                    'upload_token' => 'tok-web-999',
                    'original_name' => 'bukti.pdf',
                    'size_bytes' => 128,
                    'mime_type' => 'application/pdf',
                ],
            ], 201),
            'https://cms.invalid/api/v1/cms/forms/survei-berkas/submissions' => Http::sequence()
                ->push([
                    'success' => true,
                    'status_code' => 201,
                    'message' => 'Masukan dicatat.',
                    'data' => [
                        'submission_id' => 54,
                        'ticket_number' => 'DFB-20261119-WEB122',
                        'confirmation_message' => 'Masukan dicatat.',
                        'submitted_at' => '2026-11-19T08:59:00Z',
                    ],
                ], 201)
                ->push([
                    'success' => true,
                    'status_code' => 201,
                    'message' => 'Masukan dicatat.',
                    'data' => [
                        'submission_id' => 55,
                        'ticket_number' => 'DFB-20261119-WEB123',
                        'confirmation_message' => 'Masukan dicatat.',
                        'submitted_at' => '2026-11-19T09:00:00Z',
                    ],
                ], 201)
                ->push([
                    'success' => false,
                    'status_code' => 422,
                    'message' => 'Kuota formulir penuh.',
                    'errors' => ['form' => ['Kuota pengisian formulir ini sudah penuh.']],
                ], 422),
        ]);

        // 1. Proxy upload
        $pdf = UploadedFile::fake()->createWithContent('bukti.pdf', '%PDF-1.4 sample');
        $uploadRes = $this->postJson(route('dynamic-forms.upload', ['slug' => 'survei-berkas']), [
            'field_key' => 'lampiran',
            'file' => $pdf,
        ]);
        $uploadRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.upload_token', 'tok-web-999');

        // 2. Honeypot ditolak di Web sebelum menyentuh CMS
        $this->postJson(route('dynamic-forms.submit', ['slug' => 'survei-berkas']), [
            'hp_website' => 'spam-link',
            'answers' => ['komentar' => 'Halo'],
        ])->assertStatus(422)->assertJsonValidationErrors(['hp_website']);

        // 2b. Submit pertama kali tanpa cookie pada allow_multiple_submissions=false menerbitkan cookie otomatis
        $firstDirectRes = $this->postJson(route('dynamic-forms.submit', ['slug' => 'survei-berkas']), [
            'version_id' => 10,
            'answers' => ['komentar' => 'Submit langsung tanpa GET'],
        ]);
        $firstDirectRes->assertStatus(201);
        $firstDirectRes->assertCookie(DynamicFormController::RESPONDENT_COOKIE);

        // 3. Proxy submit valid meneruskan respondent_key dari cookie
        $submitRes = $this->withCredentials()
            ->withCookie(DynamicFormController::RESPONDENT_COOKIE, $respondentUuid)
            ->postJson(route('dynamic-forms.submit', ['slug' => 'survei-berkas']), [
                'version_id' => 10,
                'answers' => ['komentar' => 'Pelayanan baik', 'lampiran' => ['tok-web-999']],
                'source' => 'qr_barcode',
            ]);
        $submitRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ticket_number', 'DFB-20261119-WEB123');

        // 4. Penolakan kuota penuh 422 diteruskan dengan bersih
        $quotaRes = $this->withCredentials()
            ->withCookie(DynamicFormController::RESPONDENT_COOKIE, $respondentUuid)
            ->postJson(route('dynamic-forms.submit', ['slug' => 'survei-berkas']), [
                'version_id' => 10,
                'answers' => ['komentar' => 'Submit kedua'],
            ]);
        $quotaRes->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.form.0', 'Kuota pengisian formulir ini sudah penuh.');

        // 5. Upload pada form tutup ditolak 422 tanpa memanggil endpoint /uploads CMS
        $this->postJson(route('dynamic-forms.upload', ['slug' => 'form-tutup']), [
            'field_key' => 'lampiran',
            'file' => UploadedFile::fake()->createWithContent('bukti2.pdf', '%PDF-1.4'),
        ])->assertStatus(422)->assertJsonPath('message', 'Formulir ditutup sementara.');

        Http::assertSent(function ($request) use ($respondentUuid) {
            if (! str_ends_with($request->url(), '/submissions')) {
                return false;
            }
            $body = json_decode($request->body(), true);

            return ($body['respondent_key'] ?? null) === $respondentUuid
                && ($body['source'] ?? null) === 'qr_barcode'
                && ($body['version_id'] ?? null) === 10;
        });
    }

    public function test_legacy_routes_coexist_and_delegate_only_when_slug_mapping_is_configured(): void
    {
        // Default null: halaman statis /kritik-dan-saran & /kuesioner/igd tetap berjalan normal tanpa panggilan API GET
        $this->get(route('feedback.index'))
            ->assertOk()
            ->assertSee('Formulir Keluhan & Masukan Pasien');
        $this->get(route('questionnaire.igd'))
            ->assertOk()
            ->assertSee('Kuesioner Kepuasan Pasien IGD');

        // Aktifkan delegasi opsional ke slug formulir dinamis CMS
        config([
            'cms_api.legacy_form_slugs.feedback' => 'dinamis-kritik',
            'cms_api.legacy_form_slugs.igd' => 'dinamis-igd',
        ]);

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/dinamis-kritik' => Http::response([
                'data' => $this->formSchemaPayload('dinamis-kritik', [
                    'title' => 'Kritik Dinamis RS Elisabeth',
                    'mode' => 'feedback',
                    'is_public_listed' => true,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/dinamis-igd' => Http::response([
                'data' => $this->formSchemaPayload('dinamis-igd', [
                    'title' => 'Survei IGD Dinamis',
                    'mode' => 'questionnaire',
                    'is_public_listed' => false,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/dinamis-igd/submissions' => Http::response([
                'success' => true,
                'status_code' => 201,
                'message' => 'Tersimpan.',
                'data' => [
                    'submission_id' => 91,
                    'ticket_number' => null,
                    'confirmation_message' => 'Tersimpan.',
                    'submitted_at' => '2026-11-19T09:00:00Z',
                ],
            ], 201),
        ]);

        $this->get(route('feedback.index'))
            ->assertOk()
            ->assertSee('Kritik Dinamis RS Elisabeth');

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/dinamis-kritik/submissions' => Http::response([
                'success' => true,
                'status_code' => 201,
                'message' => 'Keluhan tercatat.',
                'data' => [
                    'submission_id' => 92,
                    'ticket_number' => 'DFB-20261119-LEG001',
                    'confirmation_message' => 'Keluhan tercatat.',
                    'submitted_at' => '2026-11-19T09:05:00Z',
                ],
            ], 201),
        ]);

        $this->postJson(route('feedback.store'), [
            'version_id' => 10,
            'answers' => ['komentar' => 'Masukan delegasi'],
        ])->assertStatus(201)->assertJsonPath('data.ticket_number', 'DFB-20261119-LEG001');

        $this->get(route('questionnaire.igd'))
            ->assertOk()
            ->assertSee('Survei IGD Dinamis')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->postJson(route('questionnaire.igd.store'), [
            'version_id' => 10,
            'answers' => ['komentar' => 'Cepat tanggap'],
        ])->assertStatus(201)->assertJsonPath('success', true);

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/dinamis-igd/submissions')) {
                return false;
            }
            $body = json_decode($request->body(), true);

            return ($body['source'] ?? null) === 'qr_barcode';
        });
    }

    public function test_translates_system_submission_errors_to_active_web_locale(): void
    {
        Http::fake([
            'https://cms.invalid/api/v1/cms/forms/survei-en' => Http::response([
                'data' => $this->formSchemaPayload('survei-en', [
                    'locale' => 'id',
                    'allow_multiple_submissions' => false,
                ]),
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/survei-en/submissions' => Http::response([
                'success' => false,
                'status_code' => 422,
                'message' => 'Anda sudah pernah mengisi formulir ini sebelumnya.',
                'errors' => [
                    'respondent_key' => ['Anda sudah pernah mengisi formulir ini sebelumnya.'],
                ],
            ], 422),
        ]);

        $this->withSession(['locale' => 'en'])
            ->withCredentials()
            ->withCookie(DynamicFormController::RESPONDENT_COOKIE, (string) Str::uuid())
            ->postJson(route('dynamic-forms.submit', ['slug' => 'survei-en']), [
                'version_id' => 10,
                'answers' => ['komentar' => 'Good'],
                'locale' => 'en',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'You have already submitted this form.')
            ->assertJsonPath('errors.respondent_key.0', 'You have already submitted this form.');
    }

    public function test_renders_sanitized_rich_text_form_description_in_intro_block_and_clean_plain_excerpt_in_catalog(): void
    {
        $richDescription = '<p>Isi <strong>survei ini</strong> dengan jujur.</p><ul><li>Poin pertama</li><li>Poin kedua</li></ul>'
            .'<a href="https://rs-elisabeth.com/panduan">Panduan</a>'
            .'<script>alert("xss")</script><img src="x" onerror="alert(1)">';

        Http::fake([
            'https://cms.invalid/api/v1/cms/forms' => Http::response([
                'data' => [[
                    'id' => 77,
                    'title' => 'Survei Rich Text',
                    'slug' => 'survei-rich-text',
                    'description' => '<p>Paragraf&nbsp;satu.</p><p>Paragraf dua.</p>',
                    'mode' => 'questionnaire',
                    'locale' => 'id',
                    'status' => 'published',
                    'is_accepting_responses' => true,
                    'is_public_listed' => true,
                ]],
            ], 200),
            'https://cms.invalid/api/v1/cms/forms/survei-rich-text' => Http::response([
                'data' => $this->formSchemaPayload('survei-rich-text', [
                    'title' => 'Survei Rich Text',
                    'description' => $richDescription,
                ]),
            ], 200),
        ]);

        // Katalog menampilkan ringkasan polos bersih tanpa tag HTML, kata menempel, maupun &nbsp;
        $catalog = $this->get(route('dynamic-forms.index'))->assertOk();
        $catalog->assertSee('Paragraf satu. Paragraf dua.');
        $catalog->assertDontSee('&nbsp;', false);
        $catalog->assertDontSee('<p>Paragraf', false);

        // Halaman formulir menampilkan Rich Text tersanitasi di .df-form-intro dan tidak mencetaknya di hero-subtitle
        $show = $this->get(route('dynamic-forms.show', ['slug' => 'survei-rich-text']))->assertOk();
        $show->assertSee('df-form-intro df-rich-text', false);
        $show->assertSee('<strong>survei ini</strong>', false);
        $show->assertSee('<li>Poin pertama</li>', false);
        $show->assertSee('rel="noopener noreferrer"', false);
        $show->assertDontSee('<script>', false);
        $show->assertDontSee('onerror', false);
        $show->assertDontSee('hero-subtitle', false);
    }

    private function formSchemaPayload(string $slug, array $overrides = []): array
    {
        return array_merge([
            'id' => 10,
            'title' => 'Formulir Uji',
            'slug' => $slug,
            'description' => 'Deskripsi formulir uji.',
            'mode' => 'questionnaire',
            'locale' => 'id',
            'status' => 'published',
            'starts_at' => null,
            'ends_at' => null,
            'max_responses' => null,
            'allow_multiple_submissions' => true,
            'is_public_listed' => true,
            'is_accepting_responses' => true,
            'remaining_quota' => null,
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
        ], $overrides);
    }
}
