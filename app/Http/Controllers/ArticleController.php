<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CmsApiService;
use App\DTOs\Cms\ArticleDto;

class ArticleController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawArticles = $this->cmsApiService->getArticles();
        $articlesList = array_map(fn($item) => ArticleDto::fromArray($item), $rawArticles);

        return view('articles.index', compact('articlesList'));
    }

    public function show($slug)
    {
        // PERBAIKAN: Gunakan getArticleBySlug() langsung, bukan fetch semua artikel.
        // Sebelumnya: getArticles() → filter by slug (sangat tidak efisien, bisa ratusan data)
        // Sekarang: langsung hit endpoint /articles/{slug} dengan caching per-slug
        $rawArticle = $this->cmsApiService->getArticleBySlug($slug);

        if (!$rawArticle) {
            abort(404, 'Artikel tidak ditemukan.');
        }

        $article = ArticleDto::fromArray($rawArticle);

        return view('articles.show', compact('article'));
    }
}
