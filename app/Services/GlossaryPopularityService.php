<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlossaryPopularityService
{
    public const RANKING_CACHE_KEY = 'glossary_popular_ranking_v1';

    public const VALID_COLUMNS = [
        'search_clicks',
        'directory_views',
        'direct_views',
        'internal_nav_views',
        'popular_clicks',
    ];

    protected const BOT_PATTERN = '/bot|crawl|spider|slurp|mediapartners|adsbot|bingpreview|facebookexternalhit|whatsapp|telegrambot|twitterbot|linkedinbot|embedly|quora|pinterest|slackbot|discordbot|applebot|yandex|baiduspider|duckduckbot|semrush|ahrefs|mj12bot|dotbot|petalbot|bytespider|gptbot|claudebot|ccbot|lighthouse|headlesschrome|phantomjs|puppeteer|playwright|curl|wget|python-requests|go-http-client|axios|okhttp|postmanruntime|insomnia|uptimerobot|pingdom|statuscake|datadog|newrelic/i';

    public function __construct(
        protected ?GlossarySearchService $searchService = null
    ) {}

    public function recordTermView(Request $request, string $canonicalSlug, bool $deferToTerminating = true): void
    {
        $slug = strtolower(trim($canonicalSlug));
        if ($slug === '' || strlen($slug) > 100) {
            return;
        }

        if (!$this->shouldTrack($request)) {
            return;
        }

        $column = $this->classifySource($request, $slug);
        if ($column === null || !in_array($column, self::VALID_COLUMNS, true)) {
            return;
        }

        $visitorHash = $this->resolveVisitorFingerprint($request);
        $dedupeKey = "glos_hit:{$visitorHash}:{$slug}";
        $dedupeTtl = max(60, (int) config('glossary.dedupe_ttl_seconds', 900));

        try {
            if (!Cache::add($dedupeKey, 1, $dedupeTtl)) {
                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Glossary popularity dedupe cache unavailable', [
                'error' => $e->getMessage(),
            ]);
            return;
        }

        if ($deferToTerminating && !app()->runningUnitTests()) {
            app()->terminating(function () use ($slug, $column) {
                $this->incrementDailyStat($slug, $column);
            });
            return;
        }

        $this->incrementDailyStat($slug, $column);
    }

    public function shouldTrack(Request $request): bool
    {
        if (strtoupper($request->getRealMethod()) !== 'GET' || !$request->isMethod('GET')) {
            return false;
        }

        $prefetchHeaders = [
            (string) $request->header('Purpose', ''),
            (string) $request->header('X-Purpose', ''),
            (string) $request->header('Sec-Purpose', ''),
            (string) $request->header('X-Moz', ''),
        ];

        foreach ($prefetchHeaders as $headerVal) {
            if ($headerVal !== '' && preg_match('/prefetch|preview|prerender/i', $headerVal)) {
                return false;
            }
        }

        $userAgent = trim((string) $request->userAgent());
        if ($userAgent === '' || preg_match(self::BOT_PATTERN, $userAgent)) {
            return false;
        }

        return true;
    }

    public function classifySource(Request $request, string $canonicalSlug): ?string
    {
        $slug = strtolower(trim($canonicalSlug));
        $referer = trim((string) $request->headers->get('referer', ''));

        $isInternalReferer = false;
        $refererPath = '/';
        $refererQuery = '';

        if ($referer !== '') {
            $isRelativeInternal = str_starts_with($referer, '/') && !str_starts_with($referer, '//');
            $parsedReferer = parse_url($referer);

            if (is_array($parsedReferer)) {
                $refererScheme = strtolower((string) ($parsedReferer['scheme'] ?? ''));
                $refererHost = strtolower((string) ($parsedReferer['host'] ?? ''));
                $refererPath = '/' . trim((string) ($parsedReferer['path'] ?? ''), '/');
                $refererQuery = (string) ($parsedReferer['query'] ?? '');

                if ($isRelativeInternal) {
                    $isInternalReferer = true;
                } elseif (
                    in_array($refererScheme, ['http', 'https'], true)
                    && $refererHost !== ''
                    && $this->isAllowedInternalHost($refererHost, $request)
                ) {
                    $isInternalReferer = true;
                }
            }

            if (
                $isInternalReferer
                && preg_match('/^\/glosarium\/([^\/?#]+)/i', $refererPath, $matches)
            ) {
                $refererSlug = strtolower(trim(urldecode($matches[1])));
                $normalizedRefererSlug = str_replace(' ', '-', $refererSlug);
                if ($refererSlug === $slug || $normalizedRefererSlug === $slug) {
                    return null;
                }
            }
        }

        $rawSrc = $request->query('src', '');
        $explicitSrc = is_string($rawSrc) ? strtolower(trim($rawSrc)) : '';
        if ($explicitSrc !== '') {
            $mapped = match ($explicitSrc) {
                'search' => 'search_clicks',
                'dir' => 'directory_views',
                'rel' => 'internal_nav_views',
                'pop' => 'popular_clicks',
                default => null,
            };

            if ($mapped !== null) {
                return $mapped;
            }
        }

        if ($referer === '' || !$isInternalReferer) {
            return 'direct_views';
        }

        if ($refererPath === '/glosarium') {
            $queryParams = [];
            if ($refererQuery !== '') {
                parse_str($refererQuery, $queryParams);
            }

            $qParam = $queryParams['q'] ?? '';
            if (is_string($qParam) && trim($qParam) !== '') {
                return 'search_clicks';
            }

            $letterParam = $queryParams['letter'] ?? '';
            if (is_string($letterParam) && trim($letterParam) !== '') {
                return 'directory_views';
            }

            return 'popular_clicks';
        }

        if (preg_match('/^\/glosarium\/([^\/?#]+)/i', $refererPath, $matches)) {
            $refererSlug = strtolower(trim(urldecode($matches[1])));
            $normalizedRefererSlug = str_replace(' ', '-', $refererSlug);
            if ($refererSlug === $slug || $normalizedRefererSlug === $slug) {
                return null;
            }

            return 'internal_nav_views';
        }

        return 'internal_nav_views';
    }

    protected function isAllowedInternalHost(string $refererHost, Request $request): bool
    {
        $normalized = strtolower(trim($refererHost));
        if ($normalized === '') {
            return false;
        }

        $appUrlHost = strtolower((string) parse_url((string) config('app.url', ''), PHP_URL_HOST));
        $requestHost = strtolower((string) $request->getHost());
        $configuredHosts = array_map(
            fn ($h) => strtolower(trim((string) $h)),
            (array) config('glossary.allowed_referer_hosts', [])
        );

        $localHosts = app()->environment(['local', 'testing'])
            ? ['localhost', '127.0.0.1']
            : [];

        $allowed = array_values(array_unique(array_filter(array_merge(
            [$requestHost, $appUrlHost],
            $localHosts,
            $configuredHosts
        ))));

        return in_array($normalized, $allowed, true);
    }

    protected function resolveVisitorFingerprint(Request $request): string
    {
        $sessionCookieName = (string) config('session.cookie', 'laravel_session');
        $hasSessionCookie = $request->cookies->has($sessionCookieName);

        if ($hasSessionCookie && $request->hasSession() && $request->session()->getId() !== '') {
            $rawIdentity = 'sess:' . $request->session()->getId();
        } else {
            $ip = (string) ($request->ip() ?? '0.0.0.0');
            $ua = substr((string) $request->userAgent(), 0, 160);
            $rawIdentity = "ipua:{$ip}|{$ua}";
        }

        $secret = (string) config('app.key', 'glossary-popularity-fallback-key');

        return substr(hash_hmac('sha256', $rawIdentity, $secret), 0, 16);
    }

    public function incrementDailyStat(string $canonicalSlug, string $column, ?string $date = null): void
    {
        if (!in_array($column, self::VALID_COLUMNS, true)) {
            return;
        }

        $slug = strtolower(trim($canonicalSlug));
        if ($slug === '' || strlen($slug) > 100) {
            return;
        }

        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $statDate = $date ?? Carbon::now($tz)->toDateString();
        $nowTimestamp = Carbon::now($tz)->toDateTimeString();

        try {
            DB::statement(
                "INSERT INTO glossary_term_daily_stats (
                    slug, stat_date, search_clicks, directory_views, direct_views, internal_nav_views, popular_clicks, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (slug, stat_date) DO UPDATE SET
                    {$column} = glossary_term_daily_stats.{$column} + 1,
                    updated_at = EXCLUDED.updated_at",
                [
                    $slug,
                    $statDate,
                    $column === 'search_clicks' ? 1 : 0,
                    $column === 'directory_views' ? 1 : 0,
                    $column === 'direct_views' ? 1 : 0,
                    $column === 'internal_nav_views' ? 1 : 0,
                    $column === 'popular_clicks' ? 1 : 0,
                    $nowTimestamp,
                    $nowTimestamp,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to upsert glossary_term_daily_stats', [
                'slug' => $slug,
                'column' => $column,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getPopularTerms(array $allGlossaryItems, ?int $limit = null): array
    {
        $limit = max(1, $limit ?? (int) config('glossary.popular_limit', 14));

        $lookupBySlug = [];
        foreach ($allGlossaryItems as $item) {
            if (!isset($item['slug'])) {
                continue;
            }
            $key = strtolower(trim((string) $item['slug']));
            if ($key !== '' && !isset($lookupBySlug[$key])) {
                $lookupBySlug[$key] = $item;
            }
        }

        $ranking = $this->getCachedRanking();
        $rankedEntries = $ranking['entries'] ?? [];

        $trendingMaxBadges = max(0, (int) config('glossary.trending_max_badges', 3));
        $trendingAssigned = 0;

        $popularTerms = [];
        $seenSlugs = [];

        foreach ($rankedEntries as $entry) {
            $slug = strtolower(trim((string) ($entry['slug'] ?? '')));
            if ($slug === '' || isset($seenSlugs[$slug]) || !isset($lookupBySlug[$slug])) {
                continue;
            }

            $baseItem = $lookupBySlug[$slug];
            $isTrending = !empty($entry['is_trending']) && $trendingAssigned < $trendingMaxBadges;
            if ($isTrending) {
                $trendingAssigned++;
            }

            $seenSlugs[$slug] = true;
            $popularTerms[] = array_merge($baseItem, [
                'rank' => count($popularTerms) + 1,
                'is_trending' => $isTrending,
                'is_pinned' => (bool) ($entry['is_pinned'] ?? false),
                'is_organic' => (bool) ($entry['is_organic'] ?? false),
                'score_30d' => round((float) ($entry['score_30d'] ?? 0.0), 2),
                'score_7d' => round((float) ($entry['score_7d'] ?? 0.0), 2),
                'velocity' => round((float) ($entry['velocity'] ?? 1.0), 2),
                'organic_hits' => (int) ($entry['organic_hits'] ?? 0),
                'lead_excerpt' => $this->extractLeadExcerpt((string) ($baseItem['deskripsi'] ?? '')),
            ]);

            if (count($popularTerms) >= $limit) {
                break;
            }
        }

        // Backfill from remaining dictionary items if dictionary was smaller than curated list.
        if (count($popularTerms) < $limit && !empty($lookupBySlug)) {
            foreach ($lookupBySlug as $slug => $baseItem) {
                if (isset($seenSlugs[$slug])) {
                    continue;
                }

                $seenSlugs[$slug] = true;
                $popularTerms[] = array_merge($baseItem, [
                    'rank' => count($popularTerms) + 1,
                    'is_trending' => false,
                    'is_pinned' => false,
                    'is_organic' => false,
                    'score_30d' => 0.0,
                    'score_7d' => 0.0,
                    'velocity' => 1.0,
                    'organic_hits' => 0,
                    'lead_excerpt' => $this->extractLeadExcerpt((string) ($baseItem['deskripsi'] ?? '')),
                ]);

                if (count($popularTerms) >= $limit) {
                    break;
                }
            }
        }

        return [
            'terms' => $popularTerms,
            'meta' => [
                'mode' => (string) ($ranking['mode'] ?? 'curated'),
                'qualified_count' => (int) ($ranking['qualified_count'] ?? 0),
                'window_days' => (int) config('glossary.window_days', 30),
                'updated_at' => (string) ($ranking['updated_at'] ?? Carbon::now()->toIso8601String()),
            ],
        ];
    }

    public function getCachedRanking(): array
    {
        $freshSeconds = max(1, (int) config('glossary.cache_fresh_seconds', 300));
        $staleSeconds = max($freshSeconds, (int) config('glossary.cache_stale_seconds', 3600));

        try {
            return Cache::flexible(
                self::RANKING_CACHE_KEY,
                [$freshSeconds, $staleSeconds],
                fn () => $this->computePopularRanking(true)
            );
        } catch (\Throwable $e) {
            Log::warning('Glossary popularity ranking cache fallback triggered', [
                'error' => $e->getMessage(),
            ]);

            return $this->buildCuratedFallbackRanking();
        }
    }

    public function computePopularRanking(bool $throwOnError = false): array
    {
        try {
            $tz = (string) config('app.timezone', 'Asia/Jakarta');
            $today = Carbon::now($tz)->startOfDay();
            $windowDays = max(1, (int) config('glossary.window_days', 30));
            $halfLifeDays = max(0.1, (float) config('glossary.half_life_days', 10.0));
            $trendingWindowDays = max(1, (int) config('glossary.trending_window_days', 7));

            $startDate = $today->copy()->subDays($windowDays - 1)->toDateString();
            $endDate = $today->toDateString();

            $weights = (array) config('glossary.weights', []);
            $wSearch = (float) ($weights['search_clicks'] ?? 3.0);
            $wDir = (float) ($weights['directory_views'] ?? 1.0);
            $wDirect = (float) ($weights['direct_views'] ?? 1.0);
            $wInternal = (float) ($weights['internal_nav_views'] ?? 0.5);
            $wPopular = (float) ($weights['popular_clicks'] ?? 0.2);

            $rows = DB::table('glossary_term_daily_stats')
                ->whereBetween('stat_date', [$startDate, $endDate])
                ->get();

            $statsBySlug = [];

            foreach ($rows as $row) {
                $slug = strtolower(trim((string) $row->slug));
                if ($slug === '') {
                    continue;
                }

                $rowDate = Carbon::parse((string) $row->stat_date, $tz)->startOfDay();
                $ageInDays = max(0, (int) round($rowDate->diffInDays($today, false)));

                $searchClicks = (int) $row->search_clicks;
                $directoryViews = (int) $row->directory_views;
                $directViews = (int) $row->direct_views;
                $internalNavViews = (int) $row->internal_nav_views;
                $popularClicks = (int) $row->popular_clicks;

                $dailyRaw = ($wSearch * $searchClicks)
                    + ($wDir * $directoryViews)
                    + ($wDirect * $directViews)
                    + ($wInternal * $internalNavViews)
                    + ($wPopular * $popularClicks);

                $dailyOrganicHits = $searchClicks + $directoryViews + $directViews + $internalNavViews;
                $decayFactor = pow(0.5, $ageInDays / $halfLifeDays);
                $decayedScore = $dailyRaw * $decayFactor;

                if (!isset($statsBySlug[$slug])) {
                    $statsBySlug[$slug] = [
                        'slug' => $slug,
                        'score_30d' => 0.0,
                        'score_7d' => 0.0,
                        'score_prev_7d' => 0.0,
                        'organic_hits' => 0,
                        'search_clicks' => 0,
                        'directory_views' => 0,
                        'direct_views' => 0,
                        'internal_nav_views' => 0,
                        'popular_clicks' => 0,
                    ];
                }

                $statsBySlug[$slug]['score_30d'] += $decayedScore;
                $statsBySlug[$slug]['organic_hits'] += $dailyOrganicHits;
                $statsBySlug[$slug]['search_clicks'] += $searchClicks;
                $statsBySlug[$slug]['directory_views'] += $directoryViews;
                $statsBySlug[$slug]['direct_views'] += $directViews;
                $statsBySlug[$slug]['internal_nav_views'] += $internalNavViews;
                $statsBySlug[$slug]['popular_clicks'] += $popularClicks;

                if ($ageInDays < $trendingWindowDays) {
                    $statsBySlug[$slug]['score_7d'] += $dailyRaw;
                } elseif ($ageInDays < ($trendingWindowDays * 2)) {
                    $statsBySlug[$slug]['score_prev_7d'] += $dailyRaw;
                }
            }

            $minOrganicHits = (int) config('glossary.min_organic_hits', 3);
            $minScore = (float) config('glossary.min_score', 4.0);
            $alpha = max(0.1, (float) config('glossary.trending_smoothing_alpha', 5.0));
            $trendingMinScore = (float) config('glossary.trending_min_score', 6.0);
            $trendingVelocityThreshold = (float) config('glossary.trending_velocity_threshold', 1.5);
            $trendingMaxBadges = max(0, (int) config('glossary.trending_max_badges', 3));

            $qualifiedEntries = [];
            foreach ($statsBySlug as $slug => &$stat) {
                $stat['velocity'] = ($stat['score_7d'] + $alpha) / ($stat['score_prev_7d'] + $alpha);
                $stat['is_qualified'] = $stat['organic_hits'] >= $minOrganicHits
                    && $stat['score_30d'] >= $minScore;

                if ($stat['is_qualified']) {
                    $qualifiedEntries[] = $stat;
                }
            }
            unset($stat);

            usort($qualifiedEntries, function (array $a, array $b): int {
                $scoreDiff = $b['score_30d'] <=> $a['score_30d'];
                if ($scoreDiff !== 0) {
                    return $scoreDiff;
                }

                $hitsDiff = $b['organic_hits'] <=> $a['organic_hits'];
                if ($hitsDiff !== 0) {
                    return $hitsDiff;
                }

                return strcmp($a['slug'], $b['slug']);
            });

            $popularLimit = max(1, (int) config('glossary.popular_limit', 14));
            $qualifiedCount = count($qualifiedEntries);

            $mode = 'curated';
            if ($qualifiedCount >= $popularLimit) {
                $mode = 'live';
            } elseif ($qualifiedCount > 0) {
                $mode = 'hybrid';
            }

            $composed = [];
            $seen = [];

            $pinnedSlugs = array_slice(
                array_values(array_filter(
                    array_map(
                        fn ($s) => strtolower(trim((string) $s)),
                        (array) config('glossary.pinned_slugs', [])
                    )
                )),
                0,
                3
            );

            foreach ($pinnedSlugs as $pinSlug) {
                if ($pinSlug === '' || isset($seen[$pinSlug])) {
                    continue;
                }

                $stat = $statsBySlug[$pinSlug] ?? $this->emptyStatEntry($pinSlug);
                $stat['is_pinned'] = true;
                $stat['is_organic'] = (bool) ($stat['is_qualified'] ?? false);
                $stat['is_trending'] = false;

                $composed[] = $stat;
                $seen[$pinSlug] = true;
            }

            foreach ($qualifiedEntries as $qStat) {
                $slug = $qStat['slug'];
                if (isset($seen[$slug])) {
                    continue;
                }

                $qStat['is_pinned'] = false;
                $qStat['is_organic'] = true;
                $qStat['is_trending'] = false;

                $composed[] = $qStat;
                $seen[$slug] = true;
            }

            $curatedSlugs = (array) config('glossary.curated_fallback_slugs', []);
            foreach ($curatedSlugs as $curatedSlug) {
                $slug = strtolower(trim((string) $curatedSlug));
                if ($slug === '' || isset($seen[$slug])) {
                    continue;
                }

                $stat = $statsBySlug[$slug] ?? $this->emptyStatEntry($slug);
                $stat['is_pinned'] = false;
                $stat['is_organic'] = false;
                $stat['is_trending'] = false;

                $composed[] = $stat;
                $seen[$slug] = true;
            }

            // Determine top trending badges among qualified organic terms when mode is not pure cold-start curated.
            if ($mode !== 'curated' && $trendingMaxBadges > 0) {
                $trendingCandidates = [];
                foreach ($composed as $idx => $entry) {
                    if ($idx >= $popularLimit) {
                        break;
                    }
                    if (
                        !empty($entry['is_organic'])
                        && $entry['score_7d'] >= $trendingMinScore
                        && $entry['velocity'] >= $trendingVelocityThreshold
                    ) {
                        $trendingCandidates[] = [
                            'index' => $idx,
                            'velocity' => $entry['velocity'],
                            'score_7d' => $entry['score_7d'],
                        ];
                    }
                }

                usort($trendingCandidates, function (array $a, array $b): int {
                    $vDiff = $b['velocity'] <=> $a['velocity'];
                    if ($vDiff !== 0) {
                        return $vDiff;
                    }

                    return $b['score_7d'] <=> $a['score_7d'];
                });

                foreach (array_slice($trendingCandidates, 0, $trendingMaxBadges) as $winner) {
                    $composed[$winner['index']]['is_trending'] = true;
                }
            }

            return [
                'mode' => $mode,
                'qualified_count' => $qualifiedCount,
                'updated_at' => Carbon::now($tz)->toIso8601String(),
                'entries' => $composed,
            ];
        } catch (\Throwable $e) {
            if ($throwOnError) {
                throw $e;
            }

            Log::warning('Glossary popularity computation failed, falling back to curated list', [
                'error' => $e->getMessage(),
            ]);

            return $this->buildCuratedFallbackRanking();
        }
    }

    protected function buildCuratedFallbackRanking(): array
    {
        $entries = [];
        $seen = [];

        $pinnedSlugs = array_slice(
            array_values(array_filter(
                array_map(
                    fn ($s) => strtolower(trim((string) $s)),
                    (array) config('glossary.pinned_slugs', [])
                )
            )),
            0,
            3
        );

        foreach ($pinnedSlugs as $pinSlug) {
            if ($pinSlug !== '' && !isset($seen[$pinSlug])) {
                $entry = $this->emptyStatEntry($pinSlug);
                $entry['is_pinned'] = true;
                $entries[] = $entry;
                $seen[$pinSlug] = true;
            }
        }

        foreach ((array) config('glossary.curated_fallback_slugs', []) as $rawSlug) {
            $slug = strtolower(trim((string) $rawSlug));
            if ($slug !== '' && !isset($seen[$slug])) {
                $entries[] = $this->emptyStatEntry($slug);
                $seen[$slug] = true;
            }
        }

        return [
            'mode' => 'curated',
            'qualified_count' => 0,
            'updated_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta'))->toIso8601String(),
            'entries' => $entries,
        ];
    }

    protected function emptyStatEntry(string $slug): array
    {
        return [
            'slug' => $slug,
            'score_30d' => 0.0,
            'score_7d' => 0.0,
            'score_prev_7d' => 0.0,
            'velocity' => 1.0,
            'organic_hits' => 0,
            'search_clicks' => 0,
            'directory_views' => 0,
            'direct_views' => 0,
            'internal_nav_views' => 0,
            'popular_clicks' => 0,
            'is_qualified' => false,
            'is_pinned' => false,
            'is_organic' => false,
            'is_trending' => false,
        ];
    }

    public function extractLeadExcerpt(string $description, ?int $maxLength = null): string
    {
        $maxLength = max(40, $maxLength ?? (int) config('glossary.excerpt_max_length', 165));
        $clean = trim((string) preg_replace('/\s+/u', ' ', strip_tags($description)));

        if ($clean === '') {
            return __('Klik istilah untuk membaca penjelasan klinis lengkap.');
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $clean, 2, PREG_SPLIT_NO_EMPTY);
        $firstSentence = trim((string) ($sentences[0] ?? $clean));

        if (mb_strlen($firstSentence) <= $maxLength) {
            return $firstSentence;
        }

        $truncated = mb_substr($firstSentence, 0, $maxLength - 3);
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > (int) ($maxLength * 0.5)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, " \t\n\r\0\x0B.,;:") . '...';
    }

    public function getTopRelevantSearchMatches(array $matchedItems, string $keyword, ?int $limit = null): array
    {
        $limit = max(1, $limit ?? (int) config('glossary.top_relevant_search_limit', 6));
        $trimmed = trim($keyword);

        if ($trimmed === '' || empty($matchedItems)) {
            return [];
        }

        $searchService = $this->searchService ??= new GlossarySearchService();
        $ranked = $searchService->searchTitleMatches($matchedItems, $trimmed);
        $topMatches = array_slice($ranked, 0, $limit);

        return array_map(function (array $item): array {
            $rawDesc = trim((string) ($item['deskripsi'] ?? ''));

            return array_merge($item, [
                'lead_excerpt' => $this->extractLeadExcerpt($rawDesc),
            ]);
        }, $topMatches);
    }

    public function getDetailedStatsReport(array $allGlossaryItems, ?int $limit = null): array
    {
        $limit = max(1, $limit ?? (int) config('glossary.popular_limit', 14));
        $ranking = $this->computePopularRanking();

        $lookupBySlug = [];
        foreach ($allGlossaryItems as $item) {
            if (isset($item['slug'])) {
                $lookupBySlug[strtolower(trim((string) $item['slug']))] = $item;
            }
        }

        $trendingMaxBadges = max(0, (int) config('glossary.trending_max_badges', 3));
        $trendingAssigned = 0;
        $rows = [];

        foreach ($ranking['entries'] as $entry) {
            $slug = $entry['slug'];
            if (!isset($lookupBySlug[$slug])) {
                continue;
            }

            $isTrending = !empty($entry['is_trending']) && $trendingAssigned < $trendingMaxBadges;
            if ($isTrending) {
                $trendingAssigned++;
            }

            $rows[] = array_merge($entry, [
                'rank' => count($rows) + 1,
                'istilah' => $lookupBySlug[$slug]['istilah'] ?? $slug,
                'is_trending' => $isTrending,
            ]);

            if (count($rows) >= $limit) {
                break;
            }
        }

        return [
            'mode' => $ranking['mode'],
            'qualified_count' => $ranking['qualified_count'],
            'updated_at' => $ranking['updated_at'],
            'rows' => $rows,
        ];
    }

    public function clearRankingCache(): void
    {
        try {
            Cache::forget(self::RANKING_CACHE_KEY);
            Cache::forget('illuminate:cache:flexible:created:' . self::RANKING_CACHE_KEY);
        } catch (\Throwable $e) {
            Log::warning('Failed to clear glossary popular ranking cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
