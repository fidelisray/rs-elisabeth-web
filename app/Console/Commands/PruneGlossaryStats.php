<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneGlossaryStats extends Command
{
    protected $signature = 'glossary:prune-stats {--days= : Jumlah hari retensi data statistik harian}';

    protected $description = 'Hapus data statistik harian kamus medis yang lebih tua dari batas retensi';

    public function handle(): int
    {
        $rawDays = $this->option('days');
        $days = $rawDays !== null && $rawDays !== ''
            ? max(1, (int) $rawDays)
            : max(1, (int) config('glossary.retention_days', 90));

        $tz = (string) config('app.timezone', 'Asia/Jakarta');
        $cutoffDate = Carbon::now($tz)->startOfDay()->subDays($days)->toDateString();

        try {
            $deleted = DB::table('glossary_term_daily_stats')
                ->where('stat_date', '<', $cutoffDate)
                ->delete();
        } catch (\Throwable $e) {
            $this->error('Gagal menghapus data statistik kamus medis: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Berhasil menghapus {$deleted} baris statistik kamus medis sebelum tanggal {$cutoffDate} (retensi {$days} hari).");

        return self::SUCCESS;
    }
}
