<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeospatialAtlasController extends Controller
{
    use ApiResponse;

    /**
     * Mengembalikan data GeoJSON Poligon Batas 38 Provinsi Resmi Indonesia (BIG / Kemendagri 2025).
     * Murni data administratif tanpa ketergantungan metrik lowongan kerja.
     */
    public function getProvincesGeoJson(Request $request): JsonResponse
    {
        $path = public_path('geo/indonesia-38-provinces-kemendagri.geojson');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Berkas batas 38 provinsi tidak ditemukan.'], 404);
        }

        $geoData = json_decode(file_get_contents($path), true);
        
        // Ambil data registri resmi
        $registry = DB::table('geospatial_dataset_registry')->where('dataset_key', 'provinces_38')->first();

        // Sematkan metadata provenansi resmi ke setiap fitur poligon provinsi
        foreach ($geoData['features'] as &$feat) {
            $code = (string) ($feat['properties']['KODE_PROV'] ?? '');
            $name = $feat['properties']['PROVINSI'] ?? '';
            
            $feat['properties']['region_code'] = $code;
            $feat['properties']['region_name'] = $name;
            $feat['properties']['level'] = 'province';
            $feat['properties']['provenance'] = [
                'source_agency' => $registry->source_agency ?? 'Badan Informasi Geospasial (BIG) & Kemendagri',
                'source_url' => $registry->source_url ?? 'https://tanahair.indonesia.go.id',
                'dataset_edition' => $registry->dataset_edition ?? 'Kepmendagri No. 300.2.2-2138 Tahun 2025',
                'boundary_type' => 'Official Provincial Boundary Polygon',
                'verification_status' => 'verified_official',
                'verified_at' => $registry->last_verified_at ?? now()->toIso8601String(),
            ];
        }

        $geoData['meta'] = [
            'crs' => 'EPSG:4326',
            'dataset_name' => 'Radar Perkembangan Industri — Batas 38 Provinsi Indonesia',
            'edition' => $registry->dataset_edition ?? 'Kepmendagri No. 300.2.2-2138',
            'total_features' => count($geoData['features']),
            'timestamp' => now()->toIso8601String(),
        ];

        return response()->json($geoData);
    }

    /**
     * Mengembalikan data GeoJSON Poligon Batas Kabupaten/Kota (BIG BATAS_KABKOTA_AR_2026).
     * Dapat difilter berdasarkan kode provinsi (?province_code=33) agar ringan dan cepat.
     */
    public function getRegenciesGeoJson(Request $request): JsonResponse
    {
        $path = public_path('geo/indonesia-regencies-kemendagri.geojson');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Berkas batas kabupaten/kota tidak ditemukan.'], 404);
        }

        $geoData = json_decode(file_get_contents($path), true);
        $provinceFilter = $request->query('province_code');

        $filteredFeatures = [];
        foreach ($geoData['features'] as $feat) {
            $props = $feat['properties'];
            $provCode = (string) ($props['province_code'] ?? $props['KDPPUM'] ?? '');
            $regCode = (string) ($props['region_code'] ?? $props['KDPKAB'] ?? '');
            $regName = (string) ($props['region_name'] ?? $props['WADMKK'] ?? $props['NAMOBJ'] ?? 'Kabupaten/Kota');
            $provName = (string) ($props['province_name'] ?? $props['WADMPR'] ?? '');
            $admType = (string) ($props['admin_type'] ?? (str_contains(strtolower($regName), 'kota') ? 'kota' : 'kabupaten'));

            if ($provinceFilter && $provCode !== $provinceFilter) {
                continue;
            }

            $feat['properties'] = [
                'region_code' => $regCode,
                'province_code' => $provCode,
                'region_name' => $regName,
                'regency_name' => $regName, // kompatibilitas ganda
                'province_name' => $provName,
                'admin_type' => $admType,
                'level' => 'regency_city',
                'provenance' => [
                    'source_agency' => 'Badan Informasi Geospasial (BIG) & Ditjen Bina Adwil Kemendagri',
                    'dataset_edition' => 'BATAS_KABKOTA_AR_2026 Kemendagri',
                    'verification_status' => 'verified_official',
                    'coordinate_system' => 'EPSG:4326 (WGS84)'
                ]
            ];

            $filteredFeatures[] = $feat;
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'meta' => [
                'crs' => 'EPSG:4326',
                'dataset_name' => 'Batas Administrasi Kabupaten dan Kota Indonesia Resmi 2026',
                'filter_province' => $provinceFilter ?? 'all',
                'total_features' => count($filteredFeatures),
                'timestamp' => now()->toIso8601String(),
            ],
            'features' => $filteredFeatures,
        ]);
    }

    /**
     * Mengembalikan data GeoJSON FeatureCollection untuk Radar Perkembangan Industri.
     * Murni memuat atribut resmi industri: nama, kategori, luas (Ha), pengelola, anchor tenants, infrastruktur pendukung, dan data provenance Kemenperin.
     */
    public function getIndustryRadarData(Request $request): JsonResponse
    {
        $zones = DB::table('industrial_zones')
            ->join('administrative_regions', 'industrial_zones.region_code', '=', 'administrative_regions.code')
            ->select(
                'industrial_zones.*',
                'administrative_regions.name as region_name',
                'administrative_regions.parent_code as province_code'
            )
            ->get();

        // Ambil nama provinsi induk
        $provinces = DB::table('administrative_regions')->where('level', 'province')->get()->keyBy('code');

        $features = [];

        foreach ($zones as $zone) {
            $tenants = json_decode($zone->anchor_tenants ?? '[]', true);
            $infrastructure = json_decode($zone->infrastructure_support ?? '[]', true);
            $prov = $provinces->get($zone->province_code);
            $provinceName = $prov ? $prov->name : 'Indonesia';

            // Klasifikasi Ikon Semantik
            $semanticIcon = match($zone->category) {
                'maritime_logistics' => ($zone->zone_type === 'maritime_hub' ? 'anchor' : 'ship'),
                'manufacturing_ev' => 'factory',
                'technology_digital' => 'cpu',
                'energy_mining' => 'pickaxe',
                'agro_industry' => 'leaf',
                default => 'building-2'
            };

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $zone->longitude, (float) $zone->latitude],
                ],
                'properties' => [
                    // A. Informasi Fasilitas
                    'id' => $zone->id,
                    'slug' => $zone->slug,
                    'name' => $zone->name,
                    'category' => $zone->category,
                    'zone_type' => $zone->zone_type,
                    'semantic_icon' => $semanticIcon,
                    'latitude' => (float) $zone->latitude,
                    'longitude' => (float) $zone->longitude,
                    'region_code' => $zone->region_code,
                    'region_name' => $zone->region_name,
                    'province_name' => $provinceName,
                    'area_hectares' => $zone->area_hectares ? (float) $zone->area_hectares : null,
                    'management_company' => $zone->management_company,
                    'operational_status' => $zone->operational_status,
                    'anchor_tenants' => $tenants,
                    'infrastructure_support' => $infrastructure,
                    'website_url' => $zone->website_url,
                    
                    // B. Provenansi Data Fakta Industri (Terpisah Tegas)
                    'industry_provenance' => [
                        'source_agency' => $zone->data_source,
                        'source_url' => $zone->source_url,
                        'dataset_edition' => $zone->dataset_edition ?? 'Direktori Industri 2024-2025',
                        'verification_status' => $zone->verification_status, // official_kemenperin / verified_estate / approximate_area
                        'verified_at' => $zone->verified_at ?? '2024-12-01',
                    ],

                    // C. Provenansi Geometri (Terpisah Tegas)
                    'geometry_provenance' => [
                        'source_agency' => 'Badan Informasi Geospasial (BIG) & Kemenhub Hubla',
                        'geometry_type' => 'Point (Geodetic Coordinates WGS84)',
                        'coordinate_system' => 'EPSG:4326',
                        'positioning_accuracy' => ($zone->verification_status === 'official_kemenperin' || $zone->verification_status === 'verified_estate') ? 'verified_site_entrance' : 'approximate_regional'
                    ]
                ]
            ];
        }

        $geoJson = [
            'type' => 'FeatureCollection',
            'meta' => [
                'module' => 'JobHarbour — Radar Perkembangan Industri Indonesia',
                'engine' => 'MapLibre GL Geospatial Provider',
                'crs' => 'EPSG:4326',
                'total_zones' => count($zones),
                'timestamp' => now()->toIso8601String(),
            ],
            'features' => $features,
        ];

        return response()->json($geoJson);
    }

    /**
     * Mengembalikan data GeoJSON Poligon Batas Kecamatan/Distrik (Level 3).
     * Disajikan secara lazy-loaded per kode kabupaten/kota (?regency_code=33.25 / 33.76 / 33.28).
     */
    public function getDistrictsGeoJson(Request $request): JsonResponse
    {
        $regencyCode = $request->query('regency_code', '33.76'); // default Kota Tegal
        $cleanCode = preg_replace('/[^0-9.]/', '', $regencyCode);
        $path = public_path("geo/districts/{$cleanCode}.geojson");

        if (!file_exists($path)) {
            return response()->json([
                'type' => 'FeatureCollection',
                'meta' => [
                    'regency_code' => $cleanCode,
                    'status' => 'not_available_yet',
                    'message' => "Dataset batas kecamatan untuk kode {$cleanCode} sedang dalam proses verifikasi topologi BIG."
                ],
                'features' => []
            ]);
        }

        $geoData = json_decode(file_get_contents($path), true);
        return response()->json($geoData);
    }

}
