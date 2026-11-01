<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Upgrade skema geospasial untuk Radar Perkembangan Industri Indonesia.
     * Mendukung metadata rilis resmi, kawasan industri terverifikasi, KEK, dan pelacakan dataset.
     */
    public function up(): void
    {
        // 1. Tabel Registri Pembaruan & Versi Dataset Resmi (Provenance Audit Log)
        Schema::create('geospatial_dataset_registry', function (Blueprint $table) {
            $table->id();
            $table->string('dataset_key', 100)->unique(); // 'provinces_38', 'regencies_518', 'kemenperin_ki', 'kek_nasional'
            $table->string('dataset_name', 200);
            $table->string('source_agency', 150); // 'Badan Informasi Geospasial (BIG)', 'Kemenperin RI', 'Dewan KEK'
            $table->string('source_url', 255)->nullable();
            $table->string('dataset_edition', 100); // 'Kepmendagri No. 300.2.2-2138 / BIG 2024-2025'
            $table->date('published_at')->nullable();
            $table->timestamp('retrieved_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->enum('data_status', ['active_official', 'staging_validation', 'deprecated'])->default('active_official');
            $table->string('coordinate_system', 50)->default('EPSG:4326 (WGS84)');
            $table->integer('feature_count')->default(0);
            $table->text('change_notes')->nullable();
            $table->timestamps();
        });

        // 2. Perluas tabel industrial_zones agar memuat atribut resmi Kemenperin & KEK
        Schema::table('industrial_zones', function (Blueprint $table) {
            $table->enum('zone_type', ['kawasan_industri', 'kawasan_ekonomi_khusus', 'sentra_manufaktur', 'tech_park', 'maritime_hub'])->default('kawasan_industri')->after('category')->index();
            $table->decimal('area_hectares', 12, 2)->nullable()->after('polygon_geojson'); // Luas kawasan resmi (Ha)
            $table->string('management_company', 255)->nullable()->after('area_hectares'); // Pengelola / Badan Usaha
            $table->enum('operational_status', ['operating', 'under_development', 'planning'])->default('operating')->after('management_company')->index();
            $table->json('anchor_tenants')->nullable()->after('operational_status'); // Perusahaan tenant terverifikasi
            $table->json('infrastructure_support')->nullable()->after('anchor_tenants'); // Pelabuhan terdekat, akses tol, bandara
            $table->string('dataset_edition', 100)->nullable()->after('data_source');
        });
    }

    /**
     * Balikkan migrasi secara aman.
     */
    public function down(): void
    {
        Schema::table('industrial_zones', function (Blueprint $table) {
            $table->dropColumn([
                'zone_type',
                'area_hectares',
                'management_company',
                'operational_status',
                'anchor_tenants',
                'infrastructure_support',
                'dataset_edition'
            ]);
        });

        Schema::dropIfExists('geospatial_dataset_registry');
    }
};
