<?php

namespace App\DTOs\Cms;

class BannerPromotionDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $title,
        public readonly ?string $slug,
        public readonly string $image_path,
        public readonly ?string $image_url,
        public readonly int $sort_order,
        public readonly bool $is_active
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? null,
            image_path: $data['image_path'] ?? '',
            image_url: $data['image_url'] ?? null,
            sort_order: (int) ($data['sort_order'] ?? 0),
            is_active: (bool) ($data['is_active'] ?? true),
        );
    }
}
