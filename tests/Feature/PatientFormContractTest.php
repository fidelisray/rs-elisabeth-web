<?php

namespace Tests\Feature;

use App\Services\CmsApiService;
use App\Services\QuestionnaireService;
use App\Support\QuestionnaireDefinition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/** Test kontrak disiapkan saja; eksekusi ditunda atas instruksi pemilik. */
class PatientFormContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cms_api.base_url' => 'https://cms.invalid/api/v1/cms', 'cms_api.cons_id' => 'TEST-WEB', 'cms_api.secret_key' => 'test-secret']);
        Http::preventStrayRequests();
    }

    public function test_feedback_requires_a_confirmed_persistence_and_a_ticket(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/feedbacks' => Http::sequence()
            ->push(['success' => true], 201)
            ->push(['success' => false, 'ticket_number' => 'FAKE'], 200)
            ->push(['success' => true, 'ticket_number' => 'FB-REAL'], 201)]);
        $service = app(CmsApiService::class);
        $this->assertSame('error', $service->submitFeedback([])['status']);
        $this->assertSame('error', $service->submitFeedback([])['status']);
        $this->assertSame('FB-REAL', $service->submitFeedback([])['ticket_number']);
    }

    public function test_rate_limit_returns_retry_information_without_logging_patient_body(): void
    {
        Log::spy();
        Http::fake(['https://cms.invalid/api/v1/cms/feedbacks' => Http::response(['patient_name' => 'Private Patient'], 429, ['Retry-After' => '90'])]);
        $response = app(CmsApiService::class)->submitFeedback(['patient_name' => 'Private Patient']);
        $this->assertSame(429, $response['http_code']);
        $this->assertSame(90, $response['retry_after']);
        Log::shouldHaveReceived('warning')->once()->with('CMS patient submission was not confirmed', ['endpoint' => 'feedbacks', 'http_code' => 429]);
    }

    public function test_questionnaire_payload_only_contains_known_rating_fields_for_each_service(): void
    {
        Http::fake(['https://cms.invalid/api/v1/cms/questionnaires/*' => Http::response(['success' => true], 201)]);
        foreach (QuestionnaireDefinition::QUESTIONS as $type => $questions) {
            $data = ['age_group' => '15 - 64 tahun', 'gender' => 'Perempuan', 'payment_method' => 'Tunai',
                'suggestions' => 'Saran uji', 'hp_website' => '', 'questionnaire_type' => $type, 'unknown_field' => 'not-a-rating'];
            foreach ($questions as $code => $scale) $data[$code] = QuestionnaireDefinition::SCALES[$scale][3];
            $this->assertTrue(app(QuestionnaireService::class)->sendQuestionnaire($type, $data)['success']);
        }
        Http::assertSentCount(3);
        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);
            $type = $payload['questionnaire_type'];
            return array_keys($payload['ratings']) === array_keys(QuestionnaireDefinition::QUESTIONS[$type])
                && ! isset($payload['ratings']['questionnaire_type'], $payload['ratings']['unknown_field']);
        });
    }
}
