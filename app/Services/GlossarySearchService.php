<?php

namespace App\Services;

class GlossarySearchService
{
    public const SCORE_EXACT = 100;
    public const SCORE_PREFIX = 80;
    public const SCORE_PHRASE_WORD_START = 65;
    public const SCORE_ALL_TOKENS_WORD_START = 55;
    public const SCORE_ALL_TOKENS_SUBSTRING = 40;

    /**
     * Normalize a raw search query: trim, collapse spaces, and lowercase.
     */
    public function normalizeQuery(string $raw): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($raw));

        return mb_strtolower((string) ($collapsed ?? ''), 'UTF-8');
    }

    /**
     * Normalize a title, slug, or query phrase for matching by converting hyphens
     * and repeated spaces into a single space, then trimming outer spaces.
     */
    public function normalizeForMatching(string $raw): string
    {
        $collapsed = preg_replace('/[\s\-]+/u', ' ', $raw);

        return mb_strtolower(trim((string) ($collapsed ?? '')), 'UTF-8');
    }

    /**
     * Split a query into unique non-empty tokens separated by spaces or hyphens.
     *
     * @return list<string>
     */
    public function tokenize(string $normalized): array
    {
        $clean = $this->normalizeQuery($normalized);
        if ($clean === '') {
            return [];
        }

        $parts = preg_split('/[\s\-]+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return [];
        }

        return array_values(array_unique($parts));
    }

    /**
     * Score a single glossary entry strictly against its title and slug.
     * Returns 0 when the entry does not match all tokens (token-based AND).
     *
     * @param  array<string, mixed>  $item
     * @param  list<string>  $tokens
     */
    public function scoreItem(array $item, string $normalizedQuery, array $tokens): int
    {
        if ($normalizedQuery === '' || empty($tokens)) {
            return 0;
        }

        $rawTitle = (string) ($item['istilah'] ?? '');
        $rawSlug = (string) ($item['slug'] ?? '');
        $normTitle = $this->normalizeForMatching($rawTitle);
        $normSlug = $this->normalizeForMatching($rawSlug);
        $normQueryPhrase = $this->normalizeForMatching($normalizedQuery);

        if ($normQueryPhrase === '') {
            return 0;
        }

        $candidates = [];
        if ($normTitle !== '') {
            $candidates[] = $normTitle;
        }
        if ($normSlug !== '' && $normSlug !== $normTitle) {
            $candidates[] = $normSlug;
        }

        $bestScore = 0;
        $rawSlugLower = mb_strtolower(trim($rawSlug), 'UTF-8');

        foreach ($candidates as $candidate) {
            if (!$this->containsAllTokens($candidate, $tokens)) {
                continue;
            }

            if ($candidate === $normQueryPhrase || ($rawSlugLower !== '' && $rawSlugLower === $normalizedQuery)) {
                $score = self::SCORE_EXACT;
            } elseif (str_starts_with($candidate, $normQueryPhrase)) {
                $score = self::SCORE_PREFIX;
            } elseif ($this->matchesAtWordStart($candidate, $normQueryPhrase)) {
                $score = self::SCORE_PHRASE_WORD_START;
            } elseif ($this->allTokensMatchAtWordStart($candidate, $tokens)) {
                $score = self::SCORE_ALL_TOKENS_WORD_START;
            } else {
                $score = self::SCORE_ALL_TOKENS_SUBSTRING;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
            }
        }

        return $bestScore;
    }

    /**
     * Filter and rank glossary entries using title-only token-based AND search.
     *
     * @param  array<int, array<string, mixed>>  $allItems
     * @return list<array<string, mixed>>
     */
    public function searchTitleMatches(array $allItems, string $keyword): array
    {
        $normalizedQuery = $this->normalizeQuery($keyword);
        $tokens = $this->tokenize($normalizedQuery);

        if ($normalizedQuery === '' || empty($tokens)) {
            return [];
        }

        $matched = [];

        foreach ($allItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $score = $this->scoreItem($item, $normalizedQuery, $tokens);
            if ($score > 0) {
                $matched[] = array_merge($item, [
                    'relevance_score' => $score,
                ]);
            }
        }

        usort($matched, static function (array $a, array $b): int {
            $scoreDiff = ((int) ($b['relevance_score'] ?? 0)) <=> ((int) ($a['relevance_score'] ?? 0));
            if ($scoreDiff !== 0) {
                return $scoreDiff;
            }

            $aTitle = (string) ($a['istilah'] ?? '');
            $bTitle = (string) ($b['istilah'] ?? '');

            $lenDiff = mb_strlen($aTitle, 'UTF-8') <=> mb_strlen($bTitle, 'UTF-8');
            if ($lenDiff !== 0) {
                return $lenDiff;
            }

            return strcasecmp($aTitle, $bTitle);
        });

        return $matched;
    }

    /**
     * Extract a clean 1-to-2 character sub-prefix without trailing whitespace.
     * Single-letter leading words like "B Cell Lymphoma" or "X Linked Agammaglobulinemia"
     * return "B" or "X" instead of "B " or "X ".
     */
    public function extractPrefix(string $title): string
    {
        $cleanTitle = trim($title);
        $rawPrefix = trim(mb_substr($cleanTitle, 0, 2, 'UTF-8'));

        if ($rawPrefix === '') {
            return '#';
        }

        $firstChar = mb_strtoupper(mb_substr($rawPrefix, 0, 1, 'UTF-8'), 'UTF-8');
        $rest = mb_strtolower(mb_substr($rawPrefix, 1, null, 'UTF-8'), 'UTF-8');

        return $firstChar . $rest;
    }

    /**
     * Group matched glossary items by a clean 2-character sub-prefix without trailing whitespace.
     * Single-letter leading words like "B Cell Lymphoma" or "X Linked Agammaglobulinemia"
     * are grouped cleanly under "B" or "X" instead of "B " or "X ".
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, list<array<string, mixed>>>
     */
    public function groupByPrefix(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $prefix = $this->extractPrefix((string) ($item['istilah'] ?? ''));

            $grouped[$prefix][] = $item;
        }

        foreach ($grouped as $prefix => $groupItems) {
            usort($groupItems, static function (array $a, array $b): int {
                return strcasecmp((string) ($a['istilah'] ?? ''), (string) ($b['istilah'] ?? ''));
            });
            $grouped[$prefix] = $groupItems;
        }

        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

        return $grouped;
    }

    /**
     * Build the dynamic autocomplete suggestion payload for a non-empty search query.
     *
     * @param  array<int, array<string, mixed>>  $allItems
     * @return array<string, mixed>
     */
    public function suggest(
        array $allItems,
        string $keyword,
        GlossaryPopularityService $popularityService,
        int $limit = 8
    ): array {
        $trimmedQuery = trim($keyword);
        $matches = $this->searchTitleMatches($allItems, $trimmedQuery);
        $total = count($matches);
        $topMatches = array_slice($matches, 0, max(1, $limit));

        $items = [];
        foreach ($topMatches as $item) {
            $title = (string) ($item['istilah'] ?? '');
            $slug = (string) ($item['slug'] ?? '');
            $items[] = [
                'slug' => $slug,
                'istilah' => $title,
                'letter' => mb_strtoupper(mb_substr(trim($title), 0, 1, 'UTF-8'), 'UTF-8'),
                'excerpt' => $popularityService->extractLeadExcerpt((string) ($item['deskripsi'] ?? ''), 125),
                'url' => route('glossary.show', ['term' => $slug, 'src' => 'search'], false),
            ];
        }

        return [
            'mode' => 'search',
            'query' => $trimmedQuery,
            'total' => $total,
            'all_results_url' => route('glossary.index', ['q' => $trimmedQuery], false),
            'items' => $items,
        ];
    }

    /**
     * Build the popular-terms suggestion payload shown when the search box is focused while empty.
     *
     * @param  array<int, array<string, mixed>>  $allItems
     * @return array<string, mixed>
     */
    public function popularSuggestions(
        array $allItems,
        GlossaryPopularityService $popularityService,
        int $limit = 6
    ): array {
        $popularData = $popularityService->getPopularTerms($allItems, max(1, $limit));
        $terms = is_array($popularData['terms'] ?? null) ? $popularData['terms'] : [];

        $items = [];
        foreach ($terms as $term) {
            $title = (string) ($term['istilah'] ?? '');
            $slug = (string) ($term['slug'] ?? '');
            $excerpt = !empty($term['lead_excerpt'])
                ? (string) $term['lead_excerpt']
                : $popularityService->extractLeadExcerpt((string) ($term['deskripsi'] ?? ''), 125);

            $isTrending = !empty($term['is_trending']);
            $itemPayload = [
                'slug' => $slug,
                'istilah' => $title,
                'letter' => mb_strtoupper(mb_substr(trim($title), 0, 1, 'UTF-8'), 'UTF-8'),
                'excerpt' => $excerpt,
                'url' => route('glossary.show', ['term' => $slug, 'src' => 'pop'], false),
                'is_trending' => $isTrending,
            ];

            if ($isTrending) {
                $itemPayload['trending_label'] = __('Sedang Tren');
            }

            $items[] = $itemPayload;
        }

        return [
            'mode' => 'popular',
            'query' => '',
            'total' => count($items),
            'all_results_url' => route('glossary.index', [], false),
            'items' => $items,
        ];
    }

    /**
     * @param  list<string>  $tokens
     */
    protected function containsAllTokens(string $haystack, array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (!str_contains($haystack, $token)) {
                return false;
            }
        }

        return true;
    }

    protected function matchesAtWordStart(string $haystack, string $needle): bool
    {
        $pattern = '/(?:^|[\s\-])' . preg_quote($needle, '/') . '/u';

        return preg_match($pattern, $haystack) === 1;
    }

    /**
     * @param  list<string>  $tokens
     */
    protected function allTokensMatchAtWordStart(string $haystack, array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (!$this->matchesAtWordStart($haystack, $token)) {
                return false;
            }
        }

        return true;
    }
}
