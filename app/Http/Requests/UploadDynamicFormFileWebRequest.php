<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadDynamicFormFileWebRequest extends FormRequest
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
            'field_key' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/'],
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'field_key.required' => __('Identitas pertanyaan unggahan tidak ditemukan.'),
            'field_key.regex' => __('Identitas pertanyaan unggahan tidak valid.'),
            'file.required' => __('Silakan pilih berkas yang akan diunggah.'),
            'file.file' => __('Input harus berupa berkas yang valid.'),
            'file.max' => __('Ukuran berkas tidak boleh melebihi 10 MB.'),
        ];
    }
}
