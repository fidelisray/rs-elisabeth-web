<?php

namespace App\Support;

/** Kontrak kode/skala sama dengan QuestionnaireDefinition pada CMS. */
final class QuestionnaireDefinition
{
    public const SCALES = [
        'puas' => ['Sangat Tidak Puas', 'Tidak Puas', 'Puas', 'Sangat Puas'],
        'setuju' => ['Sangat Tidak Setuju', 'Tidak Setuju', 'Setuju', 'Sangat Setuju'],
        'mudah' => ['Sangat Tidak Mudah', 'Tidak Mudah', 'Mudah', 'Sangat Mudah'],
        'memadai' => ['Sangat Tidak Memadai', 'Tidak Memadai', 'Memadai', 'Sangat Memadai'],
    ];

    public const QUESTIONS = [
        'igd' => [
            'speed_triage_arrival' => 'puas', 'triage_efficiency' => 'puas',
            'post_triage_care_speed' => 'puas', 'waiting_time_acceptable' => 'setuju',
            'medical_explanation' => 'puas', 'informed_procedure_risks' => 'setuju',
            'staff_responsiveness' => 'setuju', 'calm_communication' => 'puas',
            'emotional_support' => 'puas', 'family_support' => 'setuju',
            'waiting_area_comfort' => 'puas', 'family_information_clarity' => 'puas',
            'overall_igd_experience' => 'puas', 'information_accessibility' => 'mudah',
            'recommend_igd' => 'setuju',
        ],
        'rawat-jalan' => [
            'waiting_room_comfort' => 'puas', 'examination_room_comfort' => 'puas',
            'parking_staff' => 'puas', 'doctor_schedule_punctuality' => 'puas',
            'doctor_explanation' => 'puas', 'nurse_responsiveness' => 'puas',
            'staff_information_clarity' => 'puas', 'staff_friendliness' => 'puas',
            'staff_problem_solving' => 'puas', 'overall_outpatient_experience' => 'puas',
            'recommend_hospital' => 'setuju',
        ],
        'rawat-inap' => [
            'room_bed_comfort' => 'puas', 'room_cleanliness' => 'puas',
            'room_facilities' => 'puas', 'environment_peace' => 'puas',
            'doctor_visit_frequency' => 'puas', 'doctor_explanation' => 'puas',
            'nurse_responsiveness' => 'puas', 'nurse_care_quality' => 'puas',
            'medical_staff_info' => 'puas', 'decision_involvement' => 'puas',
            'procedure_adequacy' => 'memadai', 'family_communication' => 'puas',
            'food_quality' => 'puas', 'diet_menu_suitability' => 'setuju',
            'diet_explanation' => 'puas', 'overall_food_service' => 'puas',
            'overall_inpatient_experience' => 'puas', 'information_ease' => 'mudah',
            'recommend_inpatient' => 'setuju',
        ],
    ];

    public const DEMOGRAPHIC_TEXTS = [
        'age_group' => [
            'code' => 'A',
            'text' => 'Berapakah usia anda?',
        ],
        'gender' => [
            'code' => 'B',
            'text' => 'Apakah jenis kelamin anda?',
        ],
        'payment_method' => [
            'code' => 'C',
            'text' => 'Apakah cara bayar yang anda gunakan untuk melakukan pemeriksaan di Rumah Sakit ST. Elisabeth Semarang saat ini?',
        ],
    ];

    public const QUESTION_TEXTS = [
        'igd' => [
            'speed_triage_arrival' => 'Seberapa cepat Anda ditangani oleh petugas IGD setelah tiba?',
            'triage_efficiency' => 'Bagaimana penilaian Anda terhadap kecepatan dan efisiensi dalam proses triase (penilaian awal kondisi) yang Anda jalani?',
            'post_triage_care_speed' => 'Seberapa puas Anda akan kecepatan Anda menerima perawatan atau intervensi medis setelah proses triase?',
            'waiting_time_acceptable' => 'Anda merasa waktu tunggu untuk menerima perawatan lanjutan setelah penilaian awal dapat diterima',
            'medical_explanation' => 'Seberapa puas Anda terkait penjelasan dokter atau perawat tentang kondisi kesehatan Anda dan langkah-langkah perawatan yang akan diambil?',
            'informed_procedure_risks' => 'Anda diberitahu tentang prosedur medis yang akan dilakukan, termasuk risiko dan manfaatnya',
            'staff_responsiveness' => 'Dokter atau perawat responsif dalam menjawab pertanyaan atau kekhawatiran Anda selama berada di IGD',
            'calm_communication' => 'Seberapa baik staf medis menyampaikan informasi penting dengan tenang dan jelas, meskipun dalam situasi darurat atau krisis?',
            'emotional_support' => 'Seberapa baik perawat atau dokter memberikan dukungan emosional kepada Anda selama perawatan di IGD?',
            'family_support' => 'Keluarga Anda merasa diperhatikan dan diberikan dukungan emosional oleh staf IGD',
            'waiting_area_comfort' => 'Bagaimana penilaian Anda terhadap kenyamanan dan fasilitas di area tunggu IGD untuk keluarga?',
            'family_information_clarity' => 'Seberapa baik keluarga Anda menerima informasi yang konsisten dan jelas tentang kondisi dan perawatan Anda selama di IGD?',
            'overall_igd_experience' => 'Secara keseluruhan, bagaimana Anda menilai pengalaman Anda di Instalasi Gawat Darurat?',
            'information_accessibility' => 'Seberapa mudah Anda mendapatkan informasi yang Anda butuhkan selama berada di IGD?',
            'recommend_igd' => 'Anda akan merekomendasikan Unit Gawat Darurat RS St. Elisabeth Semarang kepada teman atau keluarga',
        ],
        'rawat-jalan' => [
            'waiting_room_comfort' => 'Bagaimana penilaian Anda terhadap kenyamanan ruang tunggu rawat jalan?',
            'examination_room_comfort' => 'Seberapa puas Anda dengan kenyamanan dan kualitas fasilitas di ruang pemeriksaan dokter?',
            'parking_staff' => 'Bagaimana Anda menilai ketersediaan dan keramahan petugas parkir?',
            'doctor_schedule_punctuality' => 'Seberapa puas Anda dengan ketepatan jadwal praktek dokter?',
            'doctor_explanation' => 'Seberapa puas Anda dengan penjelasan Dokter tentang kondisi kesehatan Anda dan rencana perawatan yang akan dijalani?',
            'nurse_responsiveness' => 'Bagaimana Anda menilai ketersediaan dan responsivitas perawat dalam memenuhi kebutuhan Anda?',
            'staff_information_clarity' => 'Seberapa puas Anda dengan informasi yang diberikan oleh petugas (pendaftaran, apoteker, analis, radiografer, kasir) mengenai prosedur yang akan dilakukan, pembiayaan, dan obat-obatan yang harus Anda konsumsi?',
            'staff_friendliness' => 'Seberapa puas Anda dengan keramahan petugas (pendaftaran, apoteker, analis, radiografer, kasir) dalam memberikan informasi kepada anda?',
            'staff_problem_solving' => 'Seberapa puas anda dengan kemampuan petugas kami dalam memberikan saran dan Solusi atas keluhan anda?',
            'overall_outpatient_experience' => 'Bagaimana Anda menilai keseluruhan pengalaman Anda selama melakukan pelayanan rawat jalan di Rumah Sakit St. Elisabeth Semarang?',
            'recommend_hospital' => 'Anda akan merekomendasikan rumah sakit ini kepada teman atau keluarga',
        ],
        'rawat-inap' => [
            'room_bed_comfort' => 'Bagaimana penilaian Anda terhadap kenyamanan kamar dan tempat tidur selama masa rawat inap?',
            'room_cleanliness' => 'Bagaimana penilaian Anda terhadap kebersihan kamar dan kamar mandi yang Anda gunakan selama masa rawat inap?',
            'room_facilities' => 'Seberapa puas Anda dengan ketersediaan dan kualitas fasilitas di kamar (misalnya, AC, TV, perlengkapan mandi)?',
            'environment_peace' => 'Bagaimana Anda menilai ketenangan dan keamanan lingkungan selama Anda dirawat inap?',
            'doctor_visit_frequency' => 'Seberapa puas Anda dengan frekuensi kunjungan dokter selama masa rawat inap Anda?',
            'doctor_explanation' => 'Seberapa puas Anda akan penjelasan Dokter tentang kondisi kesehatan Anda dan rencana perawatan yang akan dijalani?',
            'nurse_responsiveness' => 'Bagaimana Anda menilai ketersediaan dan responsivitas perawat dalam memenuhi kebutuhan Anda selama masa rawat inap?',
            'nurse_care_quality' => 'Seberapa puas Anda dengan kualitas perawatan yang diberikan oleh perawat selama masa rawat inap?',
            'medical_staff_info' => 'Seberapa puas Anda akan kejelasan informasi yang diberikan oleh staf medis mengenai kondisi kesehatan Anda, prosedur yang akan dilakukan, dan obat-obatan yang harus Anda konsumsi?',
            'decision_involvement' => 'Seberapa baik Anda dilibatkan dalam pengambilan keputusan mengenai perawatan dan pengobatan yang Anda terima?',
            'procedure_adequacy' => 'Seberapa memadai penjelasan tentang prosedur medis yang akan dilakukan, termasuk risiko dan manfaatnya?',
            'family_communication' => 'Seberapa baik komunikasi yang dilakukan oleh staf medis dengan keluarga Anda mengenai kondisi dan perawatan Anda selama masa rawat inap?',
            'food_quality' => 'Bagaimana penilaian Anda terhadap kualitas dan rasa makanan yang disediakan selama masa rawat inap?',
            'diet_menu_suitability' => 'Anda mendapatkan pilihan menu yang sesuai dengan kebutuhan diet Anda selama masa rawat inap',
            'diet_explanation' => 'Seberapa baik Anda diberi penjelasan tentang pentingnya diet dan bagaimana makanan yang disediakan mendukung proses penyembuhan Anda?',
            'overall_food_service' => 'Seberapa puas Anda secara keseluruhan dengan layanan makanan yang disediakan selama masa rawat inap?',
            'overall_inpatient_experience' => 'Bagaimana Anda menilai keseluruhan pengalaman Anda selama masa rawat inap di RS Elisabeth Semarang?',
            'information_ease' => 'Seberapa mudah Anda mendapatkan informasi yang Anda butuhkan selama masa rawat inap?',
            'recommend_inpatient' => 'Anda akan merekomendasikan RS St. Elisabeth Semarang kepada teman atau keluarga',
        ],
    ];

    /**
     * Kembalikan peta metadata terurut untuk seluruh butir pertanyaan kuesioner.
     *
     * @return array<string, array{code: string, badge: string, text: string, card_id: string}>
     */
    public static function fieldMetadata(string $type, ?string $suggestionPrompt = null): array
    {
        $meta = [];

        foreach (self::DEMOGRAPHIC_TEXTS as $field => $info) {
            $meta[$field] = [
                'code' => $info['code'],
                'badge' => __('Pertanyaan :number', ['number' => $info['code']]),
                'text' => __($info['text']),
                'card_id' => 'card_' . $field,
            ];
        }

        $idx = 1;
        foreach (array_keys(self::QUESTIONS[$type] ?? []) as $code) {
            $rawText = self::QUESTION_TEXTS[$type][$code] ?? $code;
            $numStr = (string) $idx;
            $meta[$code] = [
                'code' => $numStr,
                'badge' => __('Pertanyaan :number', ['number' => $numStr]),
                'text' => __($rawText),
                'card_id' => 'card_' . $code,
            ];
            $idx++;
        }

        $defaultSuggestionText = match ($type) {
            'rawat-jalan' => 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan rawat jalan di rumah sakit kami?',
            'rawat-inap' => 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan rawat inap di rumah sakit kami?',
            default => 'Apakah ada saran atau masukan yang Anda miliki untuk meningkatkan layanan di Unit Gawat Darurat kami?',
        };

        $meta['suggestions'] = [
            'code' => '3',
            'badge' => __('Saran & Masukan'),
            'text' => __($suggestionPrompt ?: $defaultSuggestionText),
            'card_id' => 'card_suggestions',
        ];

        return $meta;
    }
}
