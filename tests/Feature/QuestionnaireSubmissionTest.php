<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuestionnaireSubmissionTest extends TestCase
{
    /**
     * Data payload valid untuk kuesioner IGD.
     */
    protected function validIgdPayload(): array
    {
        return [
            'age_group' => '15 - 64 tahun',
            'gender' => 'Laki-laki',
            'payment_method' => 'BPJS Kesehatan',
            'speed_triage_arrival' => 'Sangat Puas',
            'triage_efficiency' => 'Puas',
            'post_triage_care_speed' => 'Puas',
            'waiting_time_acceptable' => 'Setuju',
            'medical_explanation' => 'Puas',
            'informed_procedure_risks' => 'Setuju',
            'staff_responsiveness' => 'Sangat Setuju',
            'calm_communication' => 'Puas',
            'emotional_support' => 'Puas',
            'family_support' => 'Setuju',
            'waiting_area_comfort' => 'Puas',
            'family_information_clarity' => 'Puas',
            'overall_igd_experience' => 'Sangat Puas',
            'information_accessibility' => 'Mudah',
            'recommend_igd' => 'Sangat Setuju',
            'suggestions' => 'Pelayanan IGD sangat cepat dan ramah.',
            'hp_website' => '',
        ];
    }

    /**
     * Data payload valid untuk kuesioner Rawat Jalan.
     */
    protected function validRawatJalanPayload(): array
    {
        return [
            'age_group' => '15 - 64 tahun',
            'gender' => 'Perempuan',
            'payment_method' => 'Asuransi',
            'waiting_room_comfort' => 'Puas',
            'examination_room_comfort' => 'Sangat Puas',
            'parking_staff' => 'Puas',
            'doctor_schedule_punctuality' => 'Puas',
            'doctor_explanation' => 'Sangat Puas',
            'nurse_responsiveness' => 'Puas',
            'staff_information_clarity' => 'Puas',
            'staff_friendliness' => 'Sangat Puas',
            'staff_problem_solving' => 'Puas',
            'overall_outpatient_experience' => 'Sangat Puas',
            'recommend_hospital' => 'Sangat Setuju',
            'suggestions' => 'Ruang tunggu rawat jalan sangat nyaman dan rapi.',
            'hp_website' => '',
        ];
    }

    /**
     * Data payload valid untuk kuesioner Rawat Inap.
     */
    protected function validRawatInapPayload(): array
    {
        return [
            'age_group' => '15 - 64 tahun',
            'gender' => 'Perempuan',
            'payment_method' => 'Tunai',
            'room_bed_comfort' => 'Sangat Puas',
            'room_cleanliness' => 'Sangat Puas',
            'room_facilities' => 'Puas',
            'environment_peace' => 'Puas',
            'doctor_visit_frequency' => 'Puas',
            'doctor_explanation' => 'Sangat Puas',
            'nurse_responsiveness' => 'Sangat Puas',
            'nurse_care_quality' => 'Sangat Puas',
            'medical_staff_info' => 'Puas',
            'decision_involvement' => 'Puas',
            'procedure_adequacy' => 'Memadai',
            'family_communication' => 'Puas',
            'food_quality' => 'Puas',
            'diet_menu_suitability' => 'Setuju',
            'diet_explanation' => 'Puas',
            'overall_food_service' => 'Puas',
            'overall_inpatient_experience' => 'Sangat Puas',
            'information_ease' => 'Sangat Mudah',
            'recommend_inpatient' => 'Sangat Setuju',
            'suggestions' => 'Perawat dan dokter sangat sigap melayani.',
            'hp_website' => '',
        ];
    }

    /**
     * Test 1: Halaman hub portal kuesioner berhasil dirender dan memiliki tag noindex.
     */
    public function test_questionnaire_hub_page_renders_with_noindex_tag(): void
    {
        $response = $this->get('/kuesioner');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('Kuesioner Kepuasan Pasien');
        $response->assertSee('Instalasi Gawat Darurat (IGD)');
        $response->assertSee('Poliklinik Rawat Jalan');
        $response->assertSee('Ruang Rawat Inap');
    }

    /**
     * Test 2: Halaman kuesioner IGD berhasil dirender dengan tag noindex dan butir pertanyaan.
     */
    public function test_igd_questionnaire_page_renders_with_all_questions_and_noindex(): void
    {
        $response = $this->get('/kuesioner/igd');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('Kuesioner Kepuasan Pasien IGD');
        $response->assertSee('Berapakah usia anda?');
        $response->assertSee('Seberapa cepat Anda ditangani oleh petugas IGD setelah tiba?');
        $response->assertSee('Sangat Puas');
        $response->assertSee('Sangat Tidak Puas');
    }

    /**
     * Test 3: Halaman kuesioner Rawat Jalan berhasil dirender dengan tag noindex.
     */
    public function test_rawat_jalan_questionnaire_page_renders_with_all_questions(): void
    {
        $response = $this->get('/kuesioner/rawat-jalan');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('Kuesioner Kepuasan Pasien Rawat Jalan');
        $response->assertSee('Bagaimana penilaian Anda terhadap kenyamanan ruang tunggu rawat jalan?');
    }

    /**
     * Test 4: Halaman kuesioner Rawat Inap berhasil dirender dengan tag noindex.
     */
    public function test_rawat_inap_questionnaire_page_renders_with_all_questions(): void
    {
        $response = $this->get('/kuesioner/rawat-inap');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $response->assertSee('Kuesioner Kepuasan Pasien Rawat Inap');
        $response->assertSee('Bagaimana penilaian Anda terhadap kenyamanan kamar dan tempat tidur selama masa rawat inap?');
    }

    /**
     * Test 5: Kuesioner mendukung multi-bahasa bahasa Inggris (i18n).
     */
    public function test_questionnaires_support_english_locale(): void
    {
        App::setLocale('en');

        $response = $this->get('/kuesioner/igd');

        $response->assertStatus(200);
        $response->assertSee('Emergency Room Patient Satisfaction Questionnaire');
        $response->assertSee('What is your age?');
        $response->assertSee('Very Satisfied');
    }

    /**
     * Test 6: Validasi gagal jika field demografi atau rating wajib belum diisi.
     */
    public function test_submission_fails_when_required_fields_missing(): void
    {
        $response = $this->post('/kuesioner/igd', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'age_group',
            'gender',
            'payment_method',
            'speed_triage_arrival',
        ]);
    }

    /**
     * Test 7: Validasi saran wajib diisi khusus untuk Rawat Jalan.
     */
    public function test_rawat_jalan_requires_suggestions_field(): void
    {
        $payload = $this->validRawatJalanPayload();
        unset($payload['suggestions']);

        $response = $this->post('/kuesioner/rawat-jalan', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['suggestions']);
    }

    /**
     * Test 8: Validasi honeypot memblokir bot spam.
     */
    public function test_submission_blocks_honeypot_spam(): void
    {
        $payload = $this->validIgdPayload();
        $payload['hp_website'] = 'spambot-payload';

        $response = $this->post('/kuesioner/igd', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['hp_website']);
    }

    /**
     * Test 9: CMS menyimpan kuesioner IGD (201). Pasien mendapat pesan terima kasih.
     */
    public function test_igd_submission_succeeds_when_cms_stores_it(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/igd' => Http::response(['success' => true], 201),
        ]);

        $response = $this->post('/kuesioner/igd', $this->validIgdPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('questionnaire_success', true);
    }

    /**
     * Test 10: CMS menolak atau tidak tersedia (404). Pasien mendapat error, bukan sukses palsu.
     */
    public function test_rawat_jalan_submission_fails_clearly_when_cms_does_not_store_it(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/rawat-jalan' => Http::response(['message' => 'Not Found'], 404),
        ]);

        $response = $this->post('/kuesioner/rawat-jalan', $this->validRawatJalanPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('questionnaire_error');
        $response->assertSessionMissing('questionnaire_success');
    }

    /**
     * Test 11: CMS offline (500). Pasien mendapat error.
     */
    public function test_rawat_inap_submission_fails_clearly_when_cms_is_offline(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/rawat-inap' => Http::response(['message' => 'Server Error'], 500),
        ]);

        $response = $this->post('/kuesioner/rawat-inap', $this->validRawatInapPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('questionnaire_error');
        $response->assertSessionMissing('questionnaire_success');
    }

    /**
     * Test 12: Body POST ikut ditandatangani HMAC (X-Signature atas timestamp.consId.body).
     */
    public function test_questionnaire_post_body_is_signed(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/igd' => Http::response(['success' => true], 201),
        ]);

        $this->post('/kuesioner/igd', $this->validIgdPayload())->assertStatus(302);

        Http::assertSent(function (Request $request) {
            $consId = $request->header('X-Cons-ID')[0] ?? '';
            $timestamp = $request->header('X-Timestamp')[0] ?? '';
            $signature = $request->header('X-Signature')[0] ?? '';
            $body = $request->body();
            $secret = config('cms_api.secret_key');

            $expected = base64_encode(hash_hmac('sha256', $timestamp.$consId.$body, $secret, true));

            return $expected === $signature && $body !== '';
        });
    }

    /**
     * Test 13: Submit via AJAX JSON mengembalikan 200 saat CMS menyimpan.
     */
    public function test_ajax_submission_returns_json_response(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/igd' => Http::response([
                'success' => true,
                'message' => 'Kuesioner diterima.',
            ], 201),
        ]);

        $response = $this->postJson('/kuesioner/igd', $this->validIgdPayload());

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Test 14: CMS menyimpan kuesioner Rawat Jalan (201). Endpoint kebab-case dipanggil dengan benar.
     */
    public function test_rawat_jalan_submission_succeeds_when_cms_stores_it(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/rawat-jalan' => Http::response(['success' => true], 201),
        ]);

        $response = $this->post('/kuesioner/rawat-jalan', $this->validRawatJalanPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('questionnaire_success', true);

        Http::assertSent(function (Request $request) {
            return str_ends_with($request->url(), '/questionnaires/rawat-jalan');
        });
    }

    /**
     * Test 15: CMS menyimpan kuesioner Rawat Inap (201). Endpoint kebab-case dipanggil dengan benar.
     */
    public function test_rawat_inap_submission_succeeds_when_cms_stores_it(): void
    {
        Http::fake([
            '*/api/v1/cms/questionnaires/rawat-inap' => Http::response(['success' => true], 201),
        ]);

        $response = $this->post('/kuesioner/rawat-inap', $this->validRawatInapPayload());

        $response->assertStatus(302);
        $response->assertSessionHas('questionnaire_success', true);

        Http::assertSent(function (Request $request) {
            return str_ends_with($request->url(), '/questionnaires/rawat-inap');
        });
    }
}
