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
        $allItems       = $this->apiService->getCachedGlosarium();
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

        // Ambil data (dari cache kalau ada, dari API kalau tidak)
        // $glossary = $this->apiService->getGlossaryByLetter($activeLetter);
        $glossary = $this->apiService->getGlossaryGrouped($activeLetter);

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
        $all  = $this->apiService->getCachedGlosarium();
        // PERBAIKAN: Gunakan pencarian case-insensitive agar "hipertensi" dan "Hipertensi"
        // sama-sama bisa ditemukan, mencegah false 404.
        $decodedTerm = strtolower(urldecode($term));
        $item = collect($all)->first(
            fn($i) => strtolower($i['istilah']) === $decodedTerm
        );

        abort_if(!$item, 404, 'Istilah medis tidak ditemukan.');

        // dd($item);

        // return view('glossary.show', compact('item'));
        return view('glosarium.show', compact('item'));
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

        $all     = $this->apiService->getCachedGlosarium();
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

    public function tampil_data() {
        // 1. Ambil file JSON dari storage
        $jsonPath = storage_path('app/json/hasil_scraping2.json');
        
        if (!file_exists($jsonPath)) {
            abort(404, "File data tidak ditemukan.");
        }

        $jsonContent = file_get_contents($jsonPath);
        $rawData = json_decode($jsonContent, true);

        $glossaryData = [];

        // 2. Olah struktur data agar lebih mudah di-loop di Blade
        foreach ($rawData['data'] as $disease) {
            foreach ($disease as $slug => $details) {
                // Mengubah "atrial-fibrillation" menjadi "Atrial Fibrillation"
                $formattedTitle = ucwords(str_replace('-', ' ', $slug));

                $glossaryData[] = [
                    'slug' => $slug,
                    'title' => $formattedTitle,
                    'url' => $details['url'],
                    'sections' => $details['sections']
                ];
            }
        }

        // 3. Kirim data ke view
        return view('glosarium.index', compact('glossaryData'));
    }



    // Helper untuk membaca file JSON
    private function getJsonData()
    {
        $jsonPath = storage_path('app/json/hasil_scraping.json');
        if (!file_exists($jsonPath)) {
            abort(404, "File data tidak ditemukan.");
        }
        return json_decode(file_get_contents($jsonPath), true)['data'];
    }

    // Menampilkan halaman A-Z dan daftar penyakit untuk versi Gemini
    public function gemini_index(Request $request)
    {
        $keyword = trim($request->query('q', ''));
        $activeLetter = strtoupper($request->query('letter', 'ALL'));
        
        if ($activeLetter !== 'ALL' && !preg_match('/^[A-Z]$/', $activeLetter)) {
            $activeLetter = 'ALL';
        }

        $rawData = $this->getJsonData();
        
        $allItems = [];
        foreach ($rawData as $disease) {
            foreach ($disease as $slug => $details) {
                $allItems[] = [
                    'slug' => $slug,
                    'istilah' => ucwords(str_replace('-', ' ', $slug)),
                    'deskripsi' => '', // Gemini version might not have a simple description field in the list, but we can map it if needed
                    'details' => $details
                ];
            }
        }
        $allItems = collect($allItems);

        $availableLetters = $allItems
            ->map(fn($item) => strtoupper(substr($item['istilah'], 0, 1)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if ($keyword !== '') {
            $glossary = $allItems
                ->filter(function($item) use ($keyword) {
                    return str_contains(strtolower($item['istilah']), strtolower($keyword));
                })
                ->groupBy(function ($item) {
                    return ucfirst(substr($item['istilah'], 0, 2));
                })
                ->sortKeys()
                ->toArray();

            return view('glosarium.gemini.index', [
                'mode' => 'glosarium',
                'glossary' => $glossary,
                'activeLetter' => 'ALL',
                'availableLetters' => $availableLetters,
                'keyword' => $keyword
            ]);
        }

        $glossary = $allItems
            ->filter(function($item) use ($activeLetter) {
                if ($activeLetter === 'ALL') return true;
                return strtoupper(substr($item['istilah'], 0, 1)) === $activeLetter;
            })
            ->groupBy(function ($item) {
                return ucfirst(substr($item['istilah'], 0, 2));
            })
            ->sortKeys()
            ->toArray();

        return view('glosarium.gemini.index', [
            'mode' => $activeLetter === 'ALL' ? 'explore' : 'glosarium',
            'glossary' => $glossary,
            'activeLetter' => $activeLetter,
            'availableLetters' => $availableLetters,
            'keyword' => ''
        ]);
    }

    // Menampilkan halaman spesifik 1 penyakit untuk versi Gemini
    public function gemini_show(string $slug)
    {
        $rawData = $this->getJsonData();
        $diseaseData = null;
        $title = ucwords(str_replace('-', ' ', $slug));

        foreach ($rawData as $disease) {
            if (isset($disease[$slug])) {
                $diseaseData = $disease[$slug];
                break;
            }
        }

        if (!$diseaseData) {
            abort(404, "Penyakit tidak ditemukan.");
        }

        // Build $item array similar to what glosarium/show.blade.php expects
        $item = [
            'slug' => $slug,
            'istilah' => $title,
            // You can extract a proper description from sections if needed
            'deskripsi' => '', 
            'details' => $diseaseData
        ];

        return view('glosarium.gemini.show', compact('item'));
    }
}
