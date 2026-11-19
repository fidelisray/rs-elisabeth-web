<?php

namespace App\DTOs\Cms;

use Illuminate\Support\Carbon;

class DynamicFormSummaryDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly string $mode,
        public readonly string $locale,
        public readonly string $status,
        public readonly ?Carbon $starts_at,
        public readonly ?Carbon $ends_at,
        public readonly ?int $max_responses,
        public readonly ?int $remaining_quota,
        public readonly bool $is_accepting_responses,
        public readonly bool $allow_multiple_submissions,
        public readonly bool $is_public_listed,
        public readonly ?int $published_version_number,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            mode: (string) ($data['mode'] ?? 'questionnaire'),
            locale: in_array($data['locale'] ?? 'id', ['id', 'en'], true) ? (string) $data['locale'] : 'id',
            status: (string) ($data['status'] ?? 'published'),
            starts_at: self::parseDate($data['starts_at'] ?? null),
            ends_at: self::parseDate($data['ends_at'] ?? null),
            max_responses: isset($data['max_responses']) && is_numeric($data['max_responses']) ? (int) $data['max_responses'] : null,
            remaining_quota: isset($data['remaining_quota']) && is_numeric($data['remaining_quota']) ? (int) $data['remaining_quota'] : null,
            is_accepting_responses: (bool) ($data['is_accepting_responses'] ?? true),
            allow_multiple_submissions: (bool) ($data['allow_multiple_submissions'] ?? true),
            is_public_listed: (bool) ($data['is_public_listed'] ?? false),
            published_version_number: isset($data['published_version_number']) && is_numeric($data['published_version_number'])
                ? (int) $data['published_version_number']
                : null,
        );
    }

    private static function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
