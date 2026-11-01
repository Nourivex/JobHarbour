<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi penambahan atribut sumber, transparansi, dan pemisahan data demo secara non-destruktif.
     */
    public function up(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            // 1. Tipe Asal Lowongan: direct, partner, external
            $table->enum('source_type', ['direct', 'partner', 'external'])->default('direct')->after('status')->index();
            
            // 2. Metadata Sumber Eksternal & URL Lamar Asli
            $table->string('source_company', 255)->nullable()->after('source_type');
            $table->string('source_url', 500)->nullable()->after('source_company');
            $table->string('application_url', 500)->nullable()->after('source_url');
            $table->string('external_reference', 100)->nullable()->after('application_url');
            
            // 3. Tanggal Publikasi, Kedaluwarsa, & Verifikasi
            $table->timestamp('published_at')->nullable()->after('external_reference');
            $table->timestamp('last_checked_at')->nullable()->after('published_at');
            
            // 4. Status Verifikasi Data Lowongan
            $table->enum('verification_status', [
                'verified_official',   // Terverifikasi langsung dari portal karir resmi perusahaan
                'verified_partner',    // Terverifikasi dari HRD mitra langsung JobHarbour
                'unverified_external', // Kurasi luar belum diverifikasi penuh
                'demo_sample'          // Sampel data untuk simulasi/demo
            ])->default('demo_sample')->after('last_checked_at')->index();
            $table->text('verification_notes')->nullable()->after('verification_status');

            // 5. Penanda Data Demo vs Produksi Nyata
            $table->boolean('is_demo')->default(false)->after('verification_notes')->index();
        });
    }

    /**
     * Balikkan migrasi secara aman.
     */
    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropColumn([
                'source_type',
                'source_company',
                'source_url',
                'application_url',
                'external_reference',
                'published_at',
                'last_checked_at',
                'verification_status',
                'verification_notes',
                'is_demo',
            ]);
        });
    }
};
