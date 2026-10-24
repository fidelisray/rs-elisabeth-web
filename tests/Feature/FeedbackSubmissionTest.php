<?php

namespace Tests\Feature;

use App\Services\CmsApiService;
use Mockery;
use Tests\TestCase;

class FeedbackSubmissionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function validPayload(array $override = []): array
    {
        return array_merge([
            'reporter_name' => 'Fidelis Raymond',
            'relation_to_patient' => 'Diri Sendiri',
            'patient_name' => 'Fidelis Raymond',
            'phone_number' => '081234567890',
            'complaint_categories' => ['Tarif dan biaya pemeriksaan'],
            'complaint_detail' => 'Penjelasan keluhan pasien mengenai rincian biaya yang perlu diklarifikasi.',
            'expected_solution' => 'Penjelasan tertulis dari bagian administrasi.',
        ], $override);
    }

    public function test_feedback_page_can_be_rendered(): void
    {
        $response = $this->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertSee('Formulir Keluhan & Masukan Pasien');
        $response->assertSee('Kirim Keluhan & Masukan');
    }

    public function test_feedback_submission_fails_when_required_fields_are_missing(): void
    {
        $response = $this->post(route('feedback.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reporter_name', 'patient_name', 'phone_number']);
    }

    public function test_feedback_submission_blocks_honeypot_spam(): void
    {
        $response = $this->post(route('feedback.store'), $this->validPayload(['hp_website' => 'bot']));

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['hp_website']);
    }

    public function test_feedback_submission_succeeds_when_cms_stores_it(): void
    {
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status' => 'success',
                'http_code' => 201,
                'ticket_number' => 'FB-20261024-ABC123',
                'message' => 'Feedback berhasil dikirimkan.',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $response = $this->post(route('feedback.store'), $this->validPayload());

        $response->assertStatus(302);
        $response->assertRedirect(route('feedback.index'));
        $response->assertSessionHas('feedback_success', true);
        $response->assertSessionHas('ticket_number', 'FB-20261024-ABC123');
    }

    public function test_feedback_submission_fails_clearly_when_cms_does_not_store_it(): void
    {
        // CMS tidak menyimpan data (404). Pasien harus diberi tahu kegagalan, bukan tiket palsu.
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status' => 'error',
                'http_code' => 404,
                'message' => 'Not Found',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $response = $this->post(route('feedback.store'), $this->validPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('feedback_error');
        $response->assertSessionMissing('ticket_number');
        $response->assertSessionMissing('feedback_success');
    }

    public function test_feedback_ajax_submission_returns_422_json_when_cms_does_not_store_it(): void
    {
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status' => 'exception',
                'http_code' => 500,
                'message' => 'Connection refused',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $response = $this->postJson(route('feedback.store'), $this->validPayload());

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $response->assertJsonMissing(['ticket_number' => 'FB-']);
    }

    public function test_feedback_ajax_submission_returns_json_with_ticket_when_stored(): void
    {
        $mockCms = Mockery::mock(CmsApiService::class);
        $mockCms->shouldReceive('submitFeedback')
            ->once()
            ->andReturn([
                'status' => 'success',
                'http_code' => 201,
                'ticket_number' => 'FB-20261024-XYZ789',
            ]);

        $this->app->instance(CmsApiService::class, $mockCms);

        $response = $this->postJson(route('feedback.store'), $this->validPayload());

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ticket_number' => 'FB-20261024-XYZ789',
        ]);
    }
}
