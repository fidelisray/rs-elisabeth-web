<?php

namespace App\Console\Commands;

use App\Services\HospitalApiService;
use Illuminate\Console\Command;

class RefreshGlossaryCache extends Command
{
    protected $signature   = 'glossary:refresh';
    protected $description = 'Validasi file JSON kamus medis lokal (per locale)';

    public function handle(HospitalApiService $apiService): int
    {
        $this->info($this->description);

        $summary = $apiService->refreshGlossaryCache();

        foreach ($summary as $locale => $count) {
            $this->line(sprintf(
                '  <fg=green;options=bold>%s</>: %d istilah',
                strtoupper($locale),
                $count
            ));
        }

        $total = array_sum($summary);

        if ($total === 0) {
            $this->error('Kamus Medis Elisabeth saat ini sedang kami siapkan... Mohon tunggu informasi lebih lanjut dari kami..');
            return self::FAILURE;
        }

        $this->info("Selesai. Total {$total} istilah medis berhasil ditampilkan.");

        return self::SUCCESS;
    }
}