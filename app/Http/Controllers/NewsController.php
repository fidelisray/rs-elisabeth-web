<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CmsApiService;
use App\DTOs\Cms\NewsDto;

class NewsController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawNews = $this->cmsApiService->getNews();
        $newsList = array_map(fn($item) => NewsDto::fromArray($item), $rawNews);

        return view('news.index', compact('newsList'));
    }

    public function show($slug)
    {
        $rawNews = $this->cmsApiService->getNewsBySlug($slug);

        if (!$rawNews) {
            abort(404, 'Berita tidak ditemukan.');
        }

        $news = NewsDto::fromArray($rawNews);

        return view('news.show', compact('news'));
    }
}
