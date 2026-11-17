<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\NewsDto;
use App\Services\CmsApiService;

class NewsController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawNews = $this->cmsApiService->getNews();
        $newsList = array_map(fn ($item) => NewsDto::fromArray($item), $rawNews);

        return view('news.index', compact('newsList'));
    }

    public function show($slug)
    {
        $activeLocale = app()->getLocale();
        $rawNews = $this->cmsApiService->getNewsBySlug($slug, $activeLocale);

        if (! $rawNews) {
            $fallbackLocale = $activeLocale === 'id' ? 'en' : 'id';
            $fallbackNews = $this->cmsApiService->getNewsBySlug($slug, $fallbackLocale);

            if ($fallbackNews) {
                $translatedSlug = $fallbackNews['alternate_slugs'][$activeLocale] ?? null;

                if (is_string($translatedSlug) && $translatedSlug !== '' && $translatedSlug !== $slug) {
                    return redirect()->route('news.show', ['slug' => $translatedSlug]);
                }

                $rawNews = $fallbackNews;
            }
        }

        if (! $rawNews) {
            abort(404, __('Berita tidak ditemukan.'));
        }

        $news = NewsDto::fromArray($rawNews);

        return view('news.show', compact('news'));
    }
}
