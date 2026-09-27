<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CmsApiService;
use App\DTOs\Cms\PromotionDto;

class PromotionsController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index(Request $request)
    {
        $rawPromotions = $this->cmsApiService->getPromotions();
        
        $promotions = collect($rawPromotions)->map(fn($item) => PromotionDto::fromArray($item))->values();

        return view('promotions.index', compact('promotions'));
    }
}
