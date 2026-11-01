<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSource;
use App\Models\JobSyncLog;
use App\Models\JobVacancy;
use App\Services\Aggregator\JobAggregationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobSourceMonitoringController extends Controller
{
    use ApiResponse;

    /**
     * Mengembalikan ringkasan kesehatan seluruh sumber agregasi lowongan (Admin Dashboard).
     */
    public function getMonitoringSummary(): JsonResponse
    {
        $sources = JobSource::with(['syncLogs' => function ($q) {
            $q->latest()->limit(5);
        }])->get();

        $metrics = [
            'total_sources' => $sources->count(),
            'enabled_sources' => $sources->where('is_enabled', true)->count(),
            'vacancies_breakdown' => [
                'total_in_db' => JobVacancy::count(),
                'active' => JobVacancy::where('status', 'open')->where('lifecycle_status', 'active')->count(),
                'expired' => JobVacancy::where('lifecycle_status', 'expired')->orWhere('status', 'closed')->count(),
                'stale' => JobVacancy::where('lifecycle_status', 'stale')->count(),
                'demo_records' => JobVacancy::where('is_demo', true)->count(),
                'real_verified' => JobVacancy::where('is_demo', false)->where('verification_status', 'verified_official')->count(),
            ],
            'sources' => $sources->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'source_type' => $s->source_type,
                    'company_name' => $s->company_name,
                    'industry' => $s->industry,
                    'coverage_regions' => $s->coverage_regions,
                    'is_enabled' => $s->is_enabled,
                    'last_fetched_at' => $s->last_fetched_at?->toIso8601String(),
                    'last_success_at' => $s->last_success_at?->toIso8601String(),
                    'last_error_at' => $s->last_error_at?->toIso8601String(),
                    'last_error_message' => $s->last_error_message,
                    'health_status' => $s->last_error_at && (!$s->last_success_at || $s->last_error_at > $s->last_success_at) ? 'degraded' : 'healthy',
                    'total_vacancies_discovered' => $s->total_vacancies_discovered,
                    'total_vacancies_active' => $s->total_vacancies_active,
                ];
            }),
            'recent_logs' => JobSyncLog::with('source:id,name,slug')->latest()->limit(10)->get()
        ];

        return $this->successResponse($metrics, 'Ringkasan kesehatan mesin agregasi lowongan berhasil diambil.');
    }

    /**
     * Memicu sinkronisasi manual untuk satu sumber tertentu oleh administrator.
     */
    public function triggerSync(Request $request, JobAggregationService $service): JsonResponse
    {
        $request->validate([
            'source_slug' => 'nullable|string',
        ]);

        $query = JobSource::where('is_enabled', true);
        if ($slug = $request->input('source_slug')) {
            $query->where('slug', $slug);
        }

        $sources = $query->get();
        if ($sources->isEmpty()) {
            return $this->notFoundResponse('Sumber agregasi tidak ditemukan atau sedang dinonaktifkan.');
        }

        $logs = [];
        foreach ($sources as $source) {
            $logs[] = $service->syncSource($source);
        }

        $service->reconcileExpiredVacancies();

        return $this->successResponse([
            'sources_processed' => count($logs),
            'timestamp' => now()->toIso8601String(),
        ], 'Proses sinkronisasi dan rekonsiliasi kesegaran data berhasil dijalankan.');
    }
}
