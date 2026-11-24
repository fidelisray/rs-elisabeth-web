<?php

namespace App\Console\Commands;

use App\Services\GlossaryPopularityService;
use App\Services\HospitalApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GlossaryStatsCommand extends Command
{
    protected $signature = 'glossary:stats
                            {--simulate : Isi data simulasi trafik multi-hari (khusus local/testing)}
                            {--reset : Kosongkan seluruh data statistik harian dan reset cache ranking (khusus local/testing)}';

    protected $description = 'Tampilkan laporan skor Popular Terms Kamus Medis, velocity tren, dan simulasi trafik';

    public function handle(
        GlossaryPopularityService $popularityService,
        HospitalApiService $apiService
    ): int {
        $doSimulate = (bool) $this->option('simulate');
        $doReset = (bool) $this->option('reset');

        if (($doSimulate || $doReset) && !app()->environment(['local', 'testing'])) {
            $this->error('Opsi --simulate dan --reset hanya diizinkan pada environment local atau testing.');
            return self::FAILURE;
        }

        if ($doReset) {
            DB::table('glossary_term_daily_stats')->delete();
            $popularityService->clearRankingCache();
            $this->info('Data statistik kamus medis dan cache ranking berhasil di-reset ke mode curated.');
        }

        if ($doSimulate) {
            $this->seedSimulatedTraffic($popularityService);
            $this->info('Simulasi trafik kamus medis multi-hari berhasil diterapkan.');
        }

        $allItems = $apiService->getLocalGlossary();
        $report = $popularityService->getDetailedStatsReport($allItems);

        $this->newLine();
        $this->line(sprintf(
            'Mode Aktif: <fg=cyan;options=bold>%s</> | Istilah Terkualifikasi Organik: <fg=yellow;options=bold>%d</> | Diperbarui: %s',
            strtoupper((string) $report['mode']),
            (int) $report['qualified_count'],
            (string) $report['updated_at']
        ));

        $tableRows = [];
        foreach ($report['rows'] as $row) {
            $flags = [];
            if (!empty($row['is_pinned'])) {
                $flags[] = 'PINNED';
            }
            if (!empty($row['is_organic'])) {
                $flags[] = 'LIVE';
            } else {
                $flags[] = 'CURATED';
            }
            if (!empty($row['is_trending'])) {
                $flags[] = 'TRENDING';
            }

            $tableRows[] = [
                '#' . $row['rank'],
                $row['slug'],
                $row['istilah'],
                implode(', ', $flags),
                number_format((float) $row['score_30d'], 2),
                number_format((float) $row['score_7d'], 2),
                number_format((float) $row['velocity'], 2) . 'x',
                (int) $row['organic_hits'],
                sprintf(
                    '%d / %d / %d / %d / %d',
                    (int) $row['search_clicks'],
                    (int) $row['directory_views'],
                    (int) $row['direct_views'],
                    (int) $row['internal_nav_views'],
                    (int) $row['popular_clicks']
                ),
            ];
        }

        $this->table(
            ['Rank', 'Slug', 'Istilah', 'Status', 'Score 30d', 'Score 7d', 'Velocity', 'Organic Hits', 'S/Dir/D/Rel/Pop'],
            $tableRows
        );

        return self::SUCCESS;
    }

    protected function seedSimulatedTraffic(GlossaryPopularityService $popularityService): void
    {
        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();
        $nowTimestamp = Carbon::now($tz)->toDateTimeString();

        DB::table('glossary_term_daily_stats')->delete();

        $simulationPlan = [
            // Lonjakan kuat minggu ini -> Trending #1
            ['slug' => 'dengue-fever', 'days_ago' => 0, 'search' => 5, 'dir' => 2, 'direct' => 2, 'rel' => 1, 'pop' => 3],
            ['slug' => 'dengue-fever', 'days_ago' => 1, 'search' => 4, 'dir' => 1, 'direct' => 1, 'rel' => 1, 'pop' => 2],
            ['slug' => 'dengue-fever', 'days_ago' => 9, 'search' => 0, 'dir' => 1, 'direct' => 0, 'rel' => 0, 'pop' => 0],

            // Lonjakan kuat minggu ini -> Trending #2
            ['slug' => 'appendicitis', 'days_ago' => 0, 'search' => 4, 'dir' => 1, 'direct' => 1, 'rel' => 2, 'pop' => 1],
            ['slug' => 'appendicitis', 'days_ago' => 2, 'search' => 3, 'dir' => 1, 'direct' => 0, 'rel' => 1, 'pop' => 1],

            // Lonjakan minggu ini -> Trending #3
            ['slug' => 'gastritis', 'days_ago' => 1, 'search' => 3, 'dir' => 2, 'direct' => 1, 'rel' => 0, 'pop' => 2],
            ['slug' => 'gastritis', 'days_ago' => 3, 'search' => 2, 'dir' => 1, 'direct' => 1, 'rel' => 1, 'pop' => 1],

            // Trafik tinggi tapi stabil dari minggu lalu (populer, namun tidak trending karena velocity < 1.5)
            ['slug' => 'diabetes', 'days_ago' => 0, 'search' => 3, 'dir' => 2, 'direct' => 1, 'rel' => 1, 'pop' => 4],
            ['slug' => 'diabetes', 'days_ago' => 8, 'search' => 4, 'dir' => 2, 'direct' => 2, 'rel' => 1, 'pop' => 2],
            ['slug' => 'diabetes', 'days_ago' => 12, 'search' => 3, 'dir' => 1, 'direct' => 1, 'rel' => 0, 'pop' => 1],

            // Istilah organik terkualifikasi lainnya
            ['slug' => 'stroke', 'days_ago' => 2, 'search' => 2, 'dir' => 1, 'direct' => 1, 'rel' => 0, 'pop' => 1],
            ['slug' => 'stroke', 'days_ago' => 10, 'search' => 2, 'dir' => 1, 'direct' => 0, 'rel' => 1, 'pop' => 0],
            ['slug' => 'asthma', 'days_ago' => 4, 'search' => 2, 'dir' => 1, 'direct' => 0, 'rel' => 1, 'pop' => 1],
        ];

        foreach ($simulationPlan as $row) {
            $statDate = $today->copy()->subDays($row['days_ago'])->toDateString();

            DB::table('glossary_term_daily_stats')->insert([
                'slug' => $row['slug'],
                'stat_date' => $statDate,
                'search_clicks' => $row['search'],
                'directory_views' => $row['dir'],
                'direct_views' => $row['direct'],
                'internal_nav_views' => $row['rel'],
                'popular_clicks' => $row['pop'],
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ]);
        }

        $popularityService->clearRankingCache();
    }
}
