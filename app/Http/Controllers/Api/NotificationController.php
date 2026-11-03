<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class NotificationController
 *
 * Lonceng pemberitahuan milik pengguna yang sedang masuk. Hanya pemilik yang bisa membaca
 * dan menandai pemberitahuannya (pemberitahuan milik orang lain diperlakukan tidak ada).
 */
class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();

        $items = UserNotification::where('user_id', $user->id)->latest('id')->limit(20)->get();

        return $this->successResponse($items->map->toApiArray()->values(), 'Pemberitahuan berhasil dimuat.', 200, [
            'unread_count' => UserNotification::where('user_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Method  : PATCH
     * Endpoint: /api/notifications/{id}/read
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();
        $item = UserNotification::where('user_id', $user->id)->find($id);

        if (! $item) {
            return $this->notFoundResponse('Pemberitahuan tidak ditemukan.');
        }

        $item->read_at ??= now();
        $item->save();

        return $this->successResponse($item->toApiArray(), 'Pemberitahuan ditandai sudah dibaca.');
    }

    /**
     * Method  : POST
     * Endpoint: /api/notifications/read-all
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();
        $count = UserNotification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);

        return $this->successResponse(['marked' => $count], 'Semua pemberitahuan ditandai sudah dibaca.');
    }
}
