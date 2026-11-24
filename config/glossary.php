<?php

return [
    'popular_limit' => (int) env('GLOSSARY_POPULAR_LIMIT', 14),
    'empty_search_limit' => (int) env('GLOSSARY_EMPTY_SEARCH_LIMIT', 6),
    'top_relevant_search_limit' => (int) env('GLOSSARY_TOP_RELEVANT_LIMIT', 6),
    'excerpt_max_length' => (int) env('GLOSSARY_EXCERPT_MAX_LENGTH', 165),

    'window_days' => (int) env('GLOSSARY_WINDOW_DAYS', 30),
    'half_life_days' => (float) env('GLOSSARY_HALF_LIFE_DAYS', 10.0),
    'trending_window_days' => (int) env('GLOSSARY_TRENDING_WINDOW_DAYS', 7),

    'dedupe_ttl_seconds' => (int) env('GLOSSARY_DEDUPE_TTL', 900),
    'cache_fresh_seconds' => (int) env('GLOSSARY_POPULAR_CACHE_FRESH', 300),
    'cache_stale_seconds' => (int) env('GLOSSARY_POPULAR_CACHE_STALE', 3600),
    'retention_days' => (int) env('GLOSSARY_STATS_RETENTION_DAYS', 90),

    // Cold-start threshold before an organic term displaces curated defaults.
    'min_organic_hits' => (int) env('GLOSSARY_MIN_ORGANIC_HITS', 3),
    'min_score' => (float) env('GLOSSARY_MIN_SCORE', 4.0),

    // Laplace-smoothed 7d vs previous-7d velocity thresholds for "Sedang Tren".
    'trending_min_score' => (float) env('GLOSSARY_TRENDING_MIN_SCORE', 6.0),
    'trending_velocity_threshold' => (float) env('GLOSSARY_TRENDING_VELOCITY', 1.5),
    'trending_smoothing_alpha' => (float) env('GLOSSARY_TRENDING_ALPHA', 5.0),
    'trending_max_badges' => (int) env('GLOSSARY_TRENDING_MAX_BADGES', 3),

    'weights' => [
        'search_clicks' => 3.0,
        'directory_views' => 1.0,
        'direct_views' => 1.0,
        'internal_nav_views' => 0.5,
        'popular_clicks' => 0.2,
    ],

    // Up to 3 editorial or seasonal pinned slugs (comma-separated in .env).
    'pinned_slugs' => array_slice(
        array_values(array_filter(
            array_map(
                fn ($slug) => strtolower(trim((string) $slug)),
                explode(',', (string) env('GLOSSARY_PINNED_SLUGS', ''))
            )
        )),
        0,
        3
    ),

    // Additional internal reverse-proxy or staging referer hosts.
    'allowed_referer_hosts' => array_values(array_filter(
        array_map(
            fn ($host) => strtolower(trim((string) $host)),
            explode(',', (string) env('GLOSSARY_ALLOWED_REFERER_HOSTS', ''))
        )
    )),

    // Validated against both id_medical_glossary.json and en_medical_glossary.json.
    'curated_fallback_slugs' => [
        'diabetes',
        'type-2-diabetes',
        'stroke',
        'heart-attack',
        'heart-disease',
        'asthma',
        'dengue-fever',
        'typhoid-fever',
        'gastritis',
        'gerd',
        'anemia',
        'migraine-headache',
        'allergies',
        'chronic-kidney-disease',
        'kidney-failure',
        'pneumonia',
        'tuberculosis',
        'appendicitis',
        'arthritis',
        'osteoporosis',
        'epilepsy',
        'atrial-fibrillation',
    ],
];
