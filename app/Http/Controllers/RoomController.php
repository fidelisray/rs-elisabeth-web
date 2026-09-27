<?php

namespace App\Http\Controllers;

use App\Services\CmsApiService;
use App\DTOs\Cms\RoomFacilityDto;

class RoomController extends Controller
{
    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    /**
     * Menampilkan halaman Ruang Perawatan dengan data dari Headless CMS.
     */
    public function index()
    {
        $rawRooms = $this->cmsApiService->getRoomFacilities();
        $roomsData = collect($rawRooms)->map(fn($item) => RoomFacilityDto::fromArray($item));

        // Pisahkan ruangan berdasarkan kategori
        $premiumRooms  = $roomsData->where('category', 'premium')->values();
        $standardRooms = $roomsData->where('category', 'standard')->values();

        return view('ruang-perawatan.index', compact(
            'roomsData',
            'premiumRooms',
            'standardRooms'
        ));
    }

    /**
     * Menampilkan halaman detail khusus untuk satu Ruang Perawatan.
     */
    public function show($slug)
    {
        $rawRoom = $this->cmsApiService->getRoomFacilityBySlug($slug);

        if (!$rawRoom) {
            abort(404, 'Ruang Perawatan tidak ditemukan.');
        }

        $room = RoomFacilityDto::fromArray($rawRoom);

        return view('ruang-perawatan.show', compact('room'));
    }
}
