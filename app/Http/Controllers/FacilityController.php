<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CmsApiService;
use App\DTOs\Cms\FacilityServiceDto;

class FacilityController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index()
    {
        $rawFacilities = $this->cmsApiService->getFacilities();
        $facilities = collect($rawFacilities)->map(fn($item) => FacilityServiceDto::fromArray($item))->values();
        
        return view('facilities-and-services.index', compact('facilities'));
    }
}
