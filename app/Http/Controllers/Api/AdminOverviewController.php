<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyVerification;
use App\Models\JobSource;
use App\Models\JobVacancy;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class AdminOverviewController
 *
 * Ringkasan platform untuk Admin Portal dan log audit. Semua angka dihitung langsung dari database;
 * data demo dipisahkan dari data nyata. Dilindungi jwt.auth:admin.
 */
class AdminOverviewController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/admin/overview
     */
    public function overview(): JsonResponse
    {
        $oldestPending = CompanyVerification::where('status', 'pending')->oldest('created_at')->first();

        $sources = JobSource::all();
        $degraded = $sources->filter(fn ($s) => $s->last_error_at && (! $s->last_success_at || $s->last_error_at > $s->last_success_at));

        $since = now()->subDays(30);

        return $this->successResponse([
            // Antrean yang menunggu keputusan admin
            'queue' => [
                'verifications_pending' => CompanyVerification::where('status', 'pending')->count(),
                'oldest_pending_at' => $oldestPending?->created_at?->toIso8601String(),
                'companies_revision' => Company::where('verification_status', 'revision_required')->count(),
                'jobs_removed' => JobVacancy::where('moderation_status', 'removed')->count(),
                'users_suspended' => User::where('status', 'suspended')->count(),
                'companies_suspended' => Company::where('verification_status', 'suspended')->count(),
                'sources_degraded' => $degraded->count(),
            ],
            'users' => [
                'by_role' => User::query()->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role'),
                'new_30d' => User::where('created_at', '>=', $since)->count(),
            ],
            'companies' => [
                'by_status' => Company::query()->select('verification_status', DB::raw('count(*) as total'))->groupBy('verification_status')->pluck('total', 'verification_status'),
                'new_30d' => Company::where('created_at', '>=', $since)->count(),
            ],
            'jobs' => [
                'by_status' => JobVacancy::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
                'open_real' => JobVacancy::where('status', 'open')->where('is_demo', false)->where('moderation_status', '!=', 'removed')->count(),
                'open_demo' => JobVacancy::where('status', 'open')->where('is_demo', true)->where('moderation_status', '!=', 'removed')->count(),
                'new_30d' => JobVacancy::where('created_at', '>=', $since)->count(),
            ],
            'applications' => [
                'total' => Application::count(),
                'new_30d' => Application::where('created_at', '>=', $since)->count(),
            ],
            'sources' => [
                'total' => $sources->count(),
                'enabled' => $sources->where('is_enabled', true)->count(),
                'degraded' => $degraded->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'last_error_at' => $s->last_error_at?->toIso8601String(),
                    'last_error_message' => $s->last_error_message,
                ])->values(),
            ],
            'recent_actions' => AdminAuditLog::with('actor:id,name')->latest('id')->limit(8)->get()->map->toApiArray(),
        ], 'Ringkasan platform berhasil dimuat.', 200, ['window_days' => 30]);
    }

    /**
     * Log audit tindakan admin (baca saja).
     *
     * Method  : GET
     * Endpoint: /api/admin/audit-logs?action=&target_type=&actor_id=&page=&per_page=
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 25), 5), 100);

        $page = AdminAuditLog::with('actor:id,name')
            ->when(array_key_exists((string) $request->input('action'), AdminAuditLog::ACTIONS), fn ($q) => $q->where('action', $request->input('action')))
            ->when(in_array($request->input('target_type'), ['user', 'company', 'jobvacancy', 'jobsource', 'companyverification'], true), fn ($q) => $q->where('target_type', $request->input('target_type')))
            ->when($request->filled('actor_id'), fn ($q) => $q->where('actor_id', (int) $request->input('actor_id')))
            ->latest('id')
            ->paginate($perPage);

        return $this->successResponse($page->getCollection()->map->toApiArray()->values(), 'Log audit berhasil dimuat.', 200, [
            'pagination' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
            'actions' => AdminAuditLog::ACTIONS,
        ]);
    }
}
