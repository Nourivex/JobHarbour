<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminUserController
 *
 * Pengelolaan akun pengguna oleh admin: daftar, detail, dan status akun (aktif / ditangguhkan).
 * Data yang dikirim dibatasi pada yang dibutuhkan untuk menangani akun; password dan token tidak pernah dikirim.
 * Peran pengguna tidak dapat diubah lewat API ini. Semua rute dilindungi jwt.auth:admin.
 */
class AdminUserController extends Controller
{
    use ApiResponse;

    private const ROLES = ['applicant', 'recruiter', 'admin'];

    private const STATUSES = ['active', 'suspended'];

    /**
     * Method  : GET
     * Endpoint: /api/admin/users?q=&role=&status=&page=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $q = trim((string) $request->input('q'));

        $page = User::query()
            ->with('company:id,owner_id,name,verification_status')
            ->withCount('applications')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when(in_array($request->input('role'), self::ROLES, true), fn ($query) => $query->where('role', $request->input('role')))
            ->when(in_array($request->input('status'), self::STATUSES, true), fn ($query) => $query->where('status', $request->input('status')))
            ->latest('id')
            ->paginate($perPage);

        $jobCounts = JobVacancy::whereIn('posted_by', $page->getCollection()->pluck('id'))
            ->select('posted_by', DB::raw('count(*) as total'))->groupBy('posted_by')->pluck('total', 'posted_by');

        $items = $page->getCollection()->map(fn (User $u) => $this->summary($u) + [
            'jobs_count' => (int) ($jobCounts[$u->id] ?? 0),
        ])->values();

        return $this->successResponse($items, 'Daftar pengguna berhasil dimuat.', 200, [
            'pagination' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'counts' => [
                'role' => User::query()->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role'),
                'status' => User::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            ],
        ]);
    }

    /**
     * Method  : GET
     * Endpoint: /api/admin/users/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = User::with(['company:id,owner_id,name,verification_status', 'statusChanger:id,name'])->withCount('applications')->find($id);

        if (! $user) {
            return $this->notFoundResponse('Pengguna tidak ditemukan.');
        }

        $applications = $user->role === 'applicant'
            ? Application::with('jobVacancy:id,title,company')->where('user_id', $user->id)->latest('id')->limit(10)->get()
                ->map(fn (Application $a) => [
                    'id' => $a->id,
                    'job_title' => $a->jobVacancy?->title,
                    'company' => $a->jobVacancy?->company,
                    'status' => $a->status,
                    'created_at' => $a->created_at?->toIso8601String(),
                ])
            : collect();

        $jobs = $user->role === 'recruiter'
            ? JobVacancy::where('posted_by', $user->id)->latest('id')->limit(10)->get(['id', 'title', 'status', 'moderation_status', 'created_at'])
            : collect();

        return $this->successResponse($this->summary($user) + [
            'phone' => $user->phone,
            'location' => $user->location,
            'skills_count' => is_array($user->skills) ? count($user->skills) : 0,
            'status_reason' => $user->status_reason,
            'status_changed_at' => $user->status_changed_at?->toIso8601String(),
            'status_changed_by' => $user->statusChanger?->only(['id', 'name']),
            'recent_applications' => $applications,
            'recent_jobs' => $jobs,
            'history' => AdminAuditLog::with('actor:id,name')->where('target_type', 'user')->where('target_id', $user->id)
                ->latest('id')->limit(20)->get()->map->toApiArray(),
        ], 'Detail pengguna berhasil dimuat.');
    }

    /**
     * Menangguhkan atau mengaktifkan kembali akun. Alasan wajib saat menangguhkan.
     * Admin tidak dapat menangguhkan dirinya sendiri atau admin lain.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/users/{id}/status
     * Body    : { "status": "suspended" | "active", "reason": "..." }
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:'.implode(',', self::STATUSES),
            'reason' => 'nullable|string|max:1000|required_if:status,suspended|min:10',
        ], [
            'status.in' => 'Status akun harus active atau suspended.',
            'reason.required_if' => 'Alasan penangguhan wajib diisi.',
            'reason.min' => 'Alasan minimal 10 karakter agar keputusan bisa ditelusuri.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Perubahan status akun tidak valid.');
        }

        $user = User::find($id);

        if (! $user) {
            return $this->notFoundResponse('Pengguna tidak ditemukan.');
        }

        if ($user->id === $admin->id) {
            return $this->forbiddenResponse('Admin tidak dapat mengubah status akunnya sendiri.', 'SELF_ACTION_FORBIDDEN');
        }

        if ($user->role === 'admin') {
            return $this->forbiddenResponse('Status akun admin tidak dapat diubah lewat Admin Portal.', 'ADMIN_PROTECTED');
        }

        if ($user->status === $request->status) {
            return $this->errorResponse($request->status === 'suspended' ? 'Akun ini sudah ditangguhkan.' : 'Akun ini sudah aktif.', 409, null, 'INVALID_TRANSITION');
        }

        $user->forceFill([
            'status' => $request->status,
            'status_reason' => $request->status === 'suspended' ? $request->reason : null,
            'status_changed_at' => now(),
            'status_changed_by' => $admin->id,
        ])->save();

        AdminAuditLog::record($request, $request->status === 'suspended' ? 'user.suspended' : 'user.reactivated', $user, "{$user->name} ({$user->email})", $request->reason, ['role' => $user->role]);

        return $this->successResponse($this->summary($user->fresh(['company'])), $request->status === 'suspended'
            ? 'Akun ditangguhkan. Pengguna tidak dapat masuk atau memakai API sampai diaktifkan kembali.'
            : 'Akun diaktifkan kembali.');
    }

    private function summary(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'status' => $u->status ?? 'active',
            'created_at' => $u->created_at?->toIso8601String(),
            'applications_count' => $u->applications_count ?? null,
            'company' => $u->company ? $u->company->only(['id', 'name', 'verification_status']) : null,
        ];
    }
}
