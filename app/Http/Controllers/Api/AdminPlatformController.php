<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Announcement;
use App\Models\JobSource;
use App\Models\JobSyncLog;
use App\Models\PlatformSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminPlatformController
 *
 * Pengaturan platform yang aman diubah dari browser, pengumuman situs, dan pengelolaan sumber lowongan.
 * Kredensial dan konfigurasi teknis sengaja tidak tersedia di sini (tetap di .env / deployment).
 */
class AdminPlatformController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/admin/settings
     */
    public function settings(): JsonResponse
    {
        return $this->successResponse(PlatformSetting::current(), 'Pengaturan platform berhasil dimuat.');
    }

    /**
     * Method  : PUT
     * Endpoint: /api/admin/settings
     * Body    : { "hide_demo_jobs": true }
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'hide_demo_jobs' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pengaturan tidak valid.');
        }

        $changes = [];
        foreach (array_keys(PlatformSetting::DEFAULTS) as $key) {
            if ($request->has($key)) {
                $old = PlatformSetting::read($key);
                $new = $request->boolean($key);
                if ($old !== $new) {
                    PlatformSetting::write($key, $new, $admin->id);
                    $changes[$key] = ['from' => $old, 'to' => $new];
                }
            }
        }

        if ($changes) {
            AdminAuditLog::record($request, 'settings.updated', null, 'Pengaturan platform', null, $changes);
        }

        return $this->successResponse(PlatformSetting::current(), $changes ? 'Pengaturan platform disimpan.' : 'Tidak ada perubahan.');
    }

    /**
     * Method  : GET
     * Endpoint: /api/admin/announcements
     */
    public function announcements(): JsonResponse
    {
        $items = Announcement::with('creator:id,name')->latest('id')->limit(30)->get();

        return $this->successResponse($items->map(fn (Announcement $a) => $a->toApiArray(true))->values(), 'Daftar pengumuman berhasil dimuat.');
    }

    /**
     * Method  : POST
     * Endpoint: /api/admin/announcements
     * Body    : { "message", "level": "info" | "warning", "audience": "all" | "applicant" | "recruiter", "starts_at"?, "ends_at"? }
     */
    public function storeAnnouncement(Request $request): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:10|max:240',
            'level' => 'required|string|in:'.implode(',', Announcement::LEVELS),
            'audience' => 'required|string|in:'.implode(',', Announcement::AUDIENCES),
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:now|after:starts_at',
        ], [
            'message.min' => 'Pengumuman minimal 10 karakter.',
            'ends_at.after' => 'Waktu berakhir harus setelah sekarang dan setelah waktu mulai.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pengumuman belum valid.');
        }

        $item = Announcement::create($validator->validated() + ['created_by' => $admin->id, 'is_active' => true]);
        AdminAuditLog::record($request, 'announcement.published', $item, mb_substr($item->message, 0, 80), null,
            ['level' => $item->level, 'audience' => $item->audience, 'ends_at' => $item->ends_at?->toIso8601String()]);

        return $this->createdResponse($item->fresh('creator')->toApiArray(true), 'Pengumuman diterbitkan.');
    }

    /**
     * Mengakhiri pengumuman sebelum waktunya.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/announcements/{id}/end
     */
    public function endAnnouncement(Request $request, int $id): JsonResponse
    {
        $item = Announcement::find($id);
        if (! $item) {
            return $this->notFoundResponse('Pengumuman tidak ditemukan.');
        }
        if ($item->state() === 'ended') {
            return $this->errorResponse('Pengumuman ini sudah berakhir.', 409, null, 'ALREADY_ENDED');
        }

        $item->update(['is_active' => false]);
        AdminAuditLog::record($request, 'announcement.ended', $item, mb_substr($item->message, 0, 80));

        return $this->successResponse($item->fresh('creator')->toApiArray(true), 'Pengumuman diakhiri.');
    }

    /**
     * Mengaktifkan atau menonaktifkan sumber lowongan. Alasan wajib saat menonaktifkan.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/sources/{id}
     * Body    : { "is_enabled": false, "reason": "..." }
     */
    public function toggleSource(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_enabled' => 'required|boolean',
            'reason' => 'nullable|string|max:1000|min:10|required_if:is_enabled,false,0',
        ], [
            'reason.required_if' => 'Tulis alasan menonaktifkan sumber ini.',
            'reason.min' => 'Alasan minimal 10 karakter.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Perubahan sumber tidak valid.');
        }

        $source = JobSource::find($id);
        if (! $source) {
            return $this->notFoundResponse('Sumber lowongan tidak ditemukan.');
        }

        $enable = $request->boolean('is_enabled');
        if ($source->is_enabled === $enable) {
            return $this->errorResponse($enable ? 'Sumber ini sudah aktif.' : 'Sumber ini sudah nonaktif.', 409, null, 'INVALID_TRANSITION');
        }

        $source->update(['is_enabled' => $enable]);
        AdminAuditLog::record($request, $enable ? 'source.enabled' : 'source.disabled', $source, $source->name, $request->reason);

        return $this->successResponse(['id' => $source->id, 'is_enabled' => $source->is_enabled],
            $enable ? 'Sumber diaktifkan kembali.' : 'Sumber dinonaktifkan. Lowongannya tidak akan diambil sampai diaktifkan.');
    }

    /**
     * Riwayat sinkronisasi satu sumber (20 terakhir).
     *
     * Method  : GET
     * Endpoint: /api/admin/sources/{id}/logs
     */
    public function sourceLogs(int $id): JsonResponse
    {
        if (! JobSource::whereKey($id)->exists()) {
            return $this->notFoundResponse('Sumber lowongan tidak ditemukan.');
        }

        $logs = JobSyncLog::where('job_source_id', $id)->latest('id')->limit(20)->get()->map(fn (JobSyncLog $l) => [
            'id' => $l->id,
            'status' => $l->status,
            'vacancies_fetched' => $l->vacancies_fetched,
            'vacancies_created' => $l->vacancies_created,
            'vacancies_updated' => $l->vacancies_updated,
            'vacancies_expired' => $l->vacancies_expired,
            'duplicates_detected' => $l->duplicates_detected,
            'error_details' => $l->error_details,
            'duration_seconds' => $l->duration_seconds !== null ? round((float) $l->duration_seconds, 1) : null,
            'started_at' => $l->started_at?->toIso8601String(),
        ]);

        return $this->successResponse($logs, 'Riwayat sinkronisasi berhasil dimuat.');
    }
}
