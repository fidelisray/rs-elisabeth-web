<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlossaryPopularityService;
use App\Services\HospitalApiService;

class GlossaryController extends Controller
{
    public function __construct(
        protected HospitalApiService $apiService,
        protected GlossaryPopularityService $popularityService
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

        // Jika ada pencarian
        if ($keyword !== '') {
            $lowerKeyword = strtolower($keyword);
            $matchedItems = collect($allItems)
                ->filter(function ($item) use ($lowerKeyword) {
                    return str_contains(strtolower((string) ($item['istilah'] ?? '')), $lowerKeyword) ||
                           str_contains(strtolower((string) ($item['slug'] ?? '')), $lowerKeyword) ||
                           str_contains(strtolower((string) ($item['deskripsi'] ?? '')), $lowerKeyword);
                })
                ->values()
                ->all();

            $glossary = collect($matchedItems)
                ->groupBy(function ($item) {
                    return ucfirst(substr($item['istilah'], 0, 2));
                })
                ->sortKeys()
                ->toArray();

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

        abort_if($currentIndex === false, 404, 'Istilah medis tidak ditemukan.');

        $item = $sortedAll->get($currentIndex);
        $this->popularityService->recordTermView($request, (string) ($item['slug'] ?? $decodedTerm));
        $prevItem = $currentIndex > 0 ? $sortedAll->get($currentIndex - 1) : null;
        $nextItem = $currentIndex < ($sortedAll->count() - 1) ? $sortedAll->get($currentIndex + 1) : null;

        $activeLetter = strtoupper(substr($item['istilah'], 0, 1));
        $activePrefix = ucfirst(strtolower(substr($item['istilah'], 0, 2)));

        $samePrefixItems = $sortedAll->filter(function ($entry) use ($item, $activePrefix) {
            return strtolower($entry['slug']) !== strtolower($item['slug'])
                && strtolower(substr($entry['istilah'], 0, 2)) === strtolower($activePrefix);
        });

        $sameLetterItems = $sortedAll->filter(function ($entry) use ($item, $activeLetter, $activePrefix) {
            return strtolower($entry['slug']) !== strtolower($item['slug'])
                && strtoupper(substr($entry['istilah'], 0, 1)) === $activeLetter
                && strtolower(substr($entry['istilah'], 0, 2)) !== strtolower($activePrefix);
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
     * Search via AJAX (Fetch API dari frontend)
    */
    public function search(Request $request)
    {
        
        $keyword = trim($request->query('q', ''));

        if (strlen($keyword) < 2) {
            return response()->json([]);
        }

        $all     = $this->apiService->getLocalGlossary();
        $results = collect($all)
            ->filter(fn($item) =>
                str_contains(strtolower($item['istilah']),   strtolower($keyword)) ||
                str_contains(strtolower($item['deskripsi']), strtolower($keyword))
            )
            ->values()
            ->take(20); // batasi hasil search

        // dd($results);
        return response()->json($results);
    }
}
