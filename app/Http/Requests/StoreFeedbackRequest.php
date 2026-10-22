<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Siapkan data untuk divalidasi (pencegahan honeypot spam bot).
     */
    protected function prepareForValidation(): void
    {
        // Sanitasi nomor telepon: bersihkan spasi berlebih
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => trim((string) $this->phone_number),
            ]);
        }
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Honeypot anti-spam (harus selalu kosong)
            'hp_website' => ['nullable', 'max:0'],

            // Data Pelapor & Pasien
            'reporter_name' => ['required', 'string', 'max:150'],
            'relation_to_patient' => [
                'required',
                'string',
                'in:Diri Sendiri,Orang Tua,Suami/Istri,Saudara Kandung,Anak Kandung,Lainnya',
            ],
            'relation_other' => [
                'required_if:relation_to_patient,Lainnya',
                'nullable',
                'string',
                'max:100',
            ],
            'patient_name' => ['required', 'string', 'max:150'],
            'phone_number' => [
                'required',
                'string',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                'min:10',
                'max:20',
            ],

            // Kategori Keluhan (Checkbox multi-select)
            'complaint_categories' => ['required', 'array', 'min:1'],
            'complaint_categories.*' => ['string', 'max:150'],
            'category_other' => [
                'nullable',
                'string',
                'max:150',
            ],

            // Detail dan Solusi
            'complaint_detail' => ['required', 'string', 'min:10', 'max:3000'],
            'expected_solution' => ['required', 'string', 'min:5', 'max:1500'],
        ];
    }

    /**
     * Pesan validasi kustom dalam bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'hp_website.max' => __('Permintaan Anda terdeteksi sebagai spam otomatis.'),
            'reporter_name.required' => __('Nama pelapor wajib diisi.'),
            'relation_to_patient.required' => __('Hubungan dengan pasien wajib dipilih.'),
            'relation_other.required_if' => __('Mohon sebutkan jenis hubungan lainnya dengan pasien.'),
            'patient_name.required' => __('Nama pasien wajib diisi.'),
            'phone_number.required' => __('Nomor telepon wajib diisi.'),
            'phone_number.regex' => __('Format nomor telepon tidak valid.'),
            'phone_number.min' => __('Nomor telepon minimal terdiri dari 10 digit.'),
            'complaint_categories.required' => __('Mohon pilih minimal satu kategori keluhan.'),
            'complaint_categories.min' => __('Mohon pilih minimal satu kategori keluhan.'),
            'complaint_detail.required' => __('Mohon jelaskan rincian keluhan Anda.'),
            'complaint_detail.min' => __('Penjelasan keluhan minimal 10 karakter agar informasi dapat dipahami dengan baik.'),
            'expected_solution.required' => __('Mohon tuliskan solusi yang Anda harapkan dari rumah sakit.'),
            'expected_solution.min' => __('Harapan solusi minimal 5 karakter.'),
        ];
    }
}
