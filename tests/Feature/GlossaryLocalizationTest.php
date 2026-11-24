<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GlossaryLocalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Strictly isolate from the local PostgreSQL testing database.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'glossary.pinned_slugs' => [],
            'glossary.allowed_referer_hosts' => [],
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $migration = include database_path('migrations/2026_11_23_000000_create_glossary_term_daily_stats_table.php');
        $migration->up();

        Cache::flush();
    }

    public function test_lang_json_files_are_valid_have_zero_duplicates_full_parity_and_cover_all_glossary_keys(): void
    {
        $enPath = base_path('lang/en.json');
        $idPath = base_path('lang/id.json');

        $enRaw = file_get_contents($enPath);
        $idRaw = file_get_contents($idPath);

        $this->assertNotFalse($enRaw);
        $this->assertNotFalse($idRaw);

        $enData = json_decode($enRaw, true, 512, JSON_THROW_ON_ERROR);
        $idData = json_decode($idRaw, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($enData);
        $this->assertIsArray($idData);

        $enKeys = $this->extractTopLevelJsonKeys($enRaw);
        $idKeys = $this->extractTopLevelJsonKeys($idRaw);

        $enDuplicates = array_keys(array_filter(array_count_values($enKeys), fn (int $count) => $count > 1));
        $idDuplicates = array_keys(array_filter(array_count_values($idKeys), fn (int $count) => $count > 1));

        $this->assertSame([], $enDuplicates, 'Duplicate keys found in lang/en.json: ' . implode(', ', $enDuplicates));
        $this->assertSame([], $idDuplicates, 'Duplicate keys found in lang/id.json: ' . implode(', ', $idDuplicates));

        $onlyInEn = array_values(array_diff(array_keys($enData), array_keys($idData)));
        $onlyInId = array_values(array_diff(array_keys($idData), array_keys($enData)));

        $this->assertSame([], $onlyInEn, 'Keys missing from lang/id.json: ' . implode(', ', $onlyInEn));
        $this->assertSame([], $onlyInId, 'Keys missing from lang/en.json: ' . implode(', ', $onlyInId));

        $this->assertSame('Insurance', $enData['Asuransi']);
        $this->assertSame('Optional', $enData['Opsional']);
        $this->assertSame('100 Years of St. Elisabeth Hospital', $enData['100 Tahun RS St. Elisabeth']);
        $this->assertSame('Medical term not found.', $enData['Istilah medis tidak ditemukan.']);

        $views = [
            resource_path('views/glosarium/index.blade.php'),
            resource_path('views/glosarium/show.blade.php'),
        ];

        $extractedKeys = [];
        foreach ($views as $viewPath) {
            $blade = file_get_contents($viewPath);
            $this->assertNotFalse($blade);

            // Strip Blade comments before extracting active translation keys.
            $bladeWithoutComments = preg_replace('/\{\{--.*?--\}\}/s', '', $blade) ?? $blade;

            preg_match_all(
                '/(?:__|trans_choice)\(\s*([\'"])((?:\\\\.|(?!\1).)*)\1/',
                $bladeWithoutComments,
                $matches
            );

            foreach ($matches[2] as $rawKey) {
                $extractedKeys[] = stripslashes($rawKey);
            }
        }

        $uniqueBladeKeys = array_values(array_unique($extractedKeys));
        $this->assertNotEmpty($uniqueBladeKeys);

        $missingInEn = array_values(array_filter($uniqueBladeKeys, fn (string $k) => !array_key_exists($k, $enData)));
        $missingInId = array_values(array_filter($uniqueBladeKeys, fn (string $k) => !array_key_exists($k, $idData)));

        $this->assertSame([], $missingInEn, 'Active glossary Blade keys missing in lang/en.json: ' . implode(', ', $missingInEn));
        $this->assertSame([], $missingInId, 'Active glossary Blade keys missing in lang/id.json: ' . implode(', ', $missingInId));

        $allowedIdenticalEnKeys = ['Reset'];
        foreach ($uniqueBladeKeys as $key) {
            $this->assertNotSame('', trim((string) $enData[$key]), "Empty English translation for key: {$key}");
            $this->assertNotSame('', trim((string) $idData[$key]), "Empty Indonesian translation for key: {$key}");
            if (!in_array($key, $allowedIdenticalEnKeys, true)) {
                $this->assertNotSame(
                    $key,
                    $enData[$key],
                    "Glossary key appears untranslated in lang/en.json: {$key}"
                );
            }
        }

        // Guard pipe-delimited trans_choice() keys from being invoked via __() anywhere in app/ or resources/.
        $pipeKeys = ['istilah medis dalam', 'kelompok sub-awalan', 'Istilah', 'Grup'];
        $pipePattern = '/\b__\(\s*([\'"])(' . implode('|', array_map(fn (string $k) => preg_quote($k, '/'), $pipeKeys)) . ')\1/';
        foreach ([app_path(), resource_path()] as $scanDir) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($scanDir));
            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'js'], true)) {
                    continue;
                }
                $contents = file_get_contents($file->getPathname());
                if ($contents === false) {
                    continue;
                }
                $contents = preg_replace('/\{\{--.*?--\}\}/s', '', $contents) ?? $contents;
                $this->assertDoesNotMatchRegularExpression(
                    $pipePattern,
                    $contents,
                    'Pipe-delimited trans_choice() key must not be called via __() in ' . $file->getPathname()
                );
            }
        }
    }

    public function test_trans_choice_handles_zero_singular_and_plural_in_en_and_id(): void
    {
        app()->setLocale('en');
        $this->assertSame('medical terms in', trans_choice('istilah medis dalam', 0));
        $this->assertSame('medical term in', trans_choice('istilah medis dalam', 1));
        $this->assertSame('medical terms in', trans_choice('istilah medis dalam', 2));
        $this->assertSame('sub-prefix groups', trans_choice('kelompok sub-awalan', 0));
        $this->assertSame('sub-prefix group', trans_choice('kelompok sub-awalan', 1));
        $this->assertSame('sub-prefix groups', trans_choice('kelompok sub-awalan', 2));
        $this->assertSame('Terms', trans_choice('Istilah', 0));
        $this->assertSame('Term', trans_choice('Istilah', 1));
        $this->assertSame('Terms', trans_choice('Istilah', 2));
        $this->assertSame('Groups', trans_choice('Grup', 0));
        $this->assertSame('Group', trans_choice('Grup', 1));
        $this->assertSame('Groups', trans_choice('Grup', 2));

        app()->setLocale('id');
        foreach ([0, 1, 2] as $count) {
            $this->assertSame('istilah medis dalam', trans_choice('istilah medis dalam', $count));
            $this->assertSame('kelompok sub-awalan', trans_choice('kelompok sub-awalan', $count));
            $this->assertSame('Istilah', trans_choice('Istilah', $count));
            $this->assertSame('Grup', trans_choice('Grup', $count));
        }
    }

    public function test_explore_landing_page_renders_localized_stats_and_navbar_alt_in_en_and_id(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('1,128+');
        $enResponse->assertDontSee('1.128+');
        $enResponse->assertSee('100 Years of St. Elisabeth Hospital');
        $enResponse->assertSee('Explore the Elisabeth Medical Glossary');
        $enResponse->assertSee('Frequently Searched Terms');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('1.128+');
        $idResponse->assertDontSee('1,128+');
        $idResponse->assertSee('100 Tahun RS St. Elisabeth');
        $idResponse->assertSee('Jelajahi Kamus Medis Elisabeth');
        $idResponse->assertSee('Istilah yang Sering Dicari');
    }

    public function test_directory_letter_a_renders_full_en_and_id_localization_with_plural_labels(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium?letter=A');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Elisabeth Medical Glossary Directory');
        $enResponse->assertSee('Medical Terms Starting with');
        $enResponse->assertSee('Showing');
        $enResponse->assertSee('data-singular="medical term in"', false);
        $enResponse->assertSee('data-plural="medical terms in"', false);
        $enResponse->assertSee('>medical terms in</span>', false);
        $enResponse->assertSee('data-singular="sub-prefix group"', false);
        $enResponse->assertSee('data-plural="sub-prefix groups"', false);
        $enResponse->assertSee('>sub-prefix groups</span>', false);
        $enResponse->assertSee('Quick filter in this list...');
        $enResponse->assertSee('aria-label="Quick filter in this list"', false);
        $enResponse->assertSee('aria-label="Clear filter"', false);
        $enResponse->assertSee('Glossary Home');
        $enResponse->assertSee('aria-label="Quick sub-prefix navigation"', false);
        $enResponse->assertSee('Jump:');
        $enResponse->assertSee('Sub-Prefix');
        $enResponse->assertSee('Medical terms starting with');
        $enResponse->assertSee('data-singular="Term"', false);
        $enResponse->assertSee('data-plural="Terms"', false);
        $enResponse->assertSee('>Term</span>', false);
        $enResponse->assertSee('>Terms</span>', false);
        $enResponse->assertSee('No Terms Found in This List');
        $enResponse->assertSee('No terms on this page match the filter keyword');
        $enResponse->assertSee('Show All Terms');
        $enResponse->assertSee('Sub-Prefix Navigation');
        $enResponse->assertSee('data-singular="Group"', false);
        $enResponse->assertSee('data-plural="Groups"', false);
        $enResponse->assertSee('>Groups</span>', false);
        $enResponse->assertSee('Click a sub-prefix code below to jump directly to the term group you are looking for.');
        $enResponse->assertSee('aria-label="Desktop Sub-Prefix Navigation"', false);
        $enResponse->assertSee('Consult your health concerns directly with our specialist doctors.');
        $enResponse->assertSee('Read the latest trusted health guides and information.');

        // Ensure Indonesian directory labels do not leak into EN rendering.
        $enResponse->assertDontSee('Direktori Kamus Medis Elisabeth');
        $enResponse->assertDontSee('Istilah Medis Awalan');
        $enResponse->assertDontSee('Menu Utama Kamus');
        $enResponse->assertDontSee('Navigasi Sub-Awalan');
        $enResponse->assertDontSee('Kumpulan istilah medis berawalan');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium?letter=A');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Direktori Kamus Medis Elisabeth');
        $idResponse->assertSee('Istilah Medis Awalan');
        $idResponse->assertSee('Menampilkan');
        $idResponse->assertSee('>istilah medis dalam</span>', false);
        $idResponse->assertSee('>kelompok sub-awalan</span>', false);
        $idResponse->assertSee('Filter cepat di daftar ini...');
        $idResponse->assertSee('Menu Utama Kamus');
        $idResponse->assertSee('Lompat:');
        $idResponse->assertSee('Sub-Awalan');
        $idResponse->assertSee('Kumpulan istilah medis berawalan');
        $idResponse->assertSee('>Istilah</span>', false);
        $idResponse->assertSee('Navigasi Sub-Awalan');
        $idResponse->assertSee('>Grup</span>', false);
    }

    public function test_directory_letter_q_renders_localized_empty_letter_state(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium?letter=Q');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('No Medical Terms Available');
        $enResponse->assertSee('No medical terms are currently listed for the letter');
        $enResponse->assertSee('Back to Main Glossary');
        $enResponse->assertDontSee('Belum Ada Data Istilah Medis');
        $enResponse->assertDontSee('Belum ada istilah medis yang terdaftar untuk awalan huruf');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium?letter=Q');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Belum Ada Data Istilah Medis');
        $idResponse->assertSee('Belum ada istilah medis yang terdaftar untuk awalan huruf');
        $idResponse->assertSee('Kembali ke Kamus Utama');
    }

    public function test_directory_letter_x_renders_singular_english_labels(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium?letter=X');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('<strong id="glossaryVisibleCount">1</strong>', false);
        $enResponse->assertSee('>medical term in</span>', false);
        $enResponse->assertSee('<strong id="glossaryVisibleGroups">1</strong>', false);
        $enResponse->assertSee('>sub-prefix group</span>', false);
        $enResponse->assertSee('<strong class="group-visible-count">1</strong>', false);
        $enResponse->assertSee('>Term</span>', false);
        $enResponse->assertSee('<span id="glossarySideGroupsCount">1</span>', false);
        $enResponse->assertSee('>Group</span>', false);

        // Plural text must not be rendered as the visible label content for count = 1.
        $enResponse->assertDontSee('>medical terms in</span>', false);
        $enResponse->assertDontSee('>sub-prefix groups</span>', false);
        $enResponse->assertDontSee('>Terms</span>', false);
        $enResponse->assertDontSee('>Groups</span>', false);

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium?letter=X');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('>istilah medis dalam</span>', false);
        $idResponse->assertSee('>kelompok sub-awalan</span>', false);
        $idResponse->assertSee('>Istilah</span>', false);
        $idResponse->assertSee('>Grup</span>', false);
    }

    public function test_search_with_results_renders_localized_relevant_matches_and_directory(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium?q=diabetes');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Elisabeth Medical Glossary Directory');
        $enResponse->assertSee('Search Results:');
        $enResponse->assertSee('Most Relevant Matches');
        $enResponse->assertSee('Closest matching medical terms for');
        $enResponse->assertSee('Sub-Prefix');
        $enResponse->assertSee('Glossary Home');
        $enResponse->assertDontSee('Hasil Paling Relevan');
        $enResponse->assertDontSee('Direktori Kamus Medis Elisabeth');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium?q=diabetes');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Direktori Kamus Medis Elisabeth');
        $idResponse->assertSee('Hasil Pencarian:');
        $idResponse->assertSee('Hasil Paling Relevan');
        $idResponse->assertSee('Istilah dengan kecocokan terdekat untuk pencarian');
    }

    public function test_search_without_results_renders_localized_empty_state_and_popular_suggestions(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium?q=zzzznotfound');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('No Medical Terms Available');
        $enResponse->assertSee('Sorry, terms for the search');
        $enResponse->assertSee('are currently not available in our medical glossary.');
        $enResponse->assertSee('Terms Frequently Searched by Patients:');
        $enResponse->assertSee('Back to Main Glossary');
        $enResponse->assertDontSee('Mohon maaf, istilah untuk pencarian');
        $enResponse->assertDontSee('Kembali ke Kamus Utama');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium?q=zzzznotfound');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Belum Ada Data Istilah Medis');
        $idResponse->assertSee('Mohon maaf, istilah untuk pencarian');
        $idResponse->assertSee('saat ini belum tersedia di dalam kamus medis kami.');
        $idResponse->assertSee('Istilah yang Sering Dicari Pasien:');
        $idResponse->assertSee('Kembali ke Kamus Utama');
    }

    public function test_show_first_term_renders_localized_start_of_directory_boundary(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium/abdominal-aortic-aneurysm');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Start of Directory');
        $enResponse->assertSee('This is the first term');
        $enResponse->assertSee('Next Term');
        $enResponse->assertDontSee('Awal Direktori');
        $enResponse->assertDontSee('Ini adalah istilah pertama');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium/abdominal-aortic-aneurysm');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Awal Direktori');
        $idResponse->assertSee('Ini adalah istilah pertama');
        $idResponse->assertSee('Istilah Selanjutnya');
    }

    public function test_show_last_term_renders_localized_end_of_directory_boundary(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium/zollinger-ellison-syndrome');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('End of Directory');
        $enResponse->assertSee('This is the last term');
        $enResponse->assertSee('Previous Term');
        $enResponse->assertDontSee('Akhir Direktori');
        $enResponse->assertDontSee('Ini adalah istilah terakhir');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium/zollinger-ellison-syndrome');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Akhir Direktori');
        $idResponse->assertSee('Ini adalah istilah terakhir');
        $idResponse->assertSee('Istilah Sebelumnya');
    }

    public function test_show_middle_term_diabetes_renders_complete_en_and_id_localization(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->get('/glosarium/diabetes');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Medical Encyclopedia');
        $enResponse->assertSee('Sub-Prefix');
        $enResponse->assertSee('Browse Letter D');
        $enResponse->assertSee('title="Medical Glossary Home"', false);
        $enResponse->assertSee('All Letters');
        $enResponse->assertSee('Summary of clinical definitions and health education from St. Elisabeth Hospital Semarang');
        $enResponse->assertSee('Medical Definition Summary');
        $enResponse->assertSee('Medical Reference Source');
        $enResponse->assertSee('This medical term explanation is provided for public health education and is not a substitute for professional medical diagnosis, consultation, or treatment.');
        $enResponse->assertSee('aria-label="Sequential term navigation"', false);
        $enResponse->assertSee('Previous Term');
        $enResponse->assertSee('Next Term');
        $enResponse->assertSee('Related Medical Terms Starting with');
        $enResponse->assertSee('Explore other related health terms in the same alphabetical group');
        $enResponse->assertSee('View All');
        $enResponse->assertSee('Need a Doctor Consultation?');
        $enResponse->assertSee('Discuss your symptoms or medical concerns directly with our specialist doctors.');
        $enResponse->assertSee('Related Health Articles');
        $enResponse->assertSee('Discover prevention tips and healthy lifestyle guides from our medical team.');

        // Ensure Indonesian show-page strings do not leak into EN rendering.
        $enResponse->assertDontSee('Ensiklopedia Medis');
        $enResponse->assertDontSee('Daftar Huruf');
        $enResponse->assertDontSee('Intisari Definisi Medis');
        $enResponse->assertDontSee('Sumber Referensi Medis');
        $enResponse->assertDontSee('Istilah Sebelumnya');
        $enResponse->assertDontSee('Istilah Selanjutnya');
        $enResponse->assertDontSee('Istilah Medis Terkait Awalan');
        $enResponse->assertDontSee('Butuh Konsultasi Dokter?');
        $enResponse->assertDontSee('Artikel Kesehatan Terkait');

        $idResponse = $this->withSession(['locale' => 'id'])->get('/glosarium/diabetes');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('Ensiklopedia Medis');
        $idResponse->assertSee('Sub-Awalan');
        $idResponse->assertSee('Daftar Huruf D');
        $idResponse->assertSee('Menu Utama Kamus Medis');
        $idResponse->assertSee('Semua Abjad');
        $idResponse->assertSee('Ringkasan definisi klinis dan penjelasan edukasi kesehatan RS St. Elisabeth Semarang');
        $idResponse->assertSee('Intisari Definisi Medis');
        $idResponse->assertSee('Sumber Referensi Medis');
        $idResponse->assertSee('Istilah Sebelumnya');
        $idResponse->assertSee('Istilah Selanjutnya');
        $idResponse->assertSee('Istilah Medis Terkait Awalan');
        $idResponse->assertSee('Lihat Semua');
        $idResponse->assertSee('Butuh Konsultasi Dokter?');
        $idResponse->assertSee('Artikel Kesehatan Terkait');
    }

    public function test_missing_term_returns_localized_404_json_message(): void
    {
        $enResponse = $this->withSession(['locale' => 'en'])->getJson('/glosarium/istilah-tidak-ada');
        $enResponse->assertStatus(404);
        $enResponse->assertJson(['message' => 'Medical term not found.']);

        $idResponse = $this->withSession(['locale' => 'id'])->getJson('/glosarium/istilah-tidak-ada');
        $idResponse->assertStatus(404);
        $idResponse->assertJson(['message' => 'Istilah medis tidak ditemukan.']);
    }

    public function test_language_switcher_preserves_glossary_query_and_path_and_updates_locale(): void
    {
        $switchToEn = $this->from(url('/glosarium?letter=A'))->get('/lang/en');
        $switchToEn->assertRedirect(url('/glosarium?letter=A'));

        $enDirectory = $this->get('/glosarium?letter=A');
        $enDirectory->assertStatus(200);
        $enDirectory->assertSee('Elisabeth Medical Glossary Directory');
        $enDirectory->assertDontSee('Direktori Kamus Medis Elisabeth');

        $switchSearchToId = $this->from(url('/glosarium?q=diabetes'))->get('/lang/id');
        $switchSearchToId->assertRedirect(url('/glosarium?q=diabetes'));

        $idSearch = $this->get('/glosarium?q=diabetes');
        $idSearch->assertStatus(200);
        $idSearch->assertSee('Hasil Paling Relevan');
        $idSearch->assertDontSee('Most Relevant Matches');

        $switchDetailToEn = $this->from(url('/glosarium/diabetes'))->get('/lang/en');
        $switchDetailToEn->assertRedirect(url('/glosarium/diabetes'));

        $enDetail = $this->get('/glosarium/diabetes');
        $enDetail->assertStatus(200);
        $enDetail->assertSee('Medical Encyclopedia');
        $enDetail->assertDontSee('Ensiklopedia Medis');
    }

    /**
     * Extract all top-level keys from a JSON object string without collapsing duplicates.
     *
     * @return list<string>
     */
    private function extractTopLevelJsonKeys(string $rawJson): array
    {
        $keys = [];
        $length = strlen($rawJson);
        $depth = 0;
        $inString = false;
        $escaped = false;
        $stringStart = 0;

        for ($i = 0; $i < $length; $i++) {
            $ch = $rawJson[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($ch === '\\') {
                    $escaped = true;
                } elseif ($ch === '"') {
                    $inString = false;
                    if ($depth === 1) {
                        $j = $i + 1;
                        while ($j < $length && ctype_space($rawJson[$j])) {
                            $j++;
                        }
                        if ($j < $length && $rawJson[$j] === ':') {
                            $token = substr($rawJson, $stringStart, $i - $stringStart + 1);
                            $keys[] = json_decode($token, true, 512, JSON_THROW_ON_ERROR);
                        }
                    }
                }
                continue;
            }

            if ($ch === '"') {
                $inString = true;
                $stringStart = $i;
            } elseif ($ch === '{' || $ch === '[') {
                $depth++;
            } elseif ($ch === '}' || $ch === ']') {
                $depth--;
            }
        }

        return $keys;
    }
}
