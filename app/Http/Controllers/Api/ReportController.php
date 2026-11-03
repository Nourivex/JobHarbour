<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Report;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ReportController
 *
 * Pengguna yang masuk melaporkan lowongan atau perusahaan bermasalah, dan recruiter mengajukan
 * banding atas lowongannya yang diturunkan admin. Keduanya ditangani di antrean Laporan Admin Portal.
 */
class ReportController extends Controller
{
    use ApiResponse;

    /**
     * Method  : POST
     * Endpoint: /api/reports
     * Body    : { "target_type": "job" | "company", "target_id": 1, "category": "scam", "description": "..." }
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();
        $categories = array_keys(array_diff_key(Report::CATEGORIES, ['appeal' => true]));

        $validator = Validator::make($request->all(), [
            'target_type' => 'required|string|in:job,company',
            'target_id' => 'required|integer|min:1',
            'category' => 'required|string|in:'.implode(',', $categories),
            'description' => 'required|string|min:20|max:2000',
        ], [
            'category.in' => 'Pilih salah satu jenis masalah.',
            'description.required' => 'Ceritakan apa yang janggal agar admin bisa memeriksanya.',
            'description.min' => 'Ceritakan minimal 20 karakter agar admin bisa memeriksanya.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Laporan belum lengkap.');
        }

        $target = $request->target_type === 'job' ? JobVacancy::find($request->target_id) : Company::find($request->target_id);
        if (! $target) {
            return $this->notFoundResponse('Yang dilaporkan tidak ditemukan.');
        }

        $own = $request->target_type === 'job' ? $target->posted_by === $user->id : $target->owner_id === $user->id;
        if ($own) {
            return $this->forbiddenResponse('Anda tidak bisa melaporkan lowongan atau perusahaan milik sendiri.', 'SELF_REPORT_FORBIDDEN');
        }

        $duplicate = Report::where('reporter_id', $user->id)->where('kind', 'report')
            ->where('target_type', $request->target_type)->where('target_id', $target->getKey())
            ->whereIn('status', Report::ACTIVE)->exists();
        if ($duplicate) {
            return $this->errorResponse('Anda sudah melaporkan ini dan laporannya masih diperiksa admin.', 409, null, 'DUPLICATE_REPORT');
        }

        $report = Report::create([
            'kind' => 'report',
            'reporter_id' => $user->id,
            'target_type' => $request->target_type,
            'target_id' => $target->getKey(),
            'category' => $request->category,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return $this->createdResponse($report->toApiArray(), 'Laporan terkirim. Admin JobHarbour akan memeriksanya.')
            ->header('Location', url("/api/reports/{$report->id}"));
    }

    /**
     * Banding recruiter atas lowongannya yang diturunkan admin. Hanya satu banding aktif per lowongan.
     *
     * Method  : POST
     * Endpoint: /api/jobs/{id}/appeal
     * Body    : { "description": "Apa yang sudah diperbaiki" }
     */
    public function appeal(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|min:20|max:2000',
        ], [
            'description.required' => 'Jelaskan apa yang sudah diperbaiki pada lowongan ini.',
            'description.min' => 'Jelaskan minimal 20 karakter agar admin bisa menilai.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pengajuan pemulihan belum lengkap.');
        }

        $job = JobVacancy::find($id);
        if (! $job) {
            return $this->notFoundResponse('Lowongan tidak ditemukan.');
        }
        if ($job->posted_by !== $user->id) {
            return $this->forbiddenResponse('Akses ditolak. Anda bukan pemilik lowongan ini.', 'IDOR_FORBIDDEN');
        }
        if ($job->moderation_status !== 'removed') {
            return $this->errorResponse('Lowongan ini tidak sedang diturunkan admin.', 409, null, 'NOT_REMOVED');
        }
        if (Report::where('kind', 'appeal')->where('target_type', 'job')->where('target_id', $job->id)->whereIn('status', Report::ACTIVE)->exists()) {
            return $this->errorResponse('Pengajuan pemulihan untuk lowongan ini masih ditinjau admin.', 409, null, 'DUPLICATE_APPEAL');
        }

        $appeal = Report::create([
            'kind' => 'appeal',
            'reporter_id' => $user->id,
            'target_type' => 'job',
            'target_id' => $job->id,
            'category' => 'appeal',
            'description' => $request->description,
            'status' => 'open',
        ]);

        return $this->createdResponse($appeal->toApiArray(), 'Pengajuan pemulihan terkirim. Admin akan meninjaunya.');
    }
}
