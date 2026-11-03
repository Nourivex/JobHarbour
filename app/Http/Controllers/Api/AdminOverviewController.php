<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Application;
use App\Models\Company;
use App\Models\CompanyVerification;
use App\Models\JobSource;
use App\Models\JobVacancy;
use App\Models\Report;
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

        // Kesehatan pasar kerja: hanya lowongan terbuka yang tidak diturunkan admin
        $openJobs = JobVacancy::where('status', 'open')->where('moderation_status', '!=', 'removed')
            ->withCount('applications')->get(['id', 'title', 'requirements']);
        $avgScore = Application::avg('match_score');
        $reviewed = CompanyVerification::whereNotNull('reviewed_at')->get(['created_at', 'reviewed_at']);

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
                'reports_open' => Report::where('kind', 'report')->whereIn('status', Report::ACTIVE)->count(),
                'appeals_open' => Report::where('kind', 'appeal')->whereIn('status', Report::ACTIVE)->count(),
                'oldest_report_at' => Report::whereIn('status', Report::ACTIVE)->oldest('created_at')->value('created_at')?->toIso8601String(),
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
                'open_real' => JobVacancy::where('status', 'open')->demo(false)->where('moderation_status', '!=', 'removed')->count(),
                'open_demo' => JobVacancy::where('status', 'open')->demo()->where('moderation_status', '!=', 'removed')->count(),
                'demo_hidden' => (bool) \App\Models\PlatformSetting::read('hide_demo_jobs'),
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
            'market' => [
                'open_jobs' => $openJobs->count(),
                // null berarti belum ada datanya, bukan nol
                'applications_per_open_job' => $openJobs->count() ? round($openJobs->sum('applications_count') / $openJobs->count(), 1) : null,
                'open_without_applicants' => $openJobs->where('applications_count', 0)->count(),
                'avg_match_score' => $avgScore !== null ? round((float) $avgScore, 1) : null,
                'verification_days' => $reviewed->count()
                    ? round($reviewed->avg(fn ($v) => $v->created_at->diffInMinutes($v->reviewed_at) / 1440), 1)
                    : null,
                'verifications_reviewed' => $reviewed->count(),
            ],
            'growth' => $this->weeklyGrowth(8),
            'skills' => $this->skillBalance($openJobs, 6),
            'recent_actions' => AdminAuditLog::with('actor:id,name')->latest('id')->limit(8)->get()->map->toApiArray(),
        ], 'Ringkasan platform berhasil dimuat.', 200, ['window_days' => 30]);
    }

    /**
     * Jumlah pengguna, lowongan, dan lamaran baru per minggu (Senin sampai Minggu).
     * Dikelompokkan di PHP agar hasilnya sama di MySQL, PostgreSQL, dan SQLite.
     */
    private function weeklyGrowth(int $weeks): array
    {
        $start = now()->startOfWeek()->subWeeks($weeks - 1);
        $bucket = function ($dates) use ($start, $weeks) {
            $counts = array_fill(0, $weeks, 0);
            foreach ($dates as $date) {
                $i = intdiv((int) floor($start->diffInDays($date)), 7);
                if ($i >= 0 && $i < $weeks) {
                    $counts[$i]++;
                }
            }

            return $counts;
        };

        return [
            'week_starts' => collect(range(0, $weeks - 1))->map(fn ($i) => $start->copy()->addWeeks($i)->toDateString())->all(),
            'users' => $bucket(User::where('created_at', '>=', $start)->pluck('created_at')),
            'jobs' => $bucket(JobVacancy::where('created_at', '>=', $start)->pluck('created_at')),
            'applications' => $bucket(Application::where('created_at', '>=', $start)->pluck('created_at')),
        ];
    }

    /**
     * Skill yang paling banyak diminta lowongan terbuka dibanding jumlah pelamar aktif yang memilikinya.
     * Nama skill dinormalkan (huruf kecil, spasi dirapikan) sama seperti MatchmakingService.
     */
    private function skillBalance($openJobs, int $limit): array
    {
        $name = fn ($s) => trim((string) (is_array($s) ? ($s['skill'] ?? $s['name'] ?? '') : $s));
        $demand = [];
        $labels = [];
        foreach ($openJobs as $job) {
            $seen = [];
            foreach ((array) $job->requirements as $req) {
                $label = $name($req);
                $key = mb_strtolower($label);
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $demand[$key] = ($demand[$key] ?? 0) + 1;
                $labels[$key] ??= $label;
            }
        }
        arsort($demand);
        $top = array_slice($demand, 0, $limit, true);

        $applicants = User::where('role', 'applicant')->where('status', 'active')->get(['skills']);
        $supply = array_fill_keys(array_keys($top), 0);
        foreach ($applicants as $user) {
            $owned = collect((array) $user->skills)->map(fn ($s) => mb_strtolower($name($s)))->unique();
            foreach ($owned as $key) {
                if (isset($supply[$key])) {
                    $supply[$key]++;
                }
            }
        }

        return [
            'open_jobs' => $openJobs->count(),
            'applicants' => $applicants->count(),
            'items' => collect($top)->map(fn ($count, $key) => [
                'skill' => $labels[$key],
                'jobs' => $count,
                'applicants' => $supply[$key],
            ])->values()->all(),
        ];
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
            ->when(in_array($request->input('target_type'), ['user', 'company', 'jobvacancy', 'jobsource', 'companyverification', 'report', 'announcement'], true), fn ($q) => $q->where('target_type', $request->input('target_type')))
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
