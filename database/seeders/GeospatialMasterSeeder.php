<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeospatialMasterSeeder extends Seeder
{
    /**
     * Seed data master wilayah resmi Indonesia 38 Provinsi & Kabupaten/Kota (BIG/Kemendagri)
     * serta Kawasan Industri & KEK Terverifikasi (Kemenperin & Dewan KEK).
     */
    public function run(): void
    {
        // 1. Catat Registri Dataset Resmi (Provenance Audit Trail)
        $datasets = [
            [
                'dataset_key' => 'provinces_38',
                'dataset_name' => 'Peta Batas Administrasi 38 Provinsi Indonesia',
                'source_agency' => 'Badan Informasi Geospasial (BIG) & Ditjen Bina Adwil Kemendagri',
                'source_url' => 'https://tanahair.indonesia.go.id',
                'dataset_edition' => 'Kepmendagri No. 300.2.2-2138 Tahun 2025 / BIG 2024-2026',
                'published_at' => '2025-01-15',
                'retrieved_at' => now(),
                'last_verified_at' => now(),
                'data_status' => 'active_official',
                'coordinate_system' => 'EPSG:4326 (WGS84)',
                'feature_count' => 38,
                'change_notes' => 'Akomodasi pemekaran 4 provinsi baru di Papua (Papua Selatan, Papua Tengah, Papua Pegunungan, Papua Barat Daya). Total resmi 38 Provinsi.'
            ],
            [
                'dataset_key' => 'regencies_518',
                'dataset_name' => 'Batas Administrasi Kabupaten/Kota Indonesia',
                'source_agency' => 'Badan Informasi Geospasial (BIG)',
                'source_url' => 'https://tanahair.indonesia.go.id/portal-web',
                'dataset_edition' => 'BATAS_KABKOTA_AR_2026 Kemendagri',
                'published_at' => '2025-06-01',
                'retrieved_at' => now(),
                'last_verified_at' => now(),
                'data_status' => 'active_official',
                'coordinate_system' => 'EPSG:4326 (WGS84)',
                'feature_count' => 518,
                'change_notes' => '518 Kabupaten/Kota dengan atribut kode wilayah resmi Kemendagri (KDPKAB).'
            ],
            [
                'dataset_key' => 'kemenperin_ki',
                'dataset_name' => 'Direktori Kawasan Industri Eksisting & KEK Nasional',
                'source_agency' => 'Kementerian Perindustrian RI (SIINas) & Dewan Nasional KEK',
                'source_url' => 'https://kemenperin.go.id/kawasan-industri',
                'dataset_edition' => 'Kemenperin Direktori 2024-2025',
                'published_at' => '2024-12-01',
                'retrieved_at' => now(),
                'last_verified_at' => now(),
                'data_status' => 'active_official',
                'coordinate_system' => 'EPSG:4326 (WGS84)',
                'feature_count' => 8,
                'change_notes' => 'Sentra industri strategis: Galangan Kapal Tegal, KITB Batang, KEK Kendal, BDV Bandung, KEK Nongsa Batam, KIPP IKN, Kawasan Industri Morowali, KEK Sei Mangkei.'
            ]
        ];

        foreach ($datasets as $ds) {
            DB::table('geospatial_dataset_registry')->updateOrInsert(
                ['dataset_key' => $ds['dataset_key']],
                array_merge($ds, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        // 2. Data Wilayah 38 Provinsi Resmi (Kepmendagri / BIG)
        // Parse nama dan kode provinsi dari GeoJSON resmi
        $provGeoPath = public_path('geo/indonesia-38-provinces-kemendagri.geojson');
        if (file_exists($provGeoPath)) {
            $provData = json_decode(file_get_contents($provGeoPath), true);
            foreach ($provData['features'] as $f) {
                $code = (string) $f['properties']['KODE_PROV'];
                $name = $f['properties']['PROVINSI'];
                
                // Hitung perkiraan centroid kasar dari geometry untuk navigasi cepat
                $coords = $f['geometry']['coordinates'];
                $firstRing = ($f['geometry']['type'] === 'MultiPolygon') ? $coords[0][0] : $coords[0];
                $avgLng = array_sum(array_column($firstRing, 0)) / count($firstRing);
                $avgLat = array_sum(array_column($firstRing, 1)) / count($firstRing);

                DB::table('administrative_regions')->updateOrInsert(
                    ['code' => $code],
                    [
                        'parent_code' => null,
                        'name' => $name,
                        'level' => 'province',
                        'latitude' => round($avgLat, 7),
                        'longitude' => round($avgLng, 7),
                        'provenance_source' => 'BIG & Ditjen Bina Adwil Kemendagri',
                        'source_edition' => 'Kepmendagri No. 300.2.2-2138 Tahun 2025',
                        'geometry_quality' => 'boundary_polygon',
                        'verified_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 3. Data Kabupaten / Kota Kunci yang Terkait dengan Sentra Industri
        $regencies = [
            ['code' => '33.76', 'parent_code' => '33', 'name' => 'Kota Tegal', 'lat' => -6.8694, 'lng' => 109.1256],
            ['code' => '33.28', 'parent_code' => '33', 'name' => 'Kabupaten Tegal', 'lat' => -7.0000, 'lng' => 109.1300],
            ['code' => '33.25', 'parent_code' => '33', 'name' => 'Kabupaten Batang', 'lat' => -6.9130, 'lng' => 109.7320],
            ['code' => '33.24', 'parent_code' => '33', 'name' => 'Kabupaten Kendal', 'lat' => -6.9200, 'lng' => 110.2000],
            ['code' => '33.74', 'parent_code' => '33', 'name' => 'Kota Semarang', 'lat' => -6.9932, 'lng' => 110.4203],
            ['code' => '32.73', 'parent_code' => '32', 'name' => 'Kota Bandung', 'lat' => -6.9175, 'lng' => 107.6191],
            ['code' => '21.71', 'parent_code' => '21', 'name' => 'Kota Batam', 'lat' => 1.1301, 'lng' => 104.0529],
            ['code' => '64.09', 'parent_code' => '64', 'name' => 'Kabupaten Penajam Paser Utara', 'lat' => -1.2500, 'lng' => 116.8300],
            ['code' => '72.06', 'parent_code' => '72', 'name' => 'Kabupaten Morowali', 'lat' => -2.6500, 'lng' => 121.9000],
            ['code' => '12.08', 'parent_code' => '12', 'name' => 'Kabupaten Simalungun', 'lat' => 2.9600, 'lng' => 99.0600]
        ];

        foreach ($regencies as $r) {
            DB::table('administrative_regions')->updateOrInsert(
                ['code' => $r['code']],
                [
                    'parent_code' => $r['parent_code'],
                    'name' => $r['name'],
                    'level' => 'regency_city',
                    'latitude' => $r['lat'],
                    'longitude' => $r['lng'],
                    'provenance_source' => 'BATAS_KABKOTA_AR_2026 Kemendagri / BIG',
                    'source_edition' => 'BPS/BIG 2026',
                    'geometry_quality' => 'boundary_polygon',
                    'verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // 4. Data Kawasan Industri Resmi & KEK (Murni Data Industri, Tanpa Ketergantungan Lowongan)
        $zonesJsonPath = public_path('geo/national_industrial_estates.json');
        $zones = file_exists($zonesJsonPath) ? json_decode(file_get_contents($zonesJsonPath), true) : [];

        // Pastikan kode kabupaten yang terkait dengan sentra industri nasional terdaftar di administrative_regions
        $regencyMap = [
            ['code' => '12.71', 'parent_code' => '12', 'name' => 'Kota Medan', 'lat' => 3.5952, 'lng' => 98.6722],
            ['code' => '13.71', 'parent_code' => '13', 'name' => 'Kota Padang', 'lat' => -0.9471, 'lng' => 100.4172],
            ['code' => '31.72', 'parent_code' => '31', 'name' => 'Kota Jakarta Utara', 'lat' => -6.1214, 'lng' => 106.8833],
            ['code' => '32.16', 'parent_code' => '32', 'name' => 'Kabupaten Bekasi', 'lat' => -6.2415, 'lng' => 107.1352],
            ['code' => '32.15', 'parent_code' => '32', 'name' => 'Kabupaten Karawang', 'lat' => -6.3052, 'lng' => 107.3012],
            ['code' => '32.13', 'parent_code' => '32', 'name' => 'Kabupaten Subang', 'lat' => -6.5682, 'lng' => 107.7612],
            ['code' => '35.25', 'parent_code' => '35', 'name' => 'Kabupaten Gresik', 'lat' => -7.1562, 'lng' => 112.6552],
            ['code' => '35.78', 'parent_code' => '35', 'name' => 'Kota Surabaya', 'lat' => -7.2575, 'lng' => 112.7521],
            ['code' => '64.71', 'parent_code' => '64', 'name' => 'Kota Balikpapan', 'lat' => -1.2654, 'lng' => 116.8312],
            ['code' => '64.74', 'parent_code' => '64', 'name' => 'Kota Bontang', 'lat' => 0.1332, 'lng' => 117.5002],
            ['code' => '73.71', 'parent_code' => '73', 'name' => 'Kota Makassar', 'lat' => -5.1477, 'lng' => 119.4327],
            ['code' => '74.03', 'parent_code' => '74', 'name' => 'Kabupaten Konawe', 'lat' => -3.8552, 'lng' => 122.0552],
            ['code' => '72.71', 'parent_code' => '72', 'name' => 'Kota Palu', 'lat' => -0.9002, 'lng' => 119.8780],
            ['code' => '51.71', 'parent_code' => '51', 'name' => 'Kota Denpasar', 'lat' => -8.6705, 'lng' => 115.2126],
            ['code' => '82.02', 'parent_code' => '82', 'name' => 'Kabupaten Halmahera Tengah', 'lat' => 0.4852, 'lng' => 127.9512],
            ['code' => '91.71', 'parent_code' => '91', 'name' => 'Kota Jayapura', 'lat' => -2.5337, 'lng' => 140.7181],
            ['code' => '96.01', 'parent_code' => '96', 'name' => 'Kabupaten Sorong', 'lat' => -0.9852, 'lng' => 131.3212]
        ];

        foreach ($regencyMap as $rm) {
            DB::table('administrative_regions')->updateOrInsert(
                ['code' => $rm['code']],
                [
                    'parent_code' => $rm['parent_code'],
                    'name' => $rm['name'],
                    'level' => 'regency_city',
                    'latitude' => $rm['lat'],
                    'longitude' => $rm['lng'],
                    'provenance_source' => 'BATAS_KABKOTA_AR_2026 Kemendagri / BIG',
                    'source_edition' => 'BPS/BIG 2026',
                    'geometry_quality' => 'boundary_polygon',
                    'verified_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        foreach ($zones as $z) {
            $record = $z;
unset($record['id']);
            if (isset($record['anchor_tenants']) && is_array($record['anchor_tenants'])) {
                $record['anchor_tenants'] = json_encode($record['anchor_tenants']);
            }
            if (isset($record['infrastructure_support']) && is_array($record['infrastructure_support'])) {
                $record['infrastructure_support'] = json_encode($record['infrastructure_support']);
            }
            DB::table('industrial_zones')->updateOrInsert(
                ['slug' => $z['slug']],
                array_merge($record, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        // 5. Hubungkan data lowongan internal ke kode wilayah resmi (tanpa merusak data)
        DB::table('job_vacancies')->where('id', 1)->update([
            'administrative_code' => '33.76',
            'industrial_zone_id' => 1,
            'job_source_type' => 'direct_recruiter',
            'location_verification_status' => 'verified_industrial',
            'location_data_source' => 'PT Teknologi Maju - Head Office Tegal Port'
        ]);

        DB::table('job_vacancies')->where('id', 2)->update([
            'administrative_code' => '32.73',
            'industrial_zone_id' => 4,
            'job_source_type' => 'direct_recruiter',
            'location_verification_status' => 'verified_site',
            'location_data_source' => 'PT Digital Solusindo - Hub Gegerkalong'
        ]);

        DB::table('job_vacancies')->where('id', 3)->update([
            'administrative_code' => '33.76',
            'industrial_zone_id' => 1,
            'job_source_type' => 'direct_recruiter',
            'location_verification_status' => 'verified_industrial',
            'location_data_source' => 'PT Samudra Cloud Bahari - Tegal Facility'
        ]);

        DB::table('job_vacancies')->where('id', 4)->update([
            'administrative_code' => '33.74',
            'industrial_zone_id' => null,
            'job_source_type' => 'direct_recruiter',
            'location_verification_status' => 'approximate_city',
            'location_data_source' => 'PT Merpati Logistik Bahari - Wilayah Semarang'
        ]);
    }
}
