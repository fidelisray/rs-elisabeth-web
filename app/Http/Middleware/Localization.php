<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale');

        // Validasi nilai locale agar hanya menerima bahasa yang memang
        // didukung (mencegah __() gagal menemukan file & getLocale() menampilkan
        // kode yang tidak valid bila session berisi nilai di luar daftar).
        if (is_string($locale) && in_array($locale, ['en', 'id'], true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
