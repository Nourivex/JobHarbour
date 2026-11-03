<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class AnnouncementController
 *
 * Pengumuman situs yang sedang berlaku, untuk banner di halaman publik dan portal. Tanpa autentikasi;
 * penyaringan berdasarkan peran (audience) dilakukan di frontend karena isinya memang publik.
 */
class AnnouncementController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/announcements/active
     */
    public function active(): JsonResponse
    {
        $items = Announcement::live()->latest('id')->limit(3)->get();

        return $this->successResponse($items->map->toApiArray()->values(), 'Pengumuman aktif berhasil dimuat.');
    }
}
