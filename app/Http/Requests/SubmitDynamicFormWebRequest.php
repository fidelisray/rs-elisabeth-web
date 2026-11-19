<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitDynamicFormWebRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hp_website' => ['nullable', 'max:0'],
            'version_id' => ['nullable', 'integer', 'min:1'],
            'answers' => ['present', 'array'],
            'locale' => ['nullable', 'string', 'in:id,en'],
            'source' => ['nullable', 'string', 'in:web_public,qr_barcode'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hp_website.max' => __('Permintaan Anda terdeteksi sebagai spam otomatis.'),
            'answers.present' => __('Jawaban formulir wajib disertakan.'),
            'answers.array' => __('Format jawaban formulir tidak valid.'),
            'source.in' => __('Sumber pengisian tidak dikenali.'),
            'locale.in' => __('Bahasa pengisian tidak dikenali.'),
        ];
    }
}
