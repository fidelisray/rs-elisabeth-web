<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DoctorApiService;
use App\Services\CmsApiService;
use App\DTOs\Cms\ArticleDto;
use App\DTOs\Cms\NewsDto;
use App\DTOs\Cms\PromotionDto;
use App\DTOs\Cms\BannerPromotionDto;
use App\DTOs\Cms\FacilityServiceDto;

class HomeController extends Controller
{
    public function __construct(
        protected DoctorApiService $doctorApiService,
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        // 1. Ambil data spesialisasi dokter (dari Medinfras API)
        $spesialisasi = $this->doctorApiService->getDaftarSpesialisasi();

        // 2. Ambil data Artikel Kesehatan (dari Headless CMS)
        $rawArticles = $this->cmsApiService->getArticles();
        $latestArticles = collect($rawArticles)
                            ->map(fn($item) => ArticleDto::fromArray($item))
                            ->take(7)
                            ->toArray();

        // 3. Ambil data Berita / ElisaNews (dari Headless CMS)
        $rawNews = $this->cmsApiService->getNews();
        $latestNews = collect($rawNews)
                        ->map(fn($item) => NewsDto::fromArray($item))
                        ->take(7)
                        ->toArray();
        
        // 4. Ambil data Promo (dari Headless CMS)
        $rawPromotions = $this->cmsApiService->getPromotions();
        $promotions = collect($rawPromotions)
                        ->map(fn($item) => PromotionDto::fromArray($item))
                        ->where('is_active', true)
                        ->take(6)
                        ->toArray();

        // 5. Ambil data Banner Promotions (dari Headless CMS)
        $rawBanners = $this->cmsApiService->getBannerPromotions();
        $banners = collect($rawBanners)
                        ->map(fn($item) => BannerPromotionDto::fromArray($item))
                        ->toArray();
        
        // 6. Ambil data Facility Services (dari Headless CMS)
        $rawFacilities = $this->cmsApiService->getFacilities();
        $facilities = collect($rawFacilities)
                        ->map(fn($item) => FacilityServiceDto::fromArray($item))
                        ->take(6)
                        ->toArray();

        return view('home.index', compact(
            'spesialisasi', 
            'latestArticles', 
            'latestNews', 
            'promotions', 
            'banners', 
            'facilities'
        ));
    }
}
