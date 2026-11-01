<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi tabel registry sumber agregasi dan log sinkronisasi.
     */
    public function up(): void
    {
        // 1. Tabel Registry Sumber Agregasi Lowongan (Ingestion Source Registry)
        Schema::create('job_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150); // e.g., 'Alkindo Naratama E-Recruitment'
            $table->string('slug', 100)->unique();
            $table->enum('source_type', ['api', 'rss_feed', 'career_page', 'partner'])->index();
            $table->string('adapter_class', 200); // e.g., 'App\Services\Aggregator\Adapters\AlkindoCareerPageAdapter'
            $table->string('base_url', 500);
            $table->string('company_name', 200)->nullable();
            $table->string('industry', 100)->default('general')->index();
            $table->string('coverage_regions', 255)->default('Indonesia');
            $table->boolean('is_enabled')->default(true)->index();
            $table->integer('polling_interval_hours')->default(12);
            $table->timestamp('last_fetched_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->string('terms_url', 500)->nullable();
            $table->enum('robots_policy_status', ['allowed', 'restricted', 'not_applicable'])->default('allowed');
            $table->integer('total_vacancies_discovered')->default(0);
            $table->integer('total_vacancies_active')->default(0);
            $table->timestamps();
        });

        // 2. Tabel Log Riwayat Sinkronisasi (Audit Trail & Health Monitoring)
        Schema::create('job_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_source_id')->constrained('job_sources')->cascadeOnDelete();
            $table->enum('status', ['running', 'success', 'failed'])->index();
            $table->integer('vacancies_fetched')->default(0);
            $table->integer('vacancies_created')->default(0);
            $table->integer('vacancies_updated')->default(0);
            $table->integer('vacancies_expired')->default(0);
            $table->integer('duplicates_detected')->default(0);
            $table->text('error_details')->nullable();
            $table->decimal('duration_seconds', 8, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        // 3. Tambahkan field pelacakan agregasi & lifecycle pada job_vacancies secara non-destruktif
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->foreignId('job_source_id')->nullable()->after('id')->constrained('job_sources')->nullOnDelete();
            $table->string('canonical_hash', 64)->nullable()->after('external_reference')->index();
            $table->timestamp('first_seen_at')->nullable()->after('published_at');
            $table->timestamp('last_seen_at')->nullable()->after('first_seen_at');
            $table->enum('lifecycle_status', [
                'active',
                'pending_verification',
                'stale',
                'expired',
                'unavailable',
                'removed'
            ])->default('active')->after('status')->index();
        });
    }

    /**
     * Balikkan migrasi secara aman.
     */
    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropForeign(['job_source_id']);
            $table->dropColumn([
                'job_source_id',
                'canonical_hash',
                'first_seen_at',
                'last_seen_at',
                'lifecycle_status',
            ]);
        });

        Schema::dropIfExists('job_sync_logs');
        Schema::dropIfExists('job_sources');
    }
};
