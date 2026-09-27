<?php

namespace App\DTOs\Cms;

use Illuminate\Support\Carbon;

class NewsDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $category,
        public readonly ?string $author,
        public readonly ?string $excerpt,
        public readonly ?string $content,
        public readonly ?string $image_path,
        public readonly ?string $image_url,
        public readonly bool $is_published,
        public readonly ?Carbon $created_at
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? '',
            category: $data['category'] ?? null,
            author: $data['author'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            content: $data['content'] ?? null,
            image_path: $data['image_path'] ?? null,
            image_url: $data['image_url'] ?? null,
            is_published: (bool) ($data['is_published'] ?? false),
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
        );
    }
}
