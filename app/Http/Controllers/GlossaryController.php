<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlossaryPopularityService;
use App\Services\GlossarySearchService;
use App\Services\HospitalApiService;

class GlossaryController extends Controller
{
    public function __construct(
        protected HospitalApiService $apiService,
        protected GlossaryPopularityService $popularityService,
        protected GlossarySearchService $searchService
    ) {}

    
    /*
    public function index(Request $request) {

        $glosarium = $this->apiService->GetGlosarium();

        return view('glosarium', compact('glosarium'));
    } */

    /**
     * Halaman utama kamus medis
     */
    public function index(Request $request)
    {
        $rawKeyword = $request->query('q', '');
        $keyword = is_string($rawKeyword) ? trim($rawKeyword) : '';
        // Ambil filter huruf dari query string (?letter=A), default 'ALL'
        $rawLetter = $request->query('letter', 'ALL');
        $activeLetter = is_string($rawLetter) ? strtoupper(trim($rawLetter)) : 'ALL';
        
        // Validasi: harus huruf A-Z atau ALL
        if ($activeLetter !== 'ALL' && !preg_match('/^[A-Z]$/', $activeLetter)) {
            $activeLetter = 'ALL';
        }

        // Hitung huruf yang tersedia untuk navigasi A-Z
        $allItems = $this->apiService->getLocalGlossary();
        $totalTerms = count($allItems);
        $availableLetters = collect($allItems)
            ->map(fn($item) => strtoupper(substr($item['istilah'], 0, 1)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Jika ada pencarian (pencarian murni khusus judul dengan token-based AND)
        if ($keyword !== '') {
            $matchedItems = $this->searchService->searchTitleMatches($allItems, $keyword);
            $glossary = $this->searchService->groupByPrefix($matchedItems);

            $topRelevantTerms = [];
            $popularTerms = [];
            $popularMeta = [
                'mode' => 'curated',
                'qualified_count' => 0,
                'window_days' => (int) config('glossary.window_days', 30),
            ];

            if (!empty($matchedItems)) {
                $topRelevantTerms = $this->popularityService->getTopRelevantSearchMatches($matchedItems, $keyword);
            } else {
                $emptySearchLimit = (int) config('glossary.empty_search_limit', 6);
                $popularData = $this->popularityService->getPopularTerms($allItems, $emptySearchLimit);
                $popularTerms = $popularData['terms'];
                $popularMeta = $popularData['meta'];
            }

            return view('glosarium.index', [
                'mode' => 'glosarium',
                'glossary' => $glossary,
                'totalTerms' => $totalTerms,
                'topRelevantTerms' => $topRelevantTerms,
                'popularTerms' => $popularTerms,
                'popularMeta' => $popularMeta,
                'activeLetter' => 'ALL',
                'availableLetters' => $availableLetters,
                'keyword' => $keyword,
            ]);
        }

        if ($activeLetter === 'ALL') {
            $popularData = $this->popularityService->getPopularTerms($allItems);

            return view('glosarium.index', [
                'mode' => 'explore',
                'glossary' => [],
                'totalTerms' => $totalTerms,
                'popularTerms' => $popularData['terms'],
                'popularMeta' => $popularData['meta'],
                'topRelevantTerms' => [],
                'activeLetter' => 'ALL',
                'availableLetters' => $availableLetters,
                'keyword' => '',
            ]);
        }

        $glossary = $this->apiService->getLocalGlossaryGrouped($activeLetter);

        return view('glosarium.index', [
            'mode' => 'glosarium',
            'glossary' => $glossary,
            'totalTerms' => $totalTerms,
            'topRelevantTerms' => [],
            'popularTerms' => [],
            'popularMeta' => [
                'mode' => 'curated',
                'qualified_count' => 0,
                'window_days' => (int) config('glossary.window_days', 30),
            ],
            'activeLetter' => $activeLetter,
            'availableLetters' => $availableLetters,
            'keyword' => '',
        ]);
    }

    /**
     * Detail satu istilah medis
     */
    public function show(Request $request, string $term)
    {
        $all  = $this->apiService->getLocalGlossary();
        // Pencarian berbasis slug (field 'name' di file JSON) yang sudah di-urlencode,
        // case-insensitive agar "hypertension" dan "Hypertension" sama-sama ditemukan.
        $decodedTerm = strtolower(urldecode($term));
        $sortedAll = collect($all)
            ->sortBy(fn($entry) => strtolower($entry['istilah']))
            ->values();

        $currentIndex = $sortedAll->search(
            fn($i) => strtolower($i['slug']) === $decodedTerm
                || strtolower($i['istilah']) === $decodedTerm
        );

        abort_if($currentIndex === false, 404, __('Istilah medis tidak ditemukan.'));

        $item = $sortedAll->get($currentIndex);
        $this->popularityService->recordTermView($request, (string) ($item['slug'] ?? $decodedTerm));
        $prevItem = $currentIndex > 0 ? $sortedAll->get($currentIndex - 1) : null;
        $nextItem = $currentIndex < ($sortedAll->count() - 1) ? $sortedAll->get($currentIndex + 1) : null;

        $activeLetter = strtoupper(substr($item['istilah'], 0, 1));
        $activePrefix = $this->searchService->extractPrefix((string) ($item['istilah'] ?? ''));

        $samePrefixItems = $sortedAll->filter(function ($entry) use ($item, $activePrefix) {
            return strtolower($entry['slug']) !== strtolower($item['slug'])
                && strtolower($this->searchService->extractPrefix((string) ($entry['istilah'] ?? ''))) === strtolower($activePrefix);
        });

        $sameLetterItems = $sortedAll->filter(function ($entry) use ($item, $activeLetter, $activePrefix) {
            return strtolower($entry['slug']) !== strtolower($item['slug'])
                && strtoupper(substr($entry['istilah'], 0, 1)) === $activeLetter
                && strtolower($this->searchService->extractPrefix((string) ($entry['istilah'] ?? ''))) !== strtolower($activePrefix);
        });

        $relatedItems = $samePrefixItems
            ->concat($sameLetterItems)
            ->take(8)
            ->values()
            ->all();

        $rawDescription = trim((string) ($item['deskripsi'] ?? ''));
        $sentences = preg_split('/(?<=[.!?])\s+/u', $rawDescription, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $leadParagraph = '';
        $bodyParagraphs = [];

        if (count($sentences) > 2) {
            $leadParagraph = implode(' ', array_slice($sentences, 0, 2));
            $chunks = array_chunk(array_slice($sentences, 2), 3);
            foreach ($chunks as $chunk) {
                $bodyParagraphs[] = implode(' ', $chunk);
            }
        } else {
            $leadParagraph = $rawDescription;
        }

        // dd($item);

        // return view('glossary.show', compact('item'));
        return view('glosarium.show', compact(
            'item',
            'prevItem',
            'nextItem',
            'activeLetter',
            'activePrefix',
            'relatedItems',
            'leadParagraph',
            'bodyParagraphs'
        ));
    }

    /**
     * Autocomplete suggestion endpoint (Fetch API dari search-box glosarium).
     */
    public function search(Request $request)
    {
        $rawQuery = $request->query('q', '');
        $keyword = is_string($rawQuery) ? trim($rawQuery) : '';

        $all = $this->apiService->getLocalGlossary();

        if ($keyword === '') {
            $payload = $this->searchService->popularSuggestions($all, $this->popularityService, 6);
        } else {
            $payload = $this->searchService->suggest($all, $keyword, $this->popularityService, 8);
        }

        return response()
            ->json($payload)
            ->header('Cache-Control', 'private, no-store');
    }
}
