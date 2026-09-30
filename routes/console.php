<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Konfigurasi Cron Job / Task Scheduling
 * --------------------------------------
 * Command ini bertugas melakukan "Cache Warm-Up" (mengambil data dari API 
 * dan menyimpannya ke cache di background) sehingga user tidak pernah merasakan loading lama.
 */

// 1. Fetch Data Dokter (Jadwal, Unit, Spesialisasi) 
// Karena jadwal dokter bisa berubah sewaktu-waktu, kita set tiap jam.
Schedule::command('dokter:fetch-all')
    ->hourly()
    ->withoutOverlapping();

// 2. Fetch Kamus Medis (Glossary)
// Data kamus medis sangat jarang berubah, cukup sehari sekali.
Schedule::command('glossary:refresh')
    ->dailyAt('02:00') // Jalan jam 2 pagi saat server sepi
    ->withoutOverlapping();
