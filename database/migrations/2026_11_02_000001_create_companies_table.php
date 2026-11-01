<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil perusahaan milik recruiter dan riwayat pengajuan verifikasinya.
 *
 * Satu recruiter memiliki satu profil perusahaan. Status verifikasi disimpan di tabel companies
 * (unverified, pending, verified, rejected, suspended), sedangkan setiap pengajuan beserta dokumen
 * dan catatan admin disimpan di tabel company_verifications agar riwayatnya tidak hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('industry', 100)->nullable();
            $table->string('size', 20)->nullable();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('verification_status', 20)->default('unverified')->index();
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('company_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('registration_number', 50);
            $table->string('tax_number', 50)->nullable();
            $table->string('applicant_position', 100);
            $table->json('documents');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_verifications');
        Schema::dropIfExists('companies');
    }
};
