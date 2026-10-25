<?php

namespace App\Support;

final class PatientFormErrors
{
    public static function map(array $errors, ?string $type = null): array
    {
        $result = [];
        foreach ($errors as $field => $messages) {
            if (! is_string($field) || ! is_array($messages)) {
                continue;
            }
            $mapped = $field;
            if ($type !== null) {
                $mapped = preg_replace('/^(demographics|ratings)\./', '', $field);
                $allowed = array_merge(['age_group', 'gender', 'payment_method', 'suggestions', 'questionnaire_type'], array_keys(QuestionnaireDefinition::QUESTIONS[$type] ?? []));
            } else {
                $mapped = preg_replace('/^categories(?=\.|$)/', 'complaint_categories', $field);
                $allowed = ['reporter_name', 'relation_to_patient', 'relation_other', 'patient_name', 'phone_number', 'complaint_categories', 'category_other', 'complaint_detail', 'expected_solution'];
            }
            if (! in_array(explode('.', $mapped)[0], $allowed, true)) {
                continue;
            }
            $result[$mapped] = array_values(array_filter($messages, fn ($message) => is_string($message)));
        }

        return $result;
    }
}
