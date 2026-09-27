<?php

namespace App\DTOs\Cms;

class RoomFacilityDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $category,
        public readonly ?string $tagline,
        public readonly ?string $description,
        public readonly ?string $room_size,
        public readonly ?string $bed_count,
        public readonly ?string $max_companion,
        public readonly ?string $image_path,
        public readonly ?string $image_url,
        public readonly ?array $amenities,
        public readonly ?array $highlight_tags,
        public readonly int $sort_order
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            category: $data['category'] ?? null,
            tagline: $data['tagline'] ?? null,
            description: $data['description'] ?? null,
            room_size: $data['room_size'] ?? null,
            bed_count: $data['bed_count'] ?? null,
            max_companion: $data['max_companion'] ?? null,
            image_path: $data['image_path'] ?? null,
            image_url: $data['image_url'] ?? null,
            amenities: isset($data['amenities']) && is_string($data['amenities']) ? json_decode($data['amenities'], true) : ($data['amenities'] ?? []),
            highlight_tags: isset($data['highlight_tags']) && is_string($data['highlight_tags']) ? json_decode($data['highlight_tags'], true) : ($data['highlight_tags'] ?? []),
            sort_order: (int) ($data['sort_order'] ?? 0),
        );
    }
}
