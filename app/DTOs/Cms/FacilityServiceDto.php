<?php

namespace App\DTOs\Cms;

class FacilityServiceDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?string $category,
        public readonly ?string $description,
        public readonly ?string $short_description,
        public readonly ?string $image_path,
        public readonly ?string $image_url,
        public readonly ?array $highlights,
        public readonly ?string $wa_link_text,
        public readonly ?string $wa_number,
        public readonly ?string $wa_prefilled_message,
        public readonly ?string $wa_action_url,
        public readonly bool $has_appointment_cta
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? null,
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            short_description: $data['short_description'] ?? null,
            image_path: $data['image_path'] ?? null,
            image_url: $data['image_url'] ?? null,
            highlights: isset($data['highlights']) && is_string($data['highlights']) ? json_decode($data['highlights'], true) : ($data['highlights'] ?? null),
            wa_link_text: $data['wa_link_text'] ?? null,
            wa_number: $data['wa_number'] ?? null,
            wa_prefilled_message: $data['wa_prefilled_message'] ?? null,
            wa_action_url: $data['wa_action_url'] ?? null,
            has_appointment_cta: (bool) ($data['has_appointment_cta'] ?? false),
        );
    }
}
