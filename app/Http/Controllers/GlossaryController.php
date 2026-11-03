<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HospitalApiService;

class GlossaryController extends Controller
{
    //
    public function __construct(
        protected HospitalApiService $apiService
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
        $keyword = trim($request->query('q', ''));
        // Ambil filter huruf dari query string (?letter=A), default 'ALL'
        $activeLetter = strtoupper($request->query('letter', 'ALL'));
        
        // Validasi: harus huruf A-Z atau ALL
        if ($activeLetter !== 'ALL' && !preg_match('/^[A-Z]$/', $activeLetter)) {
            $activeLetter = 'ALL';
        }

        // Hitung huruf yang tersedia untuk navigasi A-Z
        $allItems       = $this->apiService->getLocalGlossary();
        $availableLetters = collect($allItems)
            ->map(fn($item) => strtoupper(substr($item['istilah'], 0, 1)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // Jika ada pencarian
        if ($keyword !== '') {
            $glossary = collect($allItems)
                ->filter(function($item) use ($keyword) {
                    return str_contains(strtolower($item['istilah']), strtolower($keyword)) || 
                           str_contains(strtolower($item['deskripsi']), strtolower($keyword));
                })
                ->values()
                ->groupBy(function ($item) {
                    return ucfirst(substr($item['istilah'], 0, 2));
                })
                ->sortKeys()
                ->toArray();

            return view('glosarium.index', [
                'mode' => 'glosarium',
                'glossary' => $glossary,
                'activeLetter' => 'ALL',
                'availableLetters' => $availableLetters,
                'keyword' => $keyword
            ]);
        }

        // Ambil data kamus medis lokal dari file JSON (dengan mekanisme cache per-locale)
        $glossary = $this->apiService->getLocalGlossaryGrouped($activeLetter);

        if ($activeLetter === 'ALL') {
            return view('glosarium.index', [
                'mode' => 'explore',
                'glossary' => $glossary,
                'activeLetter' => 'ALL',
                'availableLetters' => $availableLetters,
                'keyword' => ''
            ]);
        } else {
    
            // return view('glossary.index', compact('glossary', 'activeLetter', 'availableLetters'));
    
            // return view('glosarium.index', compact('glossary', 'activeLetter', 'availableLetters'));
            return view('glosarium.index', [
                'mode' => 'glosarium',
                'glossary' => $glossary,
                'activeLetter' => $activeLetter,
                'availableLetters' => $availableLetters,
                'keyword' => ''
            ]);
        }
    }

    /**
     * Detail satu istilah medis
     */
    public function show(string $term)
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
