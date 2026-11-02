<?php

namespace App\Services\Aggregator;

use App\Models\JobSource;
use App\Models\JobSyncLog;
use App\Models\JobVacancy;
use App\Models\User;
use App\Services\Aggregator\Contracts\JobSourceAdapterInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobAggregationService
{
    /**
     * Hitung canonical hash unik untuk mendeteksi duplikasi antar-sumber.
     */
    public function calculateCanonicalHash(array $vacancy): string
    {
        $normalizedTitle = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $vacancy['title'] ?? '')));
        $normalizedCompany = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $vacancy['company'] ?? '')));
        $normalizedLocation = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $vacancy['location'] ?? '')));

        return hash('sha256', "{$normalizedTitle}:{$normalizedCompany}:{$normalizedLocation}");
    }

    /**
     * Sinkronkan satu sumber lowongan kerja secara end-to-end.
     */
    public function syncSource(JobSource $source): JobSyncLog
    {
        $startTime = microtime(true);
        $cacheKey = "job_source_feed:{$source->id}";
        $cacheTtlHours = (int) config('services.aggregator.cache_ttl_hours', 6);

        $log = new JobSyncLog([
            'job_source_id' => $source->id,
            'status' => 'running',
            'started_at' => now(),
        ]);
        $log->save();

        $source->last_fetched_at = now();
        $source->save();

        try {
            // 1. Instansiasi Adapter
            $adapterClass = $source->adapter_class;
            if (!class_exists($adapterClass)) {
                throw new \InvalidArgumentException("Adapter class {$adapterClass} tidak ditemukan.");
            }

            /** @var JobSourceAdapterInterface $adapter */
            $adapter = app($adapterClass);
            $rawVacancies = $adapter->fetch($source);

            // Simpan cache feed untuk fail-open fallback
            if (!empty($rawVacancies)) {
                Cache::put($cacheKey, $rawVacancies, now()->addHours($cacheTtlHours));
            }

            $createdCount = 0;
            $updatedCount = 0;
            $duplicatesCount = 0;
            $now = now();

            foreach ($rawVacancies as $v) {
                $canonicalHash = $this->calculateCanonicalHash($v);
                $extRef = $v['external_id'] ?? null;

                // 2. Cek Duplikasi Berdasarkan Canonical Hash atau External Reference
                $existing = JobVacancy::where('canonical_hash', $canonicalHash)
                    ->orWhere(function ($q) use ($extRef) {
                        if ($extRef) $q->where('external_reference', $extRef);
                    })
                    ->first();

                if ($existing) {
                    // Update metadata kesegaran (Freshness)
                    $existing->job_source_id = $source->id;
                    $existing->last_seen_at = $now;
                    $existing->last_checked_at = $now;
                    if ($existing->lifecycle_status === 'stale') {
                        $existing->lifecycle_status = 'active';
                    }
                    if (isset($v['expires_at'])) {
                        $existing->expires_at = $v['expires_at'];
                    }
                    $existing->save();
                    $updatedCount++;
                    $duplicatesCount++;
                } else {
                    // 3. Simpan Lowongan Baru yang Terverifikasi
                    JobVacancy::create([
                        'job_source_id' => $source->id,
                        'title' => $v['title'],
                        'company' => $v['company'],
                        'description' => $v['description'] ?? 'Deskripsi lowongan resmi.',
                        'location' => $v['location'] ?? 'Indonesia',
                        'administrative_code' => $v['administrative_code'] ?? null,
                        'industrial_zone_id' => $v['industrial_zone_id'] ?? null,
                        'employment_type' => $v['employment_type'] ?? 'full-time',
                        'salary_min' => $v['salary_min'] ?? null,
                        'salary_max' => $v['salary_max'] ?? null,
                        'quota' => $v['quota'] ?? 1,
                        'filled' => 0,
                        'requirements' => $v['requirements'] ?? [],
                        'status' => 'open',
                        'lifecycle_status' => 'active',
                        'source_type' => $v['source_type'] ?? 'external',
                        'source_company' => $v['source_company'] ?? $source->company_name,
                        'source_url' => $v['source_url'] ?? $source->base_url,
                        'application_url' => $v['application_url'] ?? $source->base_url,
                        'external_reference' => $extRef,
                        'canonical_hash' => $canonicalHash,
                        'published_at' => $v['published_at'] ?? $now,
                        'first_seen_at' => $now,
                        'last_seen_at' => $now,
                        'last_checked_at' => $now,
                        'expires_at' => $v['expires_at'] ?? now()->addDays(30),
                        'verification_status' => $v['verification_status'] ?? 'verified_official',
                        'verification_notes' => $v['verification_notes'] ?? 'Diverifikasi oleh mesin agregator otomatis.',
                        'is_demo' => false,
                        'job_source_type' => $v['job_source_type'] ?? 'official_career_page',
                        'location_verification_status' => $v['location_verification_status'] ?? 'approximate_city',
                        'location_data_source' => $v['location_data_source'] ?? $source->name,
                        'posted_by' => $v['posted_by'] ?? User::where('role', 'admin')->value('id') ?? User::where('email', 'admin@jobharbour.id')->value('id'),
                    ]);
                    $createdCount++;
                }
            }

            // 4. Catat Keberhasilan & Kesehatan Sumber
            $duration = round(microtime(true) - $startTime, 2);
            $log->update([
                'status' => 'success',
                'vacancies_fetched' => count($rawVacancies),
                'vacancies_created' => $createdCount,
                'vacancies_updated' => $updatedCount,
                'duplicates_detected' => $duplicatesCount,
                'duration_seconds' => $duration,
                'finished_at' => now(),
            ]);

            $source->update([
                'last_success_at' => now(),
                'total_vacancies_discovered' => \App\Models\JobVacancy::where('job_source_id', $source->id)->count(),
                'total_vacancies_active' => \App\Models\JobVacancy::where('job_source_id', $source->id)->where('status', 'open')->where('lifecycle_status', 'active')->count(),
            ]);

            return $log;

        } catch (\Throwable $e) {
            $duration = round(microtime(true) - $startTime, 2);
            Log::error("JobAggregationService Error [Source: {$source->name}]: " . $e->getMessage());

            $fallbackNote = Cache::has($cacheKey) ? ' (Tersedia cadangan cache feed)' : '';

            $log->update([
                'status' => 'failed',
                'error_details' => $e->getMessage() . $fallbackNote,
                'duration_seconds' => $duration,
                'finished_at' => now(),
            ]);

            $source->update([
                'last_error_at' => now(),
                'last_error_message' => substr($e->getMessage() . $fallbackNote, 0, 500),
            ]);

            return $log;
        }
    }

    /**
     * Rekonsiliasi lowongan kedaluwarsa (Lifecycle Management).
     */
    public function reconcileExpiredVacancies(): int
    {
        $now = now();
        $expired = JobVacancy::where('status', 'open')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->update([
                'status' => 'closed',
                'lifecycle_status' => 'expired',
            ]);

        // Tandai stale jika tidak pernah terlihat/dikonfirmasi selama lebih dari 14 hari
        JobVacancy::where('status', 'open')
            ->where('is_demo', false)
            ->whereNotNull('last_seen_at')
            ->where('last_seen_at', '<', now()->subDays(14))
            ->update([
                'lifecycle_status' => 'stale',
            ]);

        return $expired;
    }
}
