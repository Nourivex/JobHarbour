<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Report;
use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class AdminReportController
 *
 * Antrean laporan pengguna dan banding recruiter. Menutup laporan tidak otomatis menurunkan atau
 * memulihkan lowongan: admin melakukannya lewat moderasi agar setiap tindakan tercatat terpisah.
 */
class AdminReportController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/admin/reports?status=&kind=&page=&per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 20), 5), 100);
        $status = $request->input('status', 'active');

        $page = Report::with(['reporter:id,name,email,role', 'handler:id,name'])
            ->when($status === 'active', fn ($q) => $q->whereIn('status', Report::ACTIVE))
            ->when(in_array($status, Report::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when(in_array($request->input('kind'), ['report', 'appeal'], true), fn ($q) => $q->where('kind', $request->input('kind')))
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_review' THEN 1 ELSE 2 END")
            ->oldest('created_at')
            ->paginate($perPage);

        return $this->successResponse($page->getCollection()->map(fn (Report $r) => $r->toApiArray(true))->values(), 'Daftar laporan berhasil dimuat.', 200, [
            'pagination' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'counts' => Report::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'categories' => Report::CATEGORIES,
        ]);
    }

    /**
     * Ubah status laporan. Alasan wajib saat menyelesaikan atau menolak.
     *
     * Method  : PATCH
     * Endpoint: /api/admin/reports/{id}
     * Body    : { "status": "in_review" | "resolved" | "dismissed", "resolution": "..." }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $admin = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:in_review,resolved,dismissed',
            'resolution' => 'nullable|string|max:1000|required_if:status,resolved|required_if:status,dismissed|min:10',
        ], [
            'resolution.required_if' => 'Tulis hasil pemeriksaan agar keputusan bisa ditelusuri.',
            'resolution.min' => 'Hasil pemeriksaan minimal 10 karakter.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Perubahan laporan tidak valid.');
        }

        $report = Report::find($id);
        if (! $report) {
            return $this->notFoundResponse('Laporan tidak ditemukan.');
        }
        if (! in_array($report->status, Report::ACTIVE, true)) {
            return $this->errorResponse('Laporan ini sudah ditutup.', 409, null, 'ALREADY_CLOSED');
        }

        $report->update([
            'status' => $request->status,
            'resolution' => $request->status === 'in_review' ? $report->resolution : $request->resolution,
            'handled_by' => $admin->id,
            'handled_at' => now(),
        ]);

        $target = $report->target();
        AdminAuditLog::record($request, 'report.'.$request->status, $report,
            ($report->kind === 'appeal' ? 'Banding: ' : 'Laporan: ').($target?->title ?? $target?->name ?? "#{$report->target_id}"),
            $request->resolution, ['kind' => $report->kind, 'category' => $report->category, 'target_type' => $report->target_type, 'target_id' => $report->target_id]);

        if ($request->status !== 'in_review') {
            $name = $target?->title ?? $target?->name ?? 'yang kamu laporkan';
            [$title, $body] = $report->kind === 'appeal'
                ? [$request->status === 'resolved' ? 'Pengajuan pemulihan disetujui' : 'Pengajuan pemulihan ditolak', "{$name}: {$request->resolution}"]
                : ['Laporanmu sudah ditinjau', $request->status === 'resolved'
                    ? "Terima kasih. Laporan atas {$name} sudah ditindaklanjuti admin."
                    : "Terima kasih. Laporan atas {$name} sudah diperiksa dan tidak ditemukan pelanggaran."];
            UserNotification::send($report->reporter_id, 'report.'.$request->status, $title, $body,
                $report->kind === 'appeal' ? "/recruiter/jobs/{$report->target_id}" : null);
        }

        return $this->successResponse($report->fresh(['reporter', 'handler'])->toApiArray(true), match ($request->status) {
            'in_review' => 'Laporan ditandai sedang ditinjau.',
            'resolved' => 'Laporan diselesaikan.',
            default => 'Laporan ditolak.',
        });
    }
}
