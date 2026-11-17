<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\RoomFacilityDto;
use App\Services\CmsApiService;

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
        $roomsData = collect($rawRooms)->map(fn ($item) => RoomFacilityDto::fromArray($item));

        // Pisahkan ruangan berdasarkan kategori
        $premiumRooms = $roomsData->where('category', 'premium')->values();
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
        $activeLocale = app()->getLocale();
        $rawRoom = $this->cmsApiService->getRoomFacilityBySlug($slug, $activeLocale);

        if (! $rawRoom) {
            $fallbackLocale = $activeLocale === 'id' ? 'en' : 'id';
            $fallbackRoom = $this->cmsApiService->getRoomFacilityBySlug($slug, $fallbackLocale);

            if ($fallbackRoom) {
                $translatedSlug = $fallbackRoom['alternate_slugs'][$activeLocale] ?? null;

                if (is_string($translatedSlug) && $translatedSlug !== '' && $translatedSlug !== $slug) {
                    return redirect()->route('ruang-perawatan.show', ['slug' => $translatedSlug]);
                }

                $rawRoom = $fallbackRoom;
            }
        }

        if (! $rawRoom) {
            abort(404, __('Ruang Perawatan tidak ditemukan.'));
        }

        $room = RoomFacilityDto::fromArray($rawRoom);

        return view('ruang-perawatan.show', compact('room'));
    }
}
