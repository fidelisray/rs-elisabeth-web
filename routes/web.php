<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DokterController;
// use App\Http\Controllers\HospitalController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\GlossaryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\HomeController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [HomeController::class, 'index'])->name('home.index');

// Route::get('/dokter', function() {
//     return view('dokter');
// });

// Halaman Dokter
Route::prefix('dokter')->name('dokter.')->group(function () {
    // Route::get('/',       [DokterController::class, 'index'])->name('index');
    Route::get('/', [DokterController::class, 'spesialisasi'])->name('index');
    Route::get('/init', [DokterController::class, 'dokterInit'])->name('dokterInit');
    Route::get('/{specialtyCode}',   [DokterController::class, 'dokterBySpesialisasi'])->name('dokterBySpesialisasi')
    ->where('specialtyCode', '[A-Z]{3}-[0-9]{2}');
    Route::get('/jadwal', function() {
        return view('dokter.jadwal-dokter');
    });
    Route::get('/all-dokter', [DokterController::class, 'allDokter'])->name('allDokter');
    // Route::get('/{id}',   [DokterController::class, 'dokterByUnitId'])->name('dokterByUnitId')
    // ->where('id', '[A-Z]{2}-[0-9]{2}');
    // Route::get('/{id}',   [DokterController::class, 'detail'])->name('detail')
    //      ->where('id', '[0-9]+');
});

// Halmaan Promotions
Route::prefix('promotions')->name('promotions.')->group(function () {
    Route::get('/', [PromotionsController::class, 'index'])->name('index');
});

// Halaman Fasilitas dan Layanan
Route::get('/fasilitas', [\App\Http\Controllers\FacilityController::class, 'index'])->name('facilities.index');

// Halaman Ruang Perawatan
Route::get('/ruang-perawatan', [\App\Http\Controllers\RoomController::class, 'index'])->name('ruang-perawatan.index');
Route::get('/ruang-perawatan/{slug}', [\App\Http\Controllers\RoomController::class, 'show'])->name('ruang-perawatan.show');

// Halaman Tentang Kami
Route::get('/tentang-kami', function () {
    return view('tentang-kami.index');
})->name('tentang-kami.index');

// Halaman Informasi Pelanggan
Route::get('/informasi-pelanggan', function () {
    return view('customer-information.index');
})->name('customer-information.index');

// Glosarium
// Route::get('/kamus', [HospitalController::class, 'getKamusMedis'])->name('getKamusMedis');
// Route::prefix('kamus-medis')->name('glossary.')->group(function () {
Route::prefix('glosarium')->name('glossary.')->group(function () {
    Route::get('/',       [GlossaryController::class, 'index'])  ->name('index');
    Route::get('/cari',   [GlossaryController::class, 'search']) ->name('search');
    Route::get('/{term}', [GlossaryController::class, 'show'])   ->name('show');
});

Route::prefix('glosarium-gemini')->name('gemini.')->group(function () {
    Route::get('/tampil-data', [GlossaryController::class, 'tampil_data'])->name('tampil_data');
    Route::get('/', [GlossaryController::class, 'gemini_index'])->name('index');
    Route::get('/{slug}', [GlossaryController::class, 'gemini_show'])->name('show');
});

// Artikel Kesehatan
Route::prefix('articles')->name('articles.')->group(function () {
    Route::get('/', [App\Http\Controllers\ArticleController::class, 'index'])->name('index');
    Route::get('/{slug}', [App\Http\Controllers\ArticleController::class, 'show'])->name('show');
});

// Berita (News)
Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{slug}', [NewsController::class, 'show'])->name('show');
});

// Route Language Switcher
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        \Illuminate\Support\Facades\Session::put('locale', $locale);
    }

    // Hindari redirect()->back() yang rapuh ketika header Referer tidak ada.
    // Fallback ke halaman utama bila referer kosong atau sama dengan URL switcher
    // (mencegah redirect loop).
    $previous = url()->previous();
    $current  = url()->current();

    return redirect($previous !== $current ? $previous : url('/'));
})->name('lang.switch');