<?php

namespace App\DTOs\Cms;

use Illuminate\Support\Carbon;

class DynamicFormSchemaDto
{
    /**
     * @param  list<array<string, mixed>>  $sections
     */
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
        public readonly ?string $closed_reason,
        public readonly ?string $closed_message,
        public readonly string $confirmation_message,
        public readonly bool $allow_multiple_submissions,
        public readonly bool $is_public_listed,
        public readonly ?int $published_version_id,
        public readonly ?int $published_version_number,
        public readonly ?Carbon $published_at,
        public readonly array $sections,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $publishedVersion = is_array($data['published_version'] ?? null) ? $data['published_version'] : [];
        $rawSections = array_values(array_filter(
            (array) ($publishedVersion['sections'] ?? []),
            fn ($section): bool => is_array($section) && ! empty($section['section_key'])
        ));

        usort($rawSections, fn (array $a, array $b): int => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));

        $sections = array_map(function (array $section): array {
            $fields = array_values(array_filter(
                (array) ($section['fields'] ?? []),
                fn ($field): bool => is_array($field) && ! empty($field['field_key'])
            ));
            usort($fields, fn (array $a, array $b): int => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
            $section['fields'] = $fields;

            return $section;
        }, $rawSections);

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
            is_accepting_responses: (bool) ($data['is_accepting_responses'] ?? false),
            closed_reason: isset($data['closed_reason']) && is_string($data['closed_reason']) ? $data['closed_reason'] : null,
            closed_message: isset($data['closed_message']) && is_string($data['closed_message']) ? $data['closed_message'] : null,
            confirmation_message: (string) ($data['confirmation_message'] ?? ''),
            allow_multiple_submissions: (bool) ($data['allow_multiple_submissions'] ?? true),
            is_public_listed: (bool) ($data['is_public_listed'] ?? false),
            published_version_id: isset($publishedVersion['version_id']) && is_numeric($publishedVersion['version_id'])
                ? (int) $publishedVersion['version_id']
                : null,
            published_version_number: isset($publishedVersion['version_number']) && is_numeric($publishedVersion['version_number'])
                ? (int) $publishedVersion['version_number']
                : null,
            published_at: self::parseDate($publishedVersion['published_at'] ?? null),
            sections: $sections,
        );
    }

    /**
     * @return array{is_accepting_responses: bool, closed_reason: ?string, closed_message: ?string, remaining_quota: ?int}
     */
    public function availability(): array
    {
        return [
            'is_accepting_responses' => $this->is_accepting_responses,
            'closed_reason' => $this->closed_reason,
            'closed_message' => $this->closed_message,
            'remaining_quota' => $this->remaining_quota,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toClientSchema(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'mode' => $this->mode,
            'locale' => $this->locale,
            'version_id' => $this->published_version_id,
            'version_number' => $this->published_version_number,
            'allow_multiple_submissions' => $this->allow_multiple_submissions,
            'is_accepting_responses' => $this->is_accepting_responses,
            'remaining_quota' => $this->remaining_quota,
            'closed_reason' => $this->closed_reason,
            'closed_message' => $this->closed_message,
            'confirmation_message' => $this->confirmation_message,
            'sections' => $this->sections,
        ];
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
