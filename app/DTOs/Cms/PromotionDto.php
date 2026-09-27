<?php

namespace App\DTOs\Cms;

class PromotionDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $excerpt,
        public readonly ?string $description,
        public readonly ?string $image_path,
        public readonly ?string $image_url,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly bool $is_active
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            description: $data['description'] ?? null,
            image_path: $data['image_path'] ?? null,
            image_url: $data['image_url'] ?? null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }
}
