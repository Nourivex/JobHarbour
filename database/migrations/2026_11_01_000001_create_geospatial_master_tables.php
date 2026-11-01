<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi master spasial dan relasi geografis.
     */
    public function up(): void
    {
        // 1. Tabel Wilayah Administratif Resmi (BIG / Kemendagri)
        Schema::create('administrative_regions', function (Blueprint $table) {
            $table->string('code', 20)->primary(); // Kode Kemendagri: cth '33' (Provinsi), '33.76' (Kota Tegal)
            $table->string('parent_code', 20)->nullable()->index();
            $table->string('name', 150);
            $table->enum('level', ['province', 'regency_city', 'district'])->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('bounding_box')->nullable(); // [min_lng, min_lat, max_lng, max_lat]
            $table->string('geometry_path', 255)->nullable(); // Path ke berkas static MVT/GeoJSON terkompresi
            $table->longText('geometry_geojson')->nullable(); // Poligon geometri lokal jika disimpan inline
            $table->string('provenance_source', 150)->default('Badan Informasi Geospasial (BIG) / Kemendagri RI');
            $table->string('source_edition', 50)->default('BPS/BIG 2024 Semester 1');
            $table->enum('geometry_quality', ['simplified_topology', 'boundary_polygon', 'centroid_only'])->default('centroid_only');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Kawasan Industri & Klaster Teknologi Tervalidasi
        Schema::create('industrial_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->enum('category', [
                'maritime_logistics',
                'manufacturing_ev',
                'technology_digital',
                'energy_mining',
                'agro_industry'
            ])->index();
            $table->string('region_code', 20)->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('geometry_type', 50)->default('point'); // point / polygon
            $table->longText('polygon_geojson')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->enum('verification_status', [
                'official_kemenperin',   // Resmi terdaftar Kawasan Industri Kemenperin RI
                'verified_estate',       // Kawasan industri swasta terverifikasi
                'approximate_area'       // Estimasi area perkiraan
            ])->default('approximate_area')->index();
            $table->string('data_source', 200)->default('Kemenperin RI / OpenStreetMap');
            $table->string('source_url', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('region_code')->references('code')->on('administrative_regions')->onDelete('cascade');
        });

        // 3. Tambahkan kolom spasial pada job_vacancies secara NON-DESTRUKTIF
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->string('administrative_code', 20)->nullable()->after('location')->index();
            $table->unsignedBigInteger('industrial_zone_id')->nullable()->after('administrative_code')->index();
            $table->enum('job_source_type', [
                'direct_recruiter',      // Diposting langsung oleh recruiter mitra JobHarbour
                'official_career_page',  // Kurasi dari website karir resmi perusahaan
                'campus_partnership'     // Kemitraan Career Center kampus
            ])->default('direct_recruiter')->after('industrial_zone_id');
            $table->enum('location_verification_status', [
                'verified_site',         // Alamat kantor fisik terverifikasi
                'verified_industrial',   // Terletak di dalam Kawasan Industri resmi
                'approximate_city',      // Perkiraan tingkat kota (tidak ada koordinat presisi kantor)
                'unverified'             // Belum diverifikasi
            ])->default('approximate_city')->after('job_source_type');
            $table->string('location_data_source', 150)->default('JobHarbour Internal')->after('location_verification_status');

            $table->foreign('administrative_code')->references('code')->on('administrative_regions')->nullOnDelete();
            $table->foreign('industrial_zone_id')->references('id')->on('industrial_zones')->nullOnDelete();
        });
    }

    /**
     * Balikkan migrasi secara aman.
     */
    public function down(): void
    {
        Schema::table('job_vacancies', function (Blueprint $table) {
            $table->dropForeign(['administrative_code']);
            $table->dropForeign(['industrial_zone_id']);
            $table->dropColumn([
                'administrative_code',
                'industrial_zone_id',
                'job_source_type',
                'location_verification_status',
                'location_data_source'
            ]);
        });

        Schema::dropIfExists('industrial_zones');
        Schema::dropIfExists('administrative_regions');
    }
};
