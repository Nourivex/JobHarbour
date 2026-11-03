<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\JobVacancy;
use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminJobController
 *
 * Moderasi lowongan oleh admin. Admin tidak mengedit isi lowongan; admin hanya bisa
 * menurunkan lowongan dari katalog (dengan alasan) atau memulihkannya. Isi tetap tanggung jawab recruiter.
 * Status moderasi terpisah dari status milik recruiter sehingga sinkronisasi agregator tidak memunculkannya lagi.
 */
class AdminJobController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/admin/jobs?id=&q=&status=&moderation=&source=&demo=&page=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $q = trim((string) $request->input('q'));

        $page = JobVacancy::query()
            ->with(['recruiter:id,name,email', 'recruiter.company:id,owner_id,name,verification_status'])
            ->withCount('applications')
            ->when($request->filled('id'), fn ($query) => $query->whereKey((int) $request->input('id')))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', "%{$q}%")->orWhere('company', 'like', "%{$q}%")))
            ->when(in_array($request->input('status'), ['draft', 'open', 'closed', 'archived'], true), fn ($query) => $query->where('status', $request->input('status')))
            ->when(in_array($request->input('moderation'), ['none', 'removed'], true), fn ($query) => $query->where('moderation_status', $request->input('moderation')))
            ->when(in_array($request->input('source'), ['direct', 'external', 'partner'], true), fn ($query) => $query->where('source_type', $request->input('source')))
            ->when($request->filled('demo'), fn ($query) => $query->demo(filter_var($request->input('demo'), FILTER_VALIDATE_BOOLEAN)))
            ->latest('id')
            ->paginate($perPage);

        $items = $page->getCollection()->map(fn (JobVacancy $j) => [
            'id' => $j->id,
            'title' => $j->title,
            'company' => $j->company,
            'location' => $j->location,
            'status' => $j->status,
            'moderation_status' => $j->moderation_status,
            'moderation_reason' => $j->moderation_reason,
            'moderated_at' => $j->moderated_at?->toIso8601String(),
            'source_type' => $j->source_type,
            'verification_status' => $j->verification_status,
            'is_demo' => $j->isDemoData(),
            'applications_count' => $j->applications_count,
            'recruiter' => $j->recruiter?->only(['id', 'name', 'email']),
            'company_record' => $j->recruiter?->company?->only(['id', 'name', 'verification_status']),
            'created_at' => $j->created_at?->toIso8601String(),
        ])->values();

        return $this->successResponse($items, 'Daftar lowongan untuk moderasi berhasil dimuat.', 200, [
            'pagination' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'counts' => [
                'status' => JobVacancy::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
                'removed' => JobVacancy::where('moderation_status', 'removed')->count(),
            ],
        ]);
    }

    /**
     * Menurunkan lowongan dari katalog atau memulihkannya. Alasan wajib saat menurunkan.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/jobs/{id}/moderation
     * Body    : { "action": "remove" | "restore", "reason": "..." }
     */
    public function moderate(Request $request, int $id): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:remove,restore',
            'reason' => 'nullable|string|max:1000|required_if:action,remove|min:10',
        ], [
            'action.in' => 'Aksi moderasi harus remove atau restore.',
            'reason.required_if' => 'Alasan wajib diisi agar recruiter tahu apa yang perlu diperbaiki.',
            'reason.min' => 'Alasan minimal 10 karakter.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Aksi moderasi tidak valid.');
        }

        $job = JobVacancy::find($id);

        if (! $job) {
            return $this->notFoundResponse('Lowongan tidak ditemukan.');
        }

        $remove = $request->action === 'remove';

        if ($remove === ($job->moderation_status === 'removed')) {
            return $this->errorResponse($remove ? 'Lowongan ini sudah diturunkan.' : 'Lowongan ini tidak sedang diturunkan.', 409, null, 'INVALID_TRANSITION');
        }

        $job->forceFill([
            'moderation_status' => $remove ? 'removed' : 'none',
            'moderation_reason' => $remove ? $request->reason : null,
            'moderated_by' => $admin->id,
            'moderated_at' => now(),
        ])->save();

        AdminAuditLog::record($request, $remove ? 'job.removed' : 'job.restored', $job, "{$job->title} · {$job->company}", $request->reason, ['job_status' => $job->status]);

        UserNotification::send($job->posted_by, $remove ? 'job.removed' : 'job.restored',
            $remove ? 'Lowonganmu diturunkan admin' : 'Lowonganmu dipulihkan',
            $remove ? "{$job->title}: {$request->reason}" : "{$job->title} kembali mengikuti statusnya semula.",
            "/recruiter/jobs/{$job->id}");

        return $this->successResponse([
            'id' => $job->id,
            'moderation_status' => $job->moderation_status,
            'moderation_reason' => $job->moderation_reason,
            'moderated_at' => $job->moderated_at?->toIso8601String(),
        ], $remove ? 'Lowongan diturunkan dari katalog. Recruiter melihat alasannya di portal.' : 'Lowongan dipulihkan dan kembali mengikuti statusnya semula.');
    }
}
