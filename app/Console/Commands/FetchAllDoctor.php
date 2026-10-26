<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DoctorApiService;

class FetchAllDoctor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dokter:fetch-all {--force : Paksa refresh semua cache dokter langsung dari API MedInfras}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch semua data dokter dari seluruh speciality dan simpan ke cache';

    /**
     * Execute the console command.
     */
    public function handle(DoctorApiService $apiService)
    {
        if (! config('rsapi.enabled', true)) {
            $this->warn('RS API dinonaktifkan; tidak ada penarikan data.');
            return Command::FAILURE;
        }

        $force = (bool) $this->option('force');

        $this->info('Memulai warm-up cache master data (Spesialisasi & Unit)...');
        $apiService->getDaftarSpesialisasi();
        $apiService->getDaftarUnits();

        $this->info('Memulai fetch data dokter...');
        if ($force) {
            $this->info('Mode force-refresh aktif: mengabaikan cache intermediate dan menarik data segar dari MedInfras...');
        }

        $total = $apiService->fetchAndCacheAllDokter($force);
        if ($total === 0) {
            $this->error('Warm-up tidak memperoleh hasil lengkap. Cache sebelumnya dipertahankan.');
            return Command::FAILURE;
        }

        $this->info("Selesai. Total {$total} entri jadwal dokter berhasil di-cache.");

        return Command::SUCCESS;
    }
}
