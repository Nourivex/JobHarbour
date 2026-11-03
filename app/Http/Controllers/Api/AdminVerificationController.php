<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Company;
use App\Models\CompanyVerification;
use App\Models\JobVacancy;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminVerificationController
 *
 * Pemeriksaan pengajuan verifikasi perusahaan oleh admin.
 * Semua rute di controller ini dilindungi middleware jwt.auth:admin.
 */
class AdminVerificationController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/admin/verifications?status=pending|approved|revision_required|rejected
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('status');

        $submissions = CompanyVerification::with(['company.owner:id,name,email', 'reviewer:id,name'])
            ->when(in_array($status, ['pending', 'approved', 'revision_required', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->get()
            ->map(fn (CompanyVerification $v) => $v->toApiArray() + [
                'company' => $v->company ? [
                    'id' => $v->company->id,
                    'name' => $v->company->name,
                    'industry' => $v->company->industry,
                    'city' => $v->company->city,
                    'website' => $v->company->website,
                    'verification_status' => $v->company->verification_status,
                    'logo_url' => $v->company->logo_url,
                    'owner' => $v->company->owner?->only(['id', 'name', 'email']),
                ] : null,
            ]);

        return $this->successResponse($submissions, 'Daftar pengajuan verifikasi berhasil dimuat.', 200, [
            'counts' => CompanyVerification::query()
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    /**
     * Menyetujui, meminta revisi, atau menolak satu pengajuan.
     * Revisi dipakai bila dokumen kurang atau tidak terbaca: recruiter memperbaiki lalu mengajukan ulang.
     * Catatan wajib diisi untuk revisi dan penolakan.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/verifications/{id}
     * Body    : { "decision": "approve" | "revision" | "reject", "admin_notes": "..." }
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'decision' => 'required|string|in:approve,revision,reject',
            'admin_notes' => 'nullable|string|max:1000|required_if:decision,reject|required_if:decision,revision',
        ], [
            'decision.in' => 'Keputusan harus approve, revision, atau reject.',
            'admin_notes.required_if' => 'Catatan wajib diisi agar recruiter tahu apa yang perlu diperbaiki.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Keputusan verifikasi tidak valid.');
        }

        $submission = CompanyVerification::with('company')->find($id);

        if (! $submission) {
            return $this->notFoundResponse('Pengajuan verifikasi tidak ditemukan.');
        }

        if ($submission->status !== 'pending') {
            return $this->errorResponse('Pengajuan ini sudah diperiksa sebelumnya.', 409, null, 'ALREADY_REVIEWED');
        }

        $decision = $request->decision;
        $approved = $decision === 'approve';
        [$submissionStatus, $companyStatus, $message] = match ($decision) {
            'approve' => ['approved', 'verified', 'Perusahaan berhasil diverifikasi.'],
            'revision' => ['revision_required', 'revision_required', 'Recruiter diminta memperbaiki pengajuan.'],
            default => ['rejected', 'rejected', 'Pengajuan verifikasi ditolak.'],
        };

        DB::transaction(function () use ($submission, $approved, $submissionStatus, $companyStatus, $request, $admin) {
            $submission->update([
                'status' => $submissionStatus,
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $submission->company->update([
                'verification_status' => $companyStatus,
                'verification_note' => $approved ? null : $request->admin_notes,
                'verified_at' => $approved ? now() : null,
            ]);

            if ($approved && $submission->company->owner_id) {
                \App\Models\JobVacancy::where('posted_by', $submission->company->owner_id)
                    ->where('status', 'open')
                    ->update([
                        'verification_status' => 'verified_official',
                        'verification_notes' => 'Perusahaan terverifikasi resmi oleh JobHarbour.',
                    ]);
            }
        });

        AdminAuditLog::record($request, match ($decision) {
            'approve' => 'company.verification_approved',
            'revision' => 'company.verification_revision',
            default => 'company.verification_rejected',
        }, $submission->company, $submission->company->name, $request->admin_notes, ['verification_id' => $submission->id]);

        return $this->successResponse($submission->fresh(['reviewer'])->toApiArray(), $message);
    }

    /**
     * Menangguhkan perusahaan terverifikasi, atau memulihkannya kembali.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/companies/{id}/status
     * Body    : { "status": "suspended" | "verified", "note": "..." }
     */
    public function setCompanyStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:suspended,verified',
            'note' => 'nullable|string|max:1000|required_if:status,suspended',
        ], [
            'note.required_if' => 'Alasan penangguhan wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Status perusahaan tidak valid.');
        }

        $company = Company::find($id);

        if (! $company) {
            return $this->notFoundResponse('Perusahaan tidak ditemukan.');
        }

        if ($request->status === 'verified' && $company->verification_status !== 'suspended') {
            return $this->errorResponse('Hanya perusahaan yang sedang ditangguhkan yang bisa dipulihkan.', 409, null, 'INVALID_TRANSITION');
        }

        if ($request->status === 'suspended' && $company->verification_status !== 'verified') {
            return $this->errorResponse('Hanya perusahaan terverifikasi yang bisa ditangguhkan.', 409, null, 'INVALID_TRANSITION');
        }

        $company->update([
            'verification_status' => $request->status,
            'verification_note' => $request->status === 'suspended' ? $request->note : null,
        ]);

        AdminAuditLog::record($request, $request->status === 'suspended' ? 'company.suspended' : 'company.reinstated', $company, $company->name, $request->note);

        return $this->successResponse($company->fresh(), $request->status === 'suspended'
            ? 'Perusahaan ditangguhkan. Lowongan barunya tidak dapat dipublikasikan.'
            : 'Penangguhan perusahaan dicabut.');
    }

    /**
     * Daftar semua perusahaan untuk admin (halaman di server).
     *
     * Method  : GET
     * Endpoint: /api/admin/companies?q=&status=&page=&per_page=
     */
    public function companies(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $q = trim((string) $request->input('q'));

        $page = Company::with('owner:id,name,email')
            ->withCount('verifications')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('city', 'like', "%{$q}%")))
            ->when(in_array($request->input('status'), Company::STATUSES, true), fn ($query) => $query->where('verification_status', $request->input('status')))
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'revision_required' THEN 1 WHEN 'suspended' THEN 2 ELSE 3 END")
            ->latest('id')
            ->paginate($perPage);

        $ownerIds = $page->getCollection()->pluck('owner_id')->filter()->all();
        $jobCounts = JobVacancy::whereIn('posted_by', $ownerIds)
            ->select('posted_by', DB::raw('count(*) as total'), DB::raw("sum(case when status = 'open' then 1 else 0 end) as open_total"))
            ->groupBy('posted_by')->get()->keyBy('posted_by');

        $items = $page->getCollection()->map(fn (Company $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'industry' => $c->industry,
            'city' => $c->city,
            'logo_url' => $c->logo_url,
            'verification_status' => $c->verification_status,
            'verified_at' => $c->verified_at?->toIso8601String(),
            'created_at' => $c->created_at?->toIso8601String(),
            'owner' => $c->owner?->only(['id', 'name', 'email']),
            'jobs_total' => (int) ($jobCounts[$c->owner_id]->total ?? 0),
            'jobs_open' => (int) ($jobCounts[$c->owner_id]->open_total ?? 0),
            'verifications_count' => $c->verifications_count,
        ])->values();

        return $this->successResponse($items, 'Daftar perusahaan berhasil dimuat.', 200, [
            'pagination' => $this->paginationMeta($page),
            'counts' => Company::query()->select('verification_status', DB::raw('count(*) as total'))->groupBy('verification_status')->pluck('total', 'verification_status'),
        ]);
    }

    /**
     * Detail satu perusahaan: profil, pemilik, riwayat verifikasi, dan lowongannya.
     *
     * Method  : GET
     * Endpoint: /api/admin/companies/{id}
     */
    public function company(int $id): JsonResponse
    {
        $company = Company::with(['owner', 'verifications.reviewer:id,name'])->find($id);

        if (! $company) {
            return $this->notFoundResponse('Perusahaan tidak ditemukan.');
        }

        $jobs = $company->owner_id
            ? JobVacancy::where('posted_by', $company->owner_id)->withCount('applications')->latest('id')->get()
                ->map(fn (JobVacancy $j) => [
                    'id' => $j->id,
                    'title' => $j->title,
                    'status' => $j->status,
                    'moderation_status' => $j->moderation_status,
                    'location' => $j->location,
                    'applications_count' => $j->applications_count,
                    'created_at' => $j->created_at?->toIso8601String(),
                ])
            : collect();

        return $this->successResponse([
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry,
            'size' => $company->size,
            'founded_year' => $company->founded_year,
            'description' => $company->description,
            'address' => $company->address,
            'city' => $company->city,
            'website' => $company->website,
            'email' => $company->email,
            'phone' => $company->phone,
            'logo_url' => $company->logo_url,
            'verification_status' => $company->verification_status,
            'verification_note' => $company->verification_note,
            'verified_at' => $company->verified_at?->toIso8601String(),
            'created_at' => $company->created_at?->toIso8601String(),
            'owner' => $company->owner ? [
                'id' => $company->owner->id,
                'name' => $company->owner->name,
                'email' => $company->owner->email,
                'status' => $company->owner->status,
                'created_at' => $company->owner->created_at?->toIso8601String(),
            ] : null,
            'verifications' => $company->verifications->sortByDesc('id')->values()->map(fn (CompanyVerification $v) => $v->toApiArray()),
            'jobs' => $jobs,
        ], 'Detail perusahaan berhasil dimuat.');
    }

    private function paginationMeta($page): array
    {
        return [
            'current_page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'last_page' => $page->lastPage(),
        ];
    }
}
