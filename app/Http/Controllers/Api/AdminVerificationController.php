<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyVerification;
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
        });

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

        return $this->successResponse($company->fresh(), $request->status === 'suspended'
            ? 'Perusahaan ditangguhkan. Lowongan barunya tidak dapat dipublikasikan.'
            : 'Penangguhan perusahaan dicabut.');
    }
}
