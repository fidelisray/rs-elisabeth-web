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
        // Ideally the API should have a getArticleBySlug endpoint, but for now we fetch all and filter
        // Wait, CmsApiService has getArticleById, not getArticleBySlug. Let's filter from the list as the old code did.
        $rawArticles = $this->cmsApiService->getArticles();
        $articlesList = collect($rawArticles)->map(fn($item) => ArticleDto::fromArray($item));
        
        $article = $articlesList->firstWhere('slug', $slug);

        if (!$article) {
            abort(404, 'Artikel tidak ditemukan.');
        }

        return view('articles.show', compact('article'));
    }
}
