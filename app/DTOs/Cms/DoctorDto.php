<?php

namespace App\DTOs\Cms;

final class DoctorDto
{
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    private function __construct(private readonly array $data) {}

    public function toArray(): array
    {
        $result = [
            'paramedic_code' => (string) $this->data['paramedic_code'],
            'name' => (string) $this->data['name'],
            'is_active' => (bool) ($this->data['is_active'] ?? false),
            'is_profile_complete' => (bool) ($this->data['is_profile_complete'] ?? false),
        ];
        foreach (['specialty_name', 'specialty_code', 'service_unit_name', 'bio', 'location', 'experience', 'education'] as $field) {
            $value = $this->data[$field] ?? null;
            $result[$field] = is_string($value) && trim($value) !== '' ? $value : null;
        }

        return $result;
    }
}
