<?php

namespace App\DTOs\Cms;

use Illuminate\Support\Carbon;

class OnlineLibraryDto
{
    /**
     * @param array<string, string|null> $alternate_slugs
     */
    public function __construct(
        public readonly int|string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly array $alternate_slugs,
        public readonly ?string $category,
        public readonly ?string $description,
        public readonly ?string $file_path,
        public readonly ?string $file_name,
        public readonly ?int $file_size,
        public readonly ?string $file_url,
        public readonly bool $is_published,
        public readonly ?Carbon $created_at
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            title: $data['title'] ?? '',
            slug: $data['slug'] ?? '',
            alternate_slugs: is_array($data['alternate_slugs'] ?? null) ? $data['alternate_slugs'] : [],
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            file_path: $data['file_path'] ?? null,
            file_name: $data['file_name'] ?? null,
            file_size: isset($data['file_size']) ? (int) $data['file_size'] : null,
            file_url: $data['file_url'] ?? null,
            is_published: (bool) ($data['is_published'] ?? true),
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
        );
    }

    public function formattedFileSize(): ?string
    {
        if (! $this->file_size || $this->file_size <= 0) {
            return null;
        }

        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2).' MB';
        }

        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 0).' KB';
        }

        return $this->file_size.' B';
    }
}
