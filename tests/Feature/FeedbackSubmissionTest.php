<?php

namespace Tests\Feature;

use App\Services\CmsApiService;
use App\Services\FeedbackService;
use Mockery;
use Tests\TestCase;

class FeedbackSubmissionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_feedback_page_can_be_rendered(): void
    {
        $response = $this->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Keluhan & Masukan Pasien');
        $response->assertSee('Identitas Pelapor & Pasien');
        $response->assertSee('Kategori Keluhan');
        $response->assertSee('Mohon jelaskan keluhan Anda secara rinci');
        $response->assertSee('Apa solusi yang Anda harapkan dari Rumah Sakit?');
        $response->assertSee('Kirim Keluhan & Masukan');
    }

    public function test_feedback_page_renders_english_translations(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertSee('Patient Complaint &amp; Feedback Form', false);
        $response->assertSee('Reporter &amp; Patient Identity', false);
        $response->assertSee('Complaint Category');
        $response->assertSee('Reporter Name');
        $response->assertSee('Submit Complaint &amp; Feedback', false);
    }

    public function test_feedback_submission_fails_when_required_fields_are_missing(): void
    {
        $response = $this->post(route('feedback.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'reporter_name',
            'relation_to_patient',
            'patient_name',
            'phone_number',
            'complaint_categories',
            'complaint_detail',
            'expected_solution',
        ]);
    }

    public function test_feedback_submission_blocks_honeypot_spam(): void
    {
        $response = $this->post(route('feedback.store'), [
            'hp_website'           => 'https://spam-bot-automated.com',
            'reporter_name'        => 'Spam Bot',
            'relation_to_patient'  => 'Diri Sendiri',
            'patient_name'         => 'Spam Bot',
            'phone_number'         => '081234567890',
            'complaint_categories' => ['Fasilitas & Sarana Prasarana'],
            'complaint_detail'     => 'Spam content test message with more than 10 characters',
            'expected_solution'    => 'Spam resolution test message',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['hp_website']);
    }

    public function test_feedback_submission_succeeds_with_mock_logging_fallback_when_cms_endpoint_is_unavailable(): void
    {
        // Simulasi CMS API mengembalikan 404 (karena endpoint belum ada di CMS)
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status'    => 'error',
                'http_code' => 404,
                'message'   => 'Not Found',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $payload = [
            'reporter_name'        => 'Fidelis Raymond',
            'relation_to_patient'  => 'Orang Tua',
            'patient_name'         => 'Pasien Contoh',
            'phone_number'         => '081234567890',
            'complaint_categories' => [
                'Pelayanan Medis (Tindakan medis, penegakan diagnosis, penundaan pelayanan, dll)',
                'Fasilitas & Sarana Prasarana',
            ],
            'complaint_detail'     => 'Penjelasan keluhan pasien secara mendalam dan jelas mengenai pelayanan.',
            'expected_solution'    => 'Diharapkan adanya tindak lanjut evaluasi segera dari pihak rumah sakit.',
        ];

        $response = $this->post(route('feedback.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('feedback.index'));
        $response->assertSessionHas('feedback_success', true);
        $response->assertSessionHas('ticket_number');

        // Pastikan tiket berformat FB-YYYYMMDD-XXXX
        $ticket = session('ticket_number');
        $this->assertStringStartsWith('FB-', $ticket);

        // Kunjungi halaman feedback untuk memastikan layar konfirmasi sukses ter-render
        $followUp = $this->get(route('feedback.index'));
        $followUp->assertStatus(200);
        $followUp->assertSee('Terima Kasih Atas Masukan Anda');
        $followUp->assertSee($ticket);
    }

    public function test_feedback_submission_succeeds_when_cms_api_returns_success(): void
    {
        // Simulasi CMS API sudah aktif dan membalas 201 dengan tiket resmi
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status'        => 'success',
                'http_code'     => 201,
                'ticket_number' => 'FB-CMS-2026-8888',
                'message'       => 'Disimpan di database CMS',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $payload = [
            'reporter_name'        => 'Fidelis Raymond',
            'relation_to_patient'  => 'Diri Sendiri',
            'patient_name'         => 'Fidelis Raymond',
            'phone_number'         => '081234567890',
            'complaint_categories' => ['Tarif dan biaya pemeriksaan'],
            'complaint_detail'     => 'Penjelasan keluhan pasien mengenai rincian biaya yang perlu diklarifikasi.',
            'expected_solution'    => 'Penjelasan tertulis dari bagian administrasi.',
        ];

        $response = $this->post(route('feedback.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('feedback.index'));
        $response->assertSessionHas('ticket_number', 'FB-CMS-2026-8888');
    }

    public function test_feedback_json_ajax_submission_returns_json_response(): void
    {
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status'    => 'error',
                'http_code' => 404,
                'message'   => 'Not Found',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $payload = [
            'reporter_name'        => 'Fidelis Raymond',
            'relation_to_patient'  => 'Diri Sendiri',
            'patient_name'         => 'Fidelis Raymond',
            'phone_number'         => '081234567890',
            'complaint_categories' => ['Etika dan perilaku petugas'],
            'complaint_detail'     => 'Penjelasan keluhan pasien mengenai sikap petugas di bagian pendaftaran.',
            'expected_solution'    => 'Pelatihan keramahan petugas.',
        ];

        $response = $this->postJson(route('feedback.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'ticket_number',
            'message',
        ]);
    }
}
