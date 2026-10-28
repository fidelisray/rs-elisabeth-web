<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\OnlineLibraryDto;
use App\Services\CmsApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class OnlineLibraryController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawDocuments = $this->cmsApiService->getOnlineLibraries();
        $documentsList = array_map(fn ($item) => OnlineLibraryDto::fromArray($item), $rawDocuments);

        $categories = collect($documentsList)
            ->pluck('category')
            ->filter(fn ($cat) => is_string($cat) && trim($cat) !== '')
            ->unique()
            ->values()
            ->all();

        return view('online-library.index', compact('documentsList', 'categories'));
    }

    public function show(string $slug)
    {
        $activeLocale = app()->getLocale();
        $rawDocument = $this->cmsApiService->getOnlineLibraryBySlug($slug, $activeLocale);

        if (! $rawDocument) {
            $fallbackLocale = $activeLocale === 'id' ? 'en' : 'id';
            $fallbackDocument = $this->cmsApiService->getOnlineLibraryBySlug($slug, $fallbackLocale);

            if ($fallbackDocument) {
                $translatedSlug = $fallbackDocument['alternate_slugs'][$activeLocale] ?? null;

                if (is_string($translatedSlug) && $translatedSlug !== '' && $translatedSlug !== $slug) {
                    return redirect()->route('online-library.show', ['slug' => $translatedSlug]);
                }

                $rawDocument = $fallbackDocument;
            }
        }

        if (! $rawDocument) {
            abort(404, __('Dokumen tidak ditemukan.'));
        }

        $document = OnlineLibraryDto::fromArray($rawDocument);

        return view('online-library.show', compact('document'));
    }

    /**
     * Menyajikan stream berkas PDF dari origin aplikasi web sendiri (Same-Origin)
     * untuk mengatasi blokir X-Frame-Options: SAMEORIGIN dari server media eksternal
     * serta mendukung unduh langsung (Content-Disposition: attachment) dengan nama file asli.
     */
    public function file(Request $request, string $slug)
    {
        $document = $this->resolveDocumentBySlug($slug);

        if (! $document || (empty($document->file_path) && empty($document->file_url))) {
            abort(404, __('Dokumen tidak ditemukan.'));
        }

        $fileName = $this->sanitizeFilename($document->file_name ?: ($document->slug.'.pdf'));
        $dispositionType = $request->boolean('download')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $headers = [
            'Content-Type' => 'application/pdf',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=3600',
        ];

        // 1. Utamakan pembacaan stream langsung dari Storage S3 / MinIO jika file_path tersedia
        if (! empty($document->file_path) && ! app()->runningUnitTests()) {
            try {
                $stream = Storage::disk('s3')->readStream($document->file_path);

                if (is_resource($stream)) {
                    return response()->stream(function () use ($stream) {
                        while (! feof($stream)) {
                            echo fread($stream, 8192);
                            flush();
                        }
                        fclose($stream);
                    }, 200, array_merge($headers, [
                        'Content-Disposition' => $this->buildContentDisposition($dispositionType, $fileName),
                    ]));
                }
            } catch (\Throwable $e) {
                Log::warning('OnlineLibrary - Gagal membaca stream S3, beralih ke fallback HTTP URL', [
                    'slug' => $slug,
                    'file_path' => $document->file_path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Fallback: Ambil berkas PDF via HTTP Server-to-Server dari file_url
        if (! empty($document->file_url)) {
            try {
                $upstream = Http::timeout(30)->get($document->file_url);

                if ($upstream->successful()) {
                    return response($upstream->body(), 200, array_merge($headers, [
                        'Content-Disposition' => $this->buildContentDisposition($dispositionType, $fileName),
                    ]));
                }
            } catch (\Throwable $e) {
                Log::error('OnlineLibrary - Gagal mengambil berkas PDF dari file_url', [
                    'slug' => $slug,
                    'file_url' => $document->file_url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        abort(404, __('Dokumen tidak ditemukan.'));
    }

    protected function resolveDocumentBySlug(string $slug): ?OnlineLibraryDto
    {
        $activeLocale = app()->getLocale();
        $rawDocument = $this->cmsApiService->getOnlineLibraryBySlug($slug, $activeLocale);

        if (! $rawDocument) {
            $fallbackLocale = $activeLocale === 'id' ? 'en' : 'id';
            $rawDocument = $this->cmsApiService->getOnlineLibraryBySlug($slug, $fallbackLocale);
        }

        return $rawDocument ? OnlineLibraryDto::fromArray($rawDocument) : null;
    }

    protected function sanitizeFilename(string $filename): string
    {
        $clean = trim(str_replace(["\r", "\n", '"', '/', '\\'], '', $filename));
        if ($clean === '') {
            $clean = 'document.pdf';
        }
        if (! str_ends_with(strtolower($clean), '.pdf')) {
            $clean .= '.pdf';
        }

        return $clean;
    }

    protected function buildContentDisposition(string $dispositionType, string $filename): string
    {
        $asciiFallback = preg_replace('/[^\x20-\x7E]/', '_', $filename) ?: 'document.pdf';

        return sprintf(
            '%s; filename="%s"; filename*=UTF-8\'\'%s',
            $dispositionType,
            $asciiFallback,
            rawurlencode($filename)
        );
    }
}
