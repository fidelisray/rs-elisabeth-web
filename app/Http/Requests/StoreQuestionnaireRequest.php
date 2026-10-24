<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionnaireRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan jenis kuesioner dari rute atau input.
     */
    public function getQuestionnaireType(): string
    {
        $route = $this->route() ? $this->route()->getName() : '';
        if (str_contains($route, 'igd') || $this->input('questionnaire_type') === 'igd') {
            return 'igd';
        }
        if (str_contains($route, 'rawat-jalan') || $this->input('questionnaire_type') === 'rawat-jalan') {
            return 'rawat-jalan';
        }
        return 'rawat-inap';
    }

    /**
     * Aturan validasi untuk formulir kuesioner kepuasan pasien.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $puas = 'in:Sangat Tidak Puas,Tidak Puas,Puas,Sangat Puas';
        $setuju = 'in:Sangat Tidak Setuju,Tidak Setuju,Setuju,Sangat Setuju';
        $mudah = 'in:Sangat Tidak Mudah,Tidak Mudah,Mudah,Sangat Mudah';
        $memadai = 'in:Sangat Tidak Memadai,Tidak Memadai,Memadai,Sangat Memadai';

        $rules = [
            // Honeypot anti-spam (harus kosong)
            'hp_website'     => ['nullable', 'size:0'],

            // Profil Responden (Wajib di semua kuesioner)
            'age_group'      => ['required', 'string', 'in:< 15 tahun,15 - 64 tahun,> 64 tahun'],
            'gender'         => ['required', 'string', 'in:Laki-laki,Perempuan'],
            'payment_method' => ['required', 'string', 'in:Tunai,BPJS Kesehatan,Asuransi,Jaminan Perusahaan'],
        ];

        $type = $this->getQuestionnaireType();

        if ($type === 'igd') {
            $rules['speed_triage_arrival']      = ['required', 'string', $puas];
            $rules['triage_efficiency']         = ['required', 'string', $puas];
            $rules['post_triage_care_speed']    = ['required', 'string', $puas];
            $rules['waiting_time_acceptable']   = ['required', 'string', $setuju];
            $rules['medical_explanation']       = ['required', 'string', $puas];
            $rules['informed_procedure_risks']  = ['required', 'string', $setuju];
            $rules['staff_responsiveness']      = ['required', 'string', $setuju];
            $rules['calm_communication']        = ['required', 'string', $puas];
            $rules['emotional_support']         = ['required', 'string', $puas];
            $rules['family_support']            = ['required', 'string', $setuju];
            $rules['waiting_area_comfort']      = ['required', 'string', $puas];
            $rules['family_information_clarity']= ['required', 'string', $puas];
            $rules['overall_igd_experience']    = ['required', 'string', $puas];
            $rules['information_accessibility'] = ['required', 'string', $mudah];
            $rules['recommend_igd']             = ['required', 'string', $setuju];
            $rules['suggestions']               = ['nullable', 'string', 'max:3000'];
        } elseif ($type === 'rawat-jalan') {
            $rules['waiting_room_comfort']      = ['required', 'string', $puas];
            $rules['examination_room_comfort']  = ['required', 'string', $puas];
            $rules['parking_staff']             = ['required', 'string', $puas];
            $rules['doctor_schedule_punctuality']= ['required', 'string', $puas];
            $rules['doctor_explanation']        = ['required', 'string', $puas];
            $rules['nurse_responsiveness']      = ['required', 'string', $puas];
            $rules['staff_information_clarity'] = ['required', 'string', $puas];
            $rules['staff_friendliness']        = ['required', 'string', $puas];
            $rules['staff_problem_solving']     = ['required', 'string', $puas];
            $rules['overall_outpatient_experience'] = ['required', 'string', $puas];
            $rules['recommend_hospital']        = ['required', 'string', $setuju];
            // Saran wajib untuk Rawat Jalan sesuai template Google Form
            $rules['suggestions']               = ['required', 'string', 'min:3', 'max:3000'];
        } else { // rawat_inap
            $rules['room_bed_comfort']          = ['required', 'string', $puas];
            $rules['room_cleanliness']          = ['required', 'string', $puas];
            $rules['room_facilities']           = ['required', 'string', $puas];
            $rules['environment_peace']         = ['required', 'string', $puas];
            $rules['doctor_visit_frequency']    = ['required', 'string', $puas];
            $rules['doctor_explanation']        = ['required', 'string', $puas];
            $rules['nurse_responsiveness']      = ['required', 'string', $puas];
            $rules['nurse_care_quality']        = ['required', 'string', $puas];
            $rules['medical_staff_info']        = ['required', 'string', $puas];
            $rules['decision_involvement']      = ['required', 'string', $puas];
            $rules['procedure_adequacy']        = ['required', 'string', $memadai];
            $rules['family_communication']      = ['required', 'string', $puas];
            $rules['food_quality']              = ['required', 'string', $puas];
            $rules['diet_menu_suitability']     = ['required', 'string', $setuju];
            $rules['diet_explanation']          = ['required', 'string', $puas];
            $rules['overall_food_service']      = ['required', 'string', $puas];
            $rules['overall_inpatient_experience'] = ['required', 'string', $puas];
            $rules['information_ease']          = ['required', 'string', $mudah];
            $rules['recommend_inpatient']       = ['required', 'string', $setuju];
            $rules['suggestions']               = ['nullable', 'string', 'max:3000'];
        }

        return $rules;
    }

    /**
     * Pesan error kustom untuk validasi kuesioner.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hp_website.size'       => __('Terdeteksi aktivitas bot yang tidak valid.'),
            'age_group.required'    => __('Mohon pilih kelompok usia Anda.'),
            'gender.required'       => __('Mohon pilih jenis kelamin Anda.'),
            'payment_method.required'=> __('Mohon pilih cara bayar yang Anda gunakan.'),
            'suggestions.required'  => __('Mohon tuliskan saran atau masukan Anda untuk peningkatan layanan rawat jalan kami.'),
            'suggestions.min'       => __('Saran atau masukan minimal memuat :min karakter.'),
            'suggestions.max'       => __('Saran atau masukan tidak boleh melebihi :max karakter.'),
            '*.required'            => __('Mohon berikan penilaian pada seluruh butir pertanyaan kuesioner.'),
            '*.in'                  => __('Pilihan jawaban tidak valid.'),
        ];
    }
}
