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
}
