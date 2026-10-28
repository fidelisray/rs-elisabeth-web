<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\OnlineLibraryDto;
use App\Services\CmsApiService;

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
}
