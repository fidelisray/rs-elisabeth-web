<?php

namespace App\Http\Requests;

use App\Support\QuestionnaireDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function getQuestionnaireType(): string
    {
        $name = $this->route()?->getName() ?? '';
        foreach (array_keys(QuestionnaireDefinition::QUESTIONS) as $type) {
            if ($name === 'questionnaire.'.$type.'.store') {
                return $type;
            }
        }

        return '';
    }

    public function rules(): array
    {
        $type = $this->getQuestionnaireType();
        $rules = [
            'hp_website' => ['nullable', 'size:0'],
            'questionnaire_type' => ['nullable', 'string', Rule::in([$type])],
            'age_group' => ['required', Rule::in(['< 15 tahun', '15 - 64 tahun', '> 64 tahun'])],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'payment_method' => ['required', Rule::in(['Tunai', 'BPJS Kesehatan', 'Asuransi', 'Jaminan Perusahaan'])],
            'suggestions' => $type === 'rawat-jalan'
                ? ['required', 'string', 'min:3', 'max:3000']
                : ['nullable', 'string', 'max:3000'],
        ];
        foreach (QuestionnaireDefinition::QUESTIONS[$type] ?? [] as $code => $scale) {
            $rules[$code] = ['required', 'string', Rule::in(QuestionnaireDefinition::SCALES[$scale])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'hp_website.size' => __('Terdeteksi aktivitas bot yang tidak valid.'),
            'age_group.required' => __('Mohon pilih kelompok usia Anda.'),
            'gender.required' => __('Mohon pilih jenis kelamin Anda.'),
            'payment_method.required' => __('Mohon pilih cara bayar yang Anda gunakan.'),
            'suggestions.required' => __('Mohon tuliskan saran atau masukan Anda untuk peningkatan layanan rawat jalan kami.'),
            'suggestions.min' => __('Saran atau masukan minimal memuat :min karakter.'),
            'suggestions.max' => __('Saran atau masukan tidak boleh melebihi :max karakter.'),
            '*.required' => __('Mohon berikan penilaian pada seluruh butir pertanyaan kuesioner.'),
            '*.in' => __('Pilihan jawaban tidak valid.'),
        ];
    }
}
