<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambahkan nilai 'unverified' ke enum job_vacancies.verification_status.
 *
 * Nilai ini dipakai JobVacancyController untuk lowongan dari recruiter yang perusahaannya belum
 * terverifikasi (aturan 1 lowongan uji coba). Migrasi 2026_11_01_000003 sudah diubah agar
 * database baru langsung memilikinya, tetapi database yang sudah menjalankan migrasi itu
 * sebelumnya tidak ikut berubah. Migrasi ini menyamakannya dan aman dijalankan ulang
 * (daftar nilai yang ditulis sama dengan versi terbaru migrasi asal).
 */
return new class extends Migration
{
    private const VALUES = ['verified_official', 'verified_partner', 'unverified_external', 'unverified', 'demo_sample'];

    public function up(): void
    {
        $driver = DB::getDriverName();
        $list = "'".implode("','", self::VALUES)."'";

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE job_vacancies MODIFY verification_status ENUM({$list}) NOT NULL DEFAULT 'demo_sample'");
        } elseif ($driver === 'pgsql') {
            // Laravel membuat enum di PostgreSQL sebagai varchar + CHECK constraint
            DB::statement('ALTER TABLE job_vacancies DROP CONSTRAINT IF EXISTS job_vacancies_verification_status_check');
            DB::statement("ALTER TABLE job_vacancies ADD CONSTRAINT job_vacancies_verification_status_check CHECK (verification_status IN ({$list}))");
        }
        // SQLite: enum hanya berupa CHECK di definisi tabel dan tidak dipakai di lingkungan ini
    }

    public function down(): void
    {
        // Nilai 'unverified' sengaja tidak dihapus: baris yang sudah memakainya akan gagal diubah balik
    }
};
