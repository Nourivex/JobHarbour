<?php

namespace App\Console\Commands;

use App\Models\JobSource;
use App\Services\Aggregator\JobAggregationService;
use Illuminate\Console\Command;

class SyncJobSourcesCommand extends Command
{
    protected $signature = 'jobs:sync {--source= : Slug sumber spesifik yang ingin disinkronkan} {--reconcile-only : Hanya jalankan rekonsiliasi kedaluwarsa}';
    protected $description = 'Jalankan mesin agregasi lowongan kerja otomatis dan rekonsiliasi kesegaran data.';

    public function handle(JobAggregationService $service): int
    {
        $this->info("🚀 Memulai Mesin Agregasi & Kesegaran Lowongan JobHarbour...");

        if ($this->option('reconcile-only')) {
            $expired = $service->reconcileExpiredVacancies();
            $this->info("✓ Rekonsiliasi selesai: {$expired} lowongan kedaluwarsa ditutup.");
            return Command::SUCCESS;
        }

        $query = JobSource::where('is_enabled', true);
        if ($slug = $this->option('source')) {
            $query->where('slug', $slug);
        }

        $sources = $query->get();
        if ($sources->isEmpty()) {
            $this->warn("Tidak ada sumber aktif yang ditemukan.");
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$sources->count()} sumber aktif yang akan disinkronkan.");

        foreach ($sources as $source) {
            $this->line("📡 Memproses [{$source->name}]...");
            $log = $service->syncSource($source);

            if ($log->status === 'success') {
                $this->info("  ✓ Berhasil: {$log->vacancies_fetched} ditemukan, {$log->vacancies_created} dibuat, {$log->vacancies_updated} diperbarui ({$log->duration_seconds}s)");
            } else {
                $this->error("  ✗ Gagal: {$log->error_details}");
            }
        }

        $expired = $service->reconcileExpiredVacancies();
        $this->info("✓ Rekonsiliasi siklus hidup: {$expired} lowongan kedaluwarsa ditutup.");
        $this->info("✨ Sinkronisasi selesai!");

        return Command::SUCCESS;
    }
}
