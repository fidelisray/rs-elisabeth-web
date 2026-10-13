<?php

use App\Http\Controllers\Api\CmsWebhookController;
use App\Http\Middleware\VerifyCmsWebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Internal Webhook Routes (CMS -> Web Frontend)
|--------------------------------------------------------------------------
| Rute komunikasi internal antar-modul RS Elisabeth.
| Dilindungi oleh middleware VerifyCmsWebhookSignature (HMAC SHA-256).
*/
Route::prefix('internal')->group(function () {
    Route::post('/webhook/cache-invalidate', [CmsWebhookController::class, 'invalidate'])
        ->middleware(VerifyCmsWebhookSignature::class)
        ->name('internal.webhook.cache-invalidate');
});
