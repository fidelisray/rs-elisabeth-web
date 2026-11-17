<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\ArticleDto;
use App\Services\CmsApiService;

class ArticleController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawArticles = $this->cmsApiService->getArticles();
        $articlesList = array_map(fn ($item) => ArticleDto::fromArray($item), $rawArticles);

        return view('articles.index', compact('articlesList'));
    }

    public function show($slug)
    {
        $activeLocale = app()->getLocale();
        $rawArticle = $this->cmsApiService->getArticleBySlug($slug, $activeLocale);

        if (! $rawArticle) {
            $fallbackLocale = $activeLocale === 'id' ? 'en' : 'id';
            $fallbackArticle = $this->cmsApiService->getArticleBySlug($slug, $fallbackLocale);

            if ($fallbackArticle) {
                $translatedSlug = $fallbackArticle['alternate_slugs'][$activeLocale] ?? null;

                if (is_string($translatedSlug) && $translatedSlug !== '' && $translatedSlug !== $slug) {
                    return redirect()->route('articles.show', ['slug' => $translatedSlug]);
                }

                $rawArticle = $fallbackArticle;
            }
        }

        if (! $rawArticle) {
            abort(404, __('Artikel tidak ditemukan.'));
        }

        $article = ArticleDto::fromArray($rawArticle);

        return view('articles.show', compact('article'));
    }
}
